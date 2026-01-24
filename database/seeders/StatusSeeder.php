<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('statuses')->insert([
            // Report Statuses
            ['key' => 'open', 'name' => 'Terbuka'],
            ['key' => 'verified', 'name' => 'Terverifikasi'],
            ['key' => 'converted', 'name' => 'Dikonversi'],
            ['key' => 'rejected', 'name' => 'Ditolak'],
            
            // Case Statuses (Legal Workflow)
            ['key' => 'investigation', 'name' => 'Investigasi'],
            ['key' => 'prosecution', 'name' => 'Penuntutan'],
            ['key' => 'trial', 'name' => 'Persidangan'],
            ['key' => 'executed', 'name' => 'Putusan Dijalankan'],
            ['key' => 'closed', 'name' => 'Ditutup'],
            
            // Legacy/Additional Statuses
            ['key' => 'completed', 'name' => 'Selesai'],
            ['key' => 'published', 'name' => 'Dipublikasikan'],
        ]);
    }
}
