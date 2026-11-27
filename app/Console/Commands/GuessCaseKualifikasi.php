<?php

namespace App\Console\Commands;

use App\Models\CaseModel;
use App\Services\CaseImport\KualifikasiGuesser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Mengisi kualifikasi kasus yang masih kosong dengan tebakan dari isi kasusnya (lihat KualifikasiGuesser).
 * Kasus yang kualifikasinya sudah terisi tidak diubah. Jalankan case:sync-tahapan lebih dulu agar kasus yang
 * dokumen Drive-nya punya bagian "Kualifikasi Kasus" memakai isi dokumen, bukan tebakan.
 */
class GuessCaseKualifikasi extends Command
{
    protected $signature = 'case:isi-kualifikasi
        {--dry-run : Tampilkan tebakan tanpa menyimpan}
        {--case=* : Nomor kasus tertentu (bawaan: semua kasus yang kualifikasinya kosong)}';

    protected $description = 'Isi kualifikasi kasus yang masih kosong dengan tebakan dari isi kasusnya';

    public function handle(KualifikasiGuesser $guesser): int
    {
        $cases = CaseModel::with(['status', 'translations'])
            ->whereNull('kualifikasi')
            ->when($this->option('case'), fn ($query, $numbers) => $query->whereIn('case_number', $numbers))
            ->orderBy('id')
            ->get();

        $rows = [];
        $totals = [];
        foreach ($cases as $case) {
            $translation = $case->translations->firstWhere('locale', 'id') ?? $case->translations->first();
            $guess = $guesser->guess([
                'title' => $translation?->title,
                'status' => $case->status_narasi,
                'perkembangan' => $translation?->perkembangan,
                'dugaan_permasalahan' => $translation?->dugaan_permasalahan,
                'description' => $translation?->description,
            ], $case->status?->key);

            $rows[] = [
                $case->case_number,
                Str::limit((string) $translation?->title, 50),
                $case->status?->name ?? '-',
                CaseModel::KUALIFIKASI[$guess['kualifikasi']],
                $guess['reason'],
            ];
            $totals[$guess['kualifikasi']] = ($totals[$guess['kualifikasi']] ?? 0) + 1;

            if (! $this->option('dry-run')) {
                $case->update(['kualifikasi' => $guess['kualifikasi']]);
            }
        }

        if ($rows) {
            $this->table(['Kasus', 'Judul', 'Status', 'Kualifikasi', 'Alasan'], $rows);
        }

        $summary = collect(CaseModel::KUALIFIKASI)
            ->map(fn ($label, $key) => ($totals[$key] ?? 0).' '.$label)
            ->implode(', ');
        $this->info(count($rows).' kasus '.($this->option('dry-run') ? 'akan diisi (dry run, belum disimpan)' : 'diisi').": {$summary}.");
        if ($rows) {
            $this->line('Kasus yang tebakannya kurang tepat bisa diubah di form edit kasus di CMS.');
        }

        return self::SUCCESS;
    }
}
