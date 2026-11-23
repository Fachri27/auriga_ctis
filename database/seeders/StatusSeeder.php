<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Status mengikuti urutan lifecycle "bar status" (progress bar) pada
     * case-detail: open → verified → published → penyelidikan → investigation →
     * penyidikan → prosecution → trial → (perdata: gugatan → pembuktian) → vonis →
     * (administrasi: sanksi) → berkekuatan-hukum-tetap → executed → completed →
     * closed → rejected → converted. Lihat App\Services\CaseStages.
     *
     * Menggunakan updateOrCreate agar idempotent: baris lama dipertahankan
     * (ID tidak berubah → FK cases.status_id tetap valid), baris baru disisipkan.
     */
    public function run(): void
    {
        $statuses = [
            ['key' => 'open',                       'name' => 'Terbuka'],
            ['key' => 'verified',                   'name' => 'Terverifikasi'],
            ['key' => 'published',                  'name' => 'Dipublikasikan'],
            ['key' => 'penyelidikan',               'name' => 'Penyelidikan'],
            ['key' => 'investigation',              'name' => 'Investigasi'],
            ['key' => 'penyidikan',                 'name' => 'Penyidikan'],
            ['key' => 'prosecution',                'name' => 'Penuntutan'],
            ['key' => 'trial',                      'name' => 'Persidangan'],
            ['key' => 'gugatan',                    'name' => 'Gugatan'],
            ['key' => 'pembuktian',                 'name' => 'Pembuktian'],
            ['key' => 'vonis',                      'name' => 'Vonis'],
            ['key' => 'sanksi',                     'name' => 'Sanksi'],
            ['key' => 'berkekuatan-hukum-tetap',    'name' => 'Berkekuatan Hukum Tetap'],
            ['key' => 'executed',                   'name' => 'Putusan Dijalankan'],
            ['key' => 'completed',                  'name' => 'Selesai'],
            ['key' => 'closed',                     'name' => 'Ditutup'],
            ['key' => 'rejected',                   'name' => 'Ditolak'],
            ['key' => 'converted',                  'name' => 'Dikonversi'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['key' => $status['key']],
                ['name' => $status['name']]
            );
        }
    }
}