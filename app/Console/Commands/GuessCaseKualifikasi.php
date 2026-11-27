<?php

namespace App\Console\Commands;

use App\Models\{CaseModel, CaseTranslation, Status};
use App\Services\CaseImport\KualifikasiGuesser;
use App\Services\CaseStages;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Mengisi kualifikasi kasus:
 *  1. Kasus yang isinya memuat bagian "Kualifikasi Perkara"/"Kualifikasi Kasus" dari dokumen impor (judul itu dulu
 *     belum dikenali, jadi ikut masuk ke teks Perkembangan dsb.) memakai nilai yang tertulis di dokumen, menggantikan
 *     isian sebelumnya; teks bagian itu (ID & EN) dibuang dari isi kasus.
 *  2. Kasus yang kualifikasinya masih kosong diisi tebakan dari isi kasusnya (lihat KualifikasiGuesser).
 * Bila jalurnya berubah, keterangan tahapan & status disesuaikan dengan tahapan jalur baru (mis. Vonis → Sanksi).
 * Jalankan case:sync-tahapan lebih dulu agar kasus yang dokumen Drive-nya punya bagian "Kualifikasi Kasus"
 * memakai isi dokumen, bukan tebakan.
 */
class GuessCaseKualifikasi extends Command
{
    protected $signature = 'case:isi-kualifikasi
        {--dry-run : Tampilkan perubahan tanpa menyimpan}
        {--case=* : Nomor kasus tertentu (bawaan: semua kasus)}';

    protected $description = 'Isi kualifikasi kasus dari bagian "Kualifikasi Perkara" di isinya, atau tebakan bila kosong';

    /** Kolom teks yang bisa memuat bagian kualifikasi dari dokumen impor */
    private const TRANSLATION_FIELDS = ['description', 'perkembangan', 'dugaan_permasalahan', 'pembelajaran'];

    private const CASE_FIELDS = ['status_narasi', 'sumber'];

    /** Paragraf judul "Kualifikasi Perkara"/"Kualifikasi Kasus" (boleh tebal, boleh "Judul: nilai"), lalu paragraf nilainya. */
    private const WRITTEN_ID = '~<p>\s*(?:<strong>)?\s*Kualifikasi\s+(?:Perkara|Kasus)\s*(?::\s*(?:</strong>)?\s*(?<inline>[^<]{1,300}?)\s*(?:</strong>)?\s*</p>|:?\s*(?:</strong>)?\s*</p>(?:\s*<p>\s*(?:<strong>)?\s*(?<value>(?:pidana|perdata|administrasi|-|–)[^<]{0,300}?)\s*(?:</strong>)?\s*</p>)?)~iu';

    /** Terjemahan DeepL bagian itu: "Case Classification", "Classification of the Case", "Case Qualifications", "Case Details", dsb. */
    private const WRITTEN_EN = '~<p>\s*(?:<strong>)?\s*(?:(?:(?:Case|Criminal|Legal)\s+)?(?:Classification|Qualifications?)(?:\s+of\s+(?:the\s+)?Case)?|Case\s+Details)\s*:?\s*(?:</strong>)?\s*</p>(?:\s*<p>\s*(?:<strong>)?\s*(?:Criminal|Civil|Administrative|-|–)[^<]{0,300}?\s*(?:</strong>)?\s*</p>)?~iu';

    public function handle(KualifikasiGuesser $guesser): int
    {
        $cases = CaseModel::with(['status', 'translations'])
            ->when($this->option('case'), fn ($query, $numbers) => $query->whereIn('case_number', $numbers))
            ->orderBy('id')
            ->get();

        $rows = [];
        $totals = ['dokumen' => 0, 'tebakan' => 0];
        foreach ($cases as $case) {
            $id = $case->translations->firstWhere('locale', 'id') ?? $case->translations->first();
            $en = $case->translations->firstWhere('locale', 'en');
            $written = $this->written($case, $id, $en);

            if ($written !== null) {
                $kualifikasi = $written['kualifikasi'] ?? $case->kualifikasi;
                $reason = 'dokumen: '.Str::limit($written['value'] ?? '-', 40);
                $totals['dokumen']++;
            } elseif ($case->kualifikasi === null) {
                $guess = $guesser->guess([
                    'title' => $id?->title,
                    'status' => $case->status_narasi,
                    'perkembangan' => $id?->perkembangan,
                    'dugaan_permasalahan' => $id?->dugaan_permasalahan,
                    'description' => $id?->description,
                ], $case->status?->key);
                ['kualifikasi' => $kualifikasi, 'reason' => $reason] = $guess;
                $reason = 'tebakan: '.$reason;
                $totals['tebakan']++;
            } else {
                continue;
            }

            $stages = $this->realign($case, $kualifikasi, [$id, $en]);
            $rows[] = [
                $case->case_number,
                Str::limit((string) $id?->title, 45),
                $this->change($case->kualifikasi ? CaseModel::KUALIFIKASI[$case->kualifikasi] : null, $kualifikasi ? CaseModel::KUALIFIKASI[$kualifikasi] : null),
                $this->change($case->status?->name, $stages['status']?->name ?? $case->status?->name),
                $reason,
            ];

            if ($this->option('dry-run')) {
                continue;
            }

            $case->update(array_filter([
                'kualifikasi' => $kualifikasi,
                'status_id' => $stages['status']?->id,
            ]) + ($written['case'] ?? []));
            foreach ([$id, $en] as $translation) {
                if ($translation === null) {
                    continue;
                }
                $changes = ($written['translations'][$translation->locale] ?? [])
                    + (isset($stages['notes'][$translation->locale]) ? ['keterangan_tahapan' => $stages['notes'][$translation->locale]] : []);
                if ($changes) {
                    $translation->update($changes);
                }
            }
        }

        if ($rows) {
            $this->table(['Kasus', 'Judul', 'Kualifikasi', 'Status', 'Sumber'], $rows);
        }

        $this->info(sprintf(
            '%d kasus %s: %d dari bagian Kualifikasi Perkara di dokumen, %d tebakan.',
            count($rows), $this->option('dry-run') ? 'akan diubah (dry run, belum disimpan)' : 'diubah', $totals['dokumen'], $totals['tebakan'],
        ));
        if ($totals['tebakan']) {
            $this->line('Kasus yang tebakannya kurang tepat bisa diubah di form edit kasus di CMS.');
        }

        return self::SUCCESS;
    }

    /**
     * Bagian "Kualifikasi Perkara" yang ikut tersimpan di isi kasus: nilainya, dan isi kolom tanpa bagian itu.
     *
     * @return array{value: ?string, kualifikasi: ?string, case: array<string, string>, translations: array<string, array<string, string>>}|null
     */
    private function written(CaseModel $case, ?CaseTranslation $id, ?CaseTranslation $en): ?array
    {
        $found = null;
        $result = ['value' => null, 'kualifikasi' => null, 'case' => [], 'translations' => []];

        $fields = [
            ...array_map(fn ($field) => [$case, $field, 'case'], self::CASE_FIELDS),
            ...array_map(fn ($field) => [$id, $field, 'id'], $id ? self::TRANSLATION_FIELDS : []),
        ];
        foreach ($fields as [$model, $field, $scope]) {
            $html = (string) $model->{$field};
            if (! preg_match(self::WRITTEN_ID, $html, $m)) {
                continue;
            }

            $found = true;
            $value = trim(html_entity_decode(($m['inline'] ?? '') !== '' ? $m['inline'] : ($m['value'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($value !== '' && $result['value'] === null) {
                $result['value'] = $value;
                foreach (array_keys(CaseModel::KUALIFIKASI) as $key) {
                    if (Str::contains(Str::lower($value), $key)) {
                        $result['kualifikasi'] = $key;
                        break;
                    }
                }
            }

            $clean = trim(preg_replace(self::WRITTEN_ID, '', $html));
            if ($scope === 'case') {
                $result['case'][$field] = $clean;
            } else {
                $result['translations']['id'][$field] = $clean;
                // Terjemahan bagian yang sama di kolom EN-nya.
                if ($en && preg_match(self::WRITTEN_EN, (string) $en->{$field})) {
                    $result['translations']['en'][$field] = trim(preg_replace(self::WRITTEN_EN, '', (string) $en->{$field}));
                }
            }
        }

        return $found ? $result : null;
    }

    /**
     * Keterangan tahapan per bahasa dan status kasus untuk jalur baru. Keterangan dipetakan seperti tabel tahapan
     * dokumen (mis. Vonis → Sanksi pada administrasi); keterangan tahap yang tidak ada di jalur baru tetap disimpan.
     * Status hanya diganti bila status sekarang bukan bagian jalur baru dan keterangan tahapan menunjukkan tahapnya.
     * Dijalankan juga bila kualifikasinya tidak berubah: case:sync-tahapan hanya memperbarui keterangan ID, jadi
     * keterangan EN bisa masih memakai tahapan jalur lama.
     *
     * @param  list<?CaseTranslation>  $translations
     * @return array{notes: array<string, array<string, string>>, status: ?Status}
     */
    private function realign(CaseModel $case, ?string $kualifikasi, array $translations): array
    {
        $result = ['notes' => [], 'status' => null];
        $derived = null;
        foreach (array_filter($translations) as $translation) {
            $notes = $translation->keterangan_tahapan ?: [];
            if ($notes === []) {
                continue;
            }

            $mapped = CaseStages::fromDocument($kualifikasi, array_map(
                fn ($key, $text) => ['label' => $key, 'text' => $text], array_keys($notes), $notes,
            ));
            // Keterangan tahap yang tidak ada di jalur baru tidak tampil, tapi tetap disimpan.
            $realigned = $mapped['notes'] + array_intersect_key($notes, array_flip($mapped['skipped']));
            if ($realigned != $notes) {
                $result['notes'][$translation->locale] = $realigned;
            }
            $derived ??= $mapped['status'];
        }

        $statusKey = $case->status?->key;
        if ($derived !== null && $statusKey !== $derived && ! CaseStages::inTrack($kualifikasi, $statusKey)) {
            $result['status'] = Status::findByKey($derived);
        }

        return $result;
    }

    private function change(?string $from, ?string $to): string
    {
        return $from === $to ? ($from ?? '-') : ($from ?? '-').' → '.($to ?? '-');
    }
}
