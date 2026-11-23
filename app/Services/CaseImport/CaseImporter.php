<?php

namespace App\Services\CaseImport;

use App\Models\{CaseModel, Status};
use App\Services\{CaseStages, CaseWriter};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Impor dokumen kasus (.docx) dalam dua langkah:
 *  1. prepare(): baca & petakan dokumen untuk pratinjau; isi lengkapnya disimpan di cache.
 *  2. import():  simpan sebagai kasus draft (tidak publik) memakai CaseWriter. Status mengikuti tabel
 *                 "Tahapan Kasus" di dokumen (tahapan terjauh yang terisi), atau Terbuka bila tidak ada.
 */
class CaseImporter
{
    public function __construct(
        private DocxReader $reader,
        private CaseDocumentParser $parser,
        private LocationMatcher $locations,
        private CategoryGuesser $categories,
        private CaseWriter $writer,
    ) {}

    /**
     * @return array{ref: string, name: string, title: string, lokasi: ?string, location: ?string, precision: ?string,
     *     event_date: ?string, sections: list<string>, warnings: list<string>, existing: ?string,
     *     kualifikasi: ?string, tahap: ?string, has_tahapan: bool, tahapan: list<array{label: string, note: string}>}
     */
    public function prepare(string $batch, string $sourceRef, string $name, string $binary): array
    {
        $parsed = $this->parser->parse($this->reader->read($binary));
        $location = $this->locations->match($parsed['lokasi']);

        if ($parsed['lokasi'] === null) {
            $parsed['warnings'][] = 'Tidak ada baris "Lokasi:"; atur titik peta lewat tombol "Atur titik".';
        } elseif ($location === null) {
            $parsed['warnings'][] = 'Lokasi tidak ditemukan di data wilayah; atur titik peta lewat tombol "Atur titik".';
        }

        $category = $this->categories->guess($parsed);

        Cache::put($this->cacheKey($batch, $sourceRef), $parsed + ['location' => $location], now()->addHours(3));

        return [
            'ref' => $sourceRef,
            'name' => $name,
            'title' => $parsed['title'],
            'lokasi' => $parsed['lokasi'],
            'location' => $location['label'] ?? null,
            'precision' => $location['precision'] ?? null,
            'lat' => $location['lat'] ?? null,
            'lng' => $location['lng'] ?? null,
            'event_date' => $parsed['event_date'],
            // Kategori ditebak dari isi dokumen; alasannya (kata kunci) ditampilkan di pratinjau.
            'category_id' => $category['id'] ?? null,
            'category_suggested' => $category['id'] ?? null,
            'category_reason' => $category['reason'] ?? null,
            'sections' => $parsed['sections'],
            'warnings' => $parsed['warnings'],
            'kualifikasi' => $parsed['kualifikasi'],
            // Nama status dari tabel "Tahapan Kasus", untuk pratinjau.
            'tahap' => $parsed['tahap'] ? (Status::findByKey($parsed['tahap'])?->name ?? $parsed['tahap']) : null,
            'has_tahapan' => in_array('tahapan', $parsed['sections'], true),
            'tahapan' => $this->stageNotes($parsed),
            'existing' => CaseModel::where('source_ref', $sourceRef)->value('case_number') ?? $this->caseNumberByTitle($parsed['title']),
        ];
    }

    /**
     * Keterangan tahapan dari dokumen untuk pratinjau, urut dan berlabel seperti di halaman publik.
     *
     * @return list<array{label: string, note: string}>
     */
    private function stageNotes(array $parsed): array
    {
        $steps = CaseStages::for($parsed['kualifikasi'], null, $parsed['keterangan_tahapan'] ?? [], 'id')['steps'];

        return array_values(array_map(
            fn ($step) => ['label' => $step['label'], 'note' => $step['desc']],
            array_filter($steps, fn ($step) => $step['custom']),
        ));
    }

    /**
     * Kasus dengan judul (ID) yang sama, agar dokumen yang sama tidak terimpor dua kali
     * lewat jalur berbeda (folder Drive vs upload .docx).
     */
    private function caseNumberByTitle(string $title): ?string
    {
        if ($title === '') {
            return null;
        }

        return DB::table('case_translations')
            ->join('cases', 'cases.id', '=', 'case_translations.case_id')
            ->where('case_translations.locale', 'id')
            ->where('case_translations.title', $title)
            ->whereNull('cases.deleted_at')
            ->value('cases.case_number');
    }

    /**
     * @param  list<int>  $categoryIds
     * @param  float|null  $lat  titik peta dari pratinjau (hasil pencocokan atau diatur manual); null = tanpa titik
     */
    public function import(string $batch, string $sourceRef, array $categoryIds, string $eventDate, ?float $lat, ?float $lng): CaseModel
    {
        $parsed = Cache::get($this->cacheKey($batch, $sourceRef))
            ?? throw new RuntimeException('Data pratinjau sudah kedaluwarsa. Baca ulang folder lalu impor lagi.');

        // Status dari tabel "Tahapan Kasus" (bila ada), selain itu "Terbuka"; narasi status dari dokumen tetap disimpan.
        $statusId = (($parsed['tahap'] ?? null) ? Status::findByKey($parsed['tahap'])?->id : null)
            ?? DB::table('statuses')->where('key', 'open')->value('id')
            ?? throw new RuntimeException('Status "open" (Terbuka) tidak ada di tabel statuses.');

        $case = $this->writer->save(null, [
            'source_ref' => $sourceRef,
            'category_ids' => array_map('intval', $categoryIds),
            'status_id' => $statusId,
            'kualifikasi' => $parsed['kualifikasi'] ?? null,
            'event_date' => $eventDate,
            'latitude' => $lat,
            'longitude' => $lng,
            // Hasil impor selalu draft: admin memeriksa lalu mempublikasikan dari halaman detail.
            'is_public' => false,
            'created_by' => auth()->id(),
            'verified_by' => auth()->id(),
            'pelapor' => $parsed['pelapor'],
            'terlapor' => $parsed['terlapor'],
            'instansi' => $parsed['instansi'],
            'sumber' => $parsed['sumber'],
            'status_narasi' => $parsed['status'],
        ], [
            'id' => [
                'title' => $parsed['title'],
                'description' => $parsed['description'],
                'perkembangan' => $parsed['perkembangan'],
                'pembelajaran' => $parsed['pembelajaran'],
                'dugaan_permasalahan' => $parsed['dugaan_permasalahan'],
                'keterangan_tahapan' => $parsed['keterangan_tahapan'] ?? null,
            ],
            // Baris EN kosong seperti kasus baru dari form; bisa diisi lewat Auto Translate.
            'en' => array_fill_keys(['title', 'description', 'perkembangan', 'pembelajaran', 'dugaan_permasalahan'], ''),
        ]);

        Cache::forget($this->cacheKey($batch, $sourceRef));

        return $case;
    }

    private function cacheKey(string $batch, string $sourceRef): string
    {
        return "case-import:{$batch}:".sha1($sourceRef);
    }
}
