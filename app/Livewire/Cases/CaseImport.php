<?php

namespace App\Livewire\Cases;

use App\Jobs\TranslateContent;
use App\Models\CaseModel;
use App\Services\CaseImport\CaseImporter;
use App\Services\{DeepLTranslator, GoogleDriveService};
use Illuminate\Support\Facades\{DB, Log, Validator};
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\{Component, WithFileUploads};

/**
 * Impor banyak kasus sekaligus dari folder Google Drive (Google Docs / .docx) atau upload .docx.
 * Dokumen dibaca bertahap (beberapa file per request) lalu ditampilkan sebagai pratinjau
 * sebelum disimpan sebagai kasus draft.
 */
class CaseImport extends Component
{
    use WithFileUploads;

    // Batas waktu satu request processQueue. Ditambah satu batch unduhan (timeout 15 detik)
    // tetap di bawah max_execution_time PHP (30 detik).
    private const SECONDS_PER_STEP = 8;

    // Jumlah dokumen yang diunduh paralel dari Drive (lebih banyak = lebih cepat, tapi lebih mudah diblokir Google).
    private const PARALLEL_DOWNLOADS = 2;

    // Saat Google memblokir sementara: jeda 1, 2, 4, 5, 5 menit lalu lanjut otomatis; setelah itu sisa antrean ditandai gagal.
    private const PAUSE_SECONDS = [60, 120, 240, 300, 300];

    public string $folderUrl = '';

    public array $uploads = [];

    // ID batch untuk cache isi dokumen hasil parsing
    public string $batch = '';

    // File Drive yang belum dibaca
    public array $queue = [];

    public int $total = 0;

    public array $rows = [];

    public $defaultCategoryId = null;

    public bool $translate = false;

    // Timestamp (detik) sampai kapan pembacaan dijeda karena Google membatasi unduhan; 0 = tidak dijeda.
    public int $pausedUntil = 0;

    public int $pauses = 0;

    // Sisa detik jeda saat respons dikirim; dipakai browser untuk menunggu (tidak bergantung jam browser).
    public int $resumeIn = 0;

    // Impor bertahap: jumlah dokumen yang dibaca per tahap, agar Google tidak memblokir unduhan.
    public const BATCH_SIZES = [20, 30, 40, 50];

    public int $perBatch = 30;

    // Dokumen folder yang belum diimpor dan menunggu tahap berikutnya / yang sudah pernah diimpor.
    public int $remaining = 0;

    public int $alreadyImported = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('case.create'), 403);

        $this->translate = app(DeepLTranslator::class)->isConfigured();
        $this->folderUrl = (string) request()->query('folder', '');
    }

    public function loadFolder(): void
    {
        $this->resetErrorBag();
        $folderId = GoogleDriveService::folderId($this->folderUrl);

        if (! $folderId) {
            $this->addError('folderUrl', 'Link folder Google Drive tidak dikenali.');

            return;
        }

        try {
            $files = app(GoogleDriveService::class)->listDocuments($folderId);
        } catch (\RuntimeException $e) {
            $this->addError('folderUrl', $e->getMessage());

            return;
        }

        if ($files === []) {
            $this->addError('folderUrl', 'Folder tidak berisi Google Docs atau file .docx.');

            return;
        }

        $this->startBatch();

        // Dokumen yang sudah pernah diimpor tidak diunduh lagi.
        $imported = CaseModel::whereIn('source_ref', array_map(fn ($f) => 'gdrive:'.$f['id'], $files))->pluck('source_ref')->flip();
        $pending = array_values(array_filter($files, fn ($f) => ! isset($imported['gdrive:'.$f['id']])));
        $this->alreadyImported = count($files) - count($pending);

        if ($pending === []) {
            $this->addError('folderUrl', 'Semua dokumen di folder ini sudah diimpor.');

            return;
        }

        $perBatch = in_array($this->perBatch, self::BATCH_SIZES, true) ? $this->perBatch : 30;
        $this->queue = array_slice($pending, 0, $perBatch);
        $this->total = count($this->queue);
        $this->remaining = count($pending) - $this->total;

        // Browser lalu memanggil processQueue berulang, satu per satu (lihat case-import.blade.php).
        $this->dispatch('case-import-read');
    }

    /**
     * Baca file Drive dari antrean selama maksimal SECONDS_PER_STEP detik.
     * Dipanggil berulang dari browser sampai mengembalikan false (antrean habis).
     */
    public function processQueue(): bool
    {
        if ($this->queue === []) {
            return false;
        }

        $this->resumeIn = max(0, $this->pausedUntil - now()->timestamp);
        if ($this->resumeIn > 0) {
            return true;
        }


        $drive = app(GoogleDriveService::class);
        // Dihitung dari awal request HTTP, sehingga tetap aman bila beberapa panggilan tergabung dalam satu request.
        $startedAt = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);

        while ($this->queue && microtime(true) - $startedAt < self::SECONDS_PER_STEP) {
            $files = array_splice($this->queue, 0, self::PARALLEL_DOWNLOADS);
            $downloads = $drive->downloadMany($files);

            $throttled = array_values(array_filter($files, fn ($file) => $downloads[$file['id']] instanceof \RuntimeException
                && $downloads[$file['id']]->getCode() === GoogleDriveService::THROTTLED));

            foreach ($files as $file) {
                if (! in_array($file, $throttled, true)) {
                    $this->prepareRow('gdrive:'.$file['id'], $file['name'], function () use ($downloads, $file) {
                        return is_string($downloads[$file['id']]) ? $downloads[$file['id']] : throw $downloads[$file['id']];
                    }, $file);
                }
            }

            if ($throttled) {
                // Google membatasi sementara: kembalikan ke antrean dan jeda, lalu lanjut otomatis.
                array_unshift($this->queue, ...$throttled);

                return $this->pause($downloads[$throttled[0]['id']]->getMessage());
            }

            $this->pauses = 0;
        }

        return $this->queue !== [];
    }

    private function pause(string $message): bool
    {
        if ($this->pauses >= count(self::PAUSE_SECONDS)) {
            // Sudah terlalu lama diblokir: sisa antrean ditandai gagal agar bisa dicoba lagi nanti.
            foreach ($this->queue as $file) {
                $this->rows[] = ['ref' => 'gdrive:'.$file['id'], 'name' => $file['name'], 'title' => $file['name'], 'error' => $message, 'selected' => false, 'file' => $file];
            }
            $this->queue = [];
            $this->pausedUntil = 0;
            $this->pauses = 0;
            $this->resumeIn = 0;

            return false;
        }

        $this->resumeIn = self::PAUSE_SECONDS[$this->pauses++];
        $this->pausedUntil = now()->timestamp + $this->resumeIn;

        return true;
    }

    public function updatedUploads(): void
    {
        $this->validate(['uploads.*' => 'file|extensions:docx|max:10240'], [
            'uploads.*.extensions' => 'Hanya file Word (.docx) yang bisa diimpor.',
        ]);

        if ($this->batch === '') {
            $this->startBatch();
        }

        foreach ($this->uploads as $file) {
            $binary = file_get_contents($file->getRealPath());
            $this->prepareRow('upload:'.sha1($binary), $file->getClientOriginalName(), fn () => $binary);
            $this->total++;
        }

        $this->uploads = [];
    }

    /**
     * Masukkan kembali dokumen Drive yang gagal dibaca ke antrean.
     */
    public function retryFailed(): void
    {
        $failed = array_filter($this->rows, fn ($row) => ! empty($row['error']) && isset($row['file']));

        if ($failed === []) {
            return;
        }

        $this->queue = [...$this->queue, ...array_column($failed, 'file')];
        $this->rows = array_values(array_diff_key($this->rows, $failed));
        $this->pausedUntil = 0;
        $this->pauses = 0;
        $this->resumeIn = 0;
        $this->dispatch('case-import-read');
    }

    #[Computed]
    public function failedCount(): int
    {
        return count(array_filter($this->rows, fn ($row) => ! empty($row['error']) && isset($row['file'])));
    }

    /**
     * Titik peta yang diatur manual dari pratinjau (klik/geser di peta, atau tempel koordinat).
     * Lat/lng null = hapus titik.
     */
    public function setPoint(int $index, $lat, $lng): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        if ($lat === null || $lng === null) {
            $this->rows[$index] = array_merge($this->rows[$index], ['lat' => null, 'lng' => null, 'location' => null, 'precision' => null]);

            return;
        }

        if (! is_numeric($lat) || ! is_numeric($lng) || abs((float) $lat) > 90 || abs((float) $lng) > 180) {
            $this->dispatch('notify-error', message: 'Koordinat tidak valid.');

            return;
        }

        $lat = round((float) $lat, 7);
        $lng = round((float) $lng, 7);

        $this->rows[$index] = array_merge($this->rows[$index], [
            'lat' => $lat,
            'lng' => $lng,
            'location' => sprintf('%.5f, %.5f', $lat, $lng),
            'precision' => 'manual',
        ]);
    }

    public function applyDefaultCategory(): void
    {
        if (! $this->defaultCategoryId) {
            return;
        }

        foreach ($this->rows as $i => $row) {
            if (empty($row['category_id'])) {
                $this->rows[$i]['category_id'] = (int) $this->defaultCategoryId;
            }
        }
    }

    public function selectAll(bool $selected): void
    {
        foreach ($this->rows as $i => $row) {
            $this->rows[$i]['selected'] = $selected && $this->importable($row);
        }
    }

    public function import()
    {
        $selected = array_filter($this->rows, fn ($row) => $row['selected'] && $this->importable($row));

        if ($selected === []) {
            $this->addError('rows', 'Pilih minimal satu dokumen untuk diimpor.');

            return;
        }

        $rules = [];
        foreach (array_keys($selected) as $i) {
            $rules["rows.{$i}.category_id"] = 'required|exists:categories,id';
            $rules["rows.{$i}.event_date"] = 'required|date';
        }
        $validator = Validator::make(['rows' => $this->rows], $rules, [
            'rows.*.category_id.required' => 'Pilih kategori.',
            'rows.*.event_date.required' => 'Isi tanggal kejadian.',
        ]);

        if ($validator->fails()) {
            // Error per baris bisa jauh di bawah layar, jadi beri ringkasan di dekat tombol dan gulir ke baris pertama.
            $this->setErrorBag($validator->errors());
            $invalid = collect($validator->errors()->keys())->map(fn ($key) => (int) explode('.', $key)[1])->unique();
            $this->addError('rows', $invalid->count().' dokumen belum lengkap (kategori/tanggal): '
                .$invalid->take(3)->map(fn ($i) => Str::limit($this->rows[$i]['title'], 40))->implode('; ')
                .($invalid->count() > 3 ? '; …' : '').'. Lengkapi baris bertanda merah atau hapus centangnya.');
            $this->dispatch('case-import-invalid');

            return;
        }

        $importer = app(CaseImporter::class);
        $imported = [];
        $failed = [];

        foreach ($selected as $row) {
            try {
                $imported[] = $importer->import(
                    $this->batch, $row['ref'], [(int) $row['category_id']], $row['event_date'],
                    isset($row['lat']) ? (float) $row['lat'] : null,
                    isset($row['lng']) ? (float) $row['lng'] : null,
                )->id;
            } catch (\Throwable $e) {
                Log::error("Case import failed for {$row['ref']}: ".$e->getMessage());
                $failed[] = Str::limit($row['title'], 60).' ('.$e->getMessage().')';
            }
        }

        $message = count($imported).' kasus diimpor sebagai draft (belum publik).';

        if ($imported && $this->translate && app(DeepLTranslator::class)->isConfigured()) {
            foreach (array_chunk($imported, 10) as $ids) {
                TranslateContent::dispatch('case', $ids);
            }
            $message .= ' Terjemahan English sedang diproses di background.';
        }

        // Sisa = tahap berikutnya + antrean yang belum terbaca + dokumen tahap ini yang belum diimpor (gagal / tidak dipilih).
        $left = $this->remaining + count($this->queue) + count($this->rows) - count($imported)
            - count(array_filter($this->rows, fn ($r) => ! empty($r['existing'])));

        if ($left > 0 && GoogleDriveService::folderId($this->folderUrl)) {
            $message .= " Masih ada {$left} dokumen di folder yang belum diimpor; klik \"Baca folder\" untuk tahap berikutnya.";
        }

        session()->flash('success', $message);
        if ($failed) {
            session()->flash('error', 'Gagal diimpor: '.implode('; ', $failed));
        }

        return $left > 0 && GoogleDriveService::folderId($this->folderUrl)
            ? redirect()->route('case.import', ['folder' => $this->folderUrl])
            : redirect()->route('case.index');
    }

    #[Computed]
    public function categories(): array
    {
        return DB::table('categories')
            ->leftJoin('category_translations', function ($join) {
                $join->on('category_translations.category_id', '=', 'categories.id')->where('category_translations.locale', 'id');
            })
            ->where('categories.is_active', true)
            ->selectRaw('categories.id, COALESCE(category_translations.name, categories.slug) AS label')
            ->orderBy('label')
            ->pluck('label', 'id')
            ->all();
    }

    #[Computed]
    public function selectedCount(): int
    {
        return count(array_filter($this->rows, fn ($row) => $row['selected'] && $this->importable($row)));
    }

    public function render()
    {
        return view('livewire.cases.case-import', [
            'driveConfigured' => app(GoogleDriveService::class)->isConfigured(),
            'deeplConfigured' => app(DeepLTranslator::class)->isConfigured(),
        ])->layout('layouts.internal');
    }

    private function startBatch(): void
    {
        $this->batch = (string) Str::uuid();
        $this->rows = [];
        $this->queue = [];
        $this->total = 0;
        $this->pausedUntil = 0;
        $this->pauses = 0;
        $this->resumeIn = 0;
        $this->remaining = 0;
        $this->alreadyImported = 0;
    }

    /**
     * @param  array|null  $file  metadata file Drive, disimpan pada baris gagal agar bisa dicoba lagi
     */
    private function prepareRow(string $ref, string $name, callable $binary, ?array $file = null): void
    {
        try {
            $row = app(CaseImporter::class)->prepare($this->batch, $ref, $name, $binary());
        } catch (\Throwable $e) {
            Log::warning("Case import preview failed for {$ref}: ".$e->getMessage());
            $this->rows[] = ['ref' => $ref, 'name' => $name, 'title' => $name, 'error' => $e->getMessage(), 'selected' => false, 'file' => $file];

            return;
        }

        $row['category_id'] ??= $this->defaultCategoryId ? (int) $this->defaultCategoryId : null;

        $this->rows[] = $row + [
            'error' => null,
            'selected' => $row['existing'] === null && $row['title'] !== '',
        ];
    }

    private function importable(array $row): bool
    {
        return empty($row['error']) && empty($row['existing']);
    }
}
