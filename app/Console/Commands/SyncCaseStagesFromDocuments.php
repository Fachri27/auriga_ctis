<?php

namespace App\Console\Commands;

use App\Models\{CaseModel, Status};
use App\Services\CaseImport\{CaseDocumentParser, DocxReader};
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Kasus yang sudah diimpor tidak ikut dibaca ulang di halaman impor. Perintah ini membaca ulang dokumen Google Drive-nya
 * dan menerapkan bagian "Kualifikasi Kasus" dan "Tahapan Kasus": kualifikasi, keterangan tahapan (ID), dan status.
 * Dokumen tanpa kedua bagian itu (format lama) dilewati; isi kasus lain tidak diubah.
 */
class SyncCaseStagesFromDocuments extends Command
{
    protected $signature = 'case:sync-tahapan
        {--dry-run : Tampilkan perubahan tanpa menyimpan}
        {--case=* : Nomor kasus tertentu (bawaan: semua kasus hasil impor Google Drive)}';

    protected $description = 'Perbarui kualifikasi, keterangan tahapan, dan status kasus hasil impor dari dokumen Google Drive-nya';

    public function handle(GoogleDriveService $drive, DocxReader $reader, CaseDocumentParser $parser): int
    {
        $cases = CaseModel::with(['status', 'translations'])
            ->where('source_ref', 'like', 'gdrive:%')
            ->when($this->option('case'), fn ($query, $numbers) => $query->whereIn('case_number', $numbers))
            ->orderBy('id')
            ->get();

        $rows = [];
        $oldFormat = 0;
        $failed = 0;

        foreach ($cases as $case) {
            $fileId = Str::after($case->source_ref, 'gdrive:');

            try {
                $parsed = $parser->parse($reader->read($drive->downloadDocx($fileId, $drive->file($fileId)['mimeType'])));
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("{$case->case_number}: gagal dibaca ({$e->getMessage()})");

                continue;
            }

            if (! array_intersect(['kualifikasi', 'tahapan'], $parsed['sections'])) {
                $oldFormat++;

                continue;
            }

            $translation = $case->translations->firstWhere('locale', 'id');
            $notes = $parsed['keterangan_tahapan'];
            $status = $parsed['tahap'] ? Status::findByKey($parsed['tahap']) : null;
            $changes = array_filter([
                'kualifikasi' => $parsed['kualifikasi'] && $parsed['kualifikasi'] !== $case->kualifikasi,
                // Keterangan dari dokumen menggantikan yang ada; dokumen tanpa keterangan tidak menghapus isian CMS.
                'keterangan' => $notes && $translation && $notes !== ($translation->keterangan_tahapan ?? []),
                'status' => $status && $status->id !== $case->status_id,
            ]);

            $rows[] = [
                $case->case_number,
                Str::limit((string) $translation?->title, 45),
                $this->change($case->kualifikasi, $parsed['kualifikasi'], isset($changes['kualifikasi'])),
                $this->change($case->status?->name, $status?->name, isset($changes['status'])),
                $notes ? implode(', ', array_keys($notes)).(isset($changes['keterangan']) ? '' : ' (sama)') : '-',
                implode(' ', $parsed['warnings']),
            ];

            if ($changes === [] || $this->option('dry-run')) {
                continue;
            }

            $case->update(array_filter([
                'kualifikasi' => isset($changes['kualifikasi']) ? $parsed['kualifikasi'] : null,
                'status_id' => isset($changes['status']) ? $status->id : null,
            ]));
            if (isset($changes['keterangan'])) {
                $translation->update(['keterangan_tahapan' => $notes]);
            }
        }

        if ($rows) {
            $this->table(['Kasus', 'Judul', 'Kualifikasi', 'Status', 'Keterangan tahapan', 'Catatan'], $rows);
        }
        $this->info(sprintf(
            '%d dokumen berformat baru%s, %d format lama (dilewati), %d gagal dibaca.',
            count($rows), $this->option('dry-run') ? ' (dry run, belum disimpan)' : '', $oldFormat, $failed,
        ));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function change(?string $from, ?string $to, bool $changed): string
    {
        return $changed ? ($from ?? '-').' → '.$to : ($from ?? '-');
    }
}
