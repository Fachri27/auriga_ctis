<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\ReportTranslation;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {

            $report = Report::create([

                'report_code' => 'RPT-' . str_pad($i, 3, '0', STR_PAD_LEFT),

                // IDENTITAS LENGKAP
                'nama_lengkap' => 'Pelapor '.$i,
                'nik' => '32760'.rand(100000000, 999999999),
                'jenis_kelamin' => $i % 2 === 0 ? 'L' : 'P',
                'tanggal_lahir' => now()->subYears(20 + $i),
                'alamat' => 'Jl. Contoh No. '.$i,
                'no_hp' => '08123'.rand(100000, 999999),
                'email' => 'pelapor'.$i.'@mail.com',
                'pekerjaan' => 'Karyawan Swasta',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Belum Kawin',

                // LOKASI
                'lat' => -6.2 + ($i * 0.01),
                'lng' => 106.8 + ($i * 0.01),

                // BUKTI
                'evidence' => [],

                // STATUS
                'status_id' => 1,

                // CATEGORY
                'category_id' => 1,

                // USER
                'created_by' => null,
            ]);

            // TRANSLATION
            ReportTranslation::insert([
                [
                    'report_id' => $report->id,
                    'locale' => 'id',
                    'description' => 'Laporan terkait aktivitas mencurigakan nomor '.$i
                ],
                [
                    'report_id' => $report->id,
                    'locale' => 'en',
                    'description' => 'Suspicious activity report number '.$i
                ],
            ]);
        }
    }
}
