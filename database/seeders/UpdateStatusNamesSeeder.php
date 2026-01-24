<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateStatusNamesSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'open' => 'Terbuka',
            'verified' => 'Terverifikasi',
            'converted' => 'Dikonversi',
            'rejected' => 'Ditolak',
            'investigation' => 'Investigasi',
            'prosecution' => 'Penuntutan',
            'trial' => 'Persidangan',
            'executed' => 'Putusan Dijalankan',
            'closed' => 'Ditutup',
            'completed' => 'Selesai',
            'published' => 'Dipublikasikan',
        ];

        foreach ($map as $key => $name) {
            DB::table('statuses')->updateOrInsert(
                ['key' => $key],
                ['name' => $name, 'updated_at' => now()]
            );
        }
    }
}
