<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Isi kualifikasi kasus yang sudah ada (lihat CaseModel::KUALIFIKASI), ditentukan dari perkembangan dan status
 * tiap kasus. Hanya mengisi kasus yang kualifikasinya masih kosong, jadi pilihan admin di CMS tidak tertimpa;
 * nomor kasus yang tidak ada di database dilewati.
 *
 *   php artisan db:seed --class=CaseKualifikasiSeeder
 *
 * Belum diisi (tetap "Belum ditentukan"): kasus tanpa proses hukum yang tercatat (CASE-EQ2NJ TPL Batang Toru,
 * CASE-INH2S PT SPT Subulussalam, CASE-2OPSK Korindo, CASE-OL29Y PT IFP), kasus uji, dan data dummy.
 */
class CaseKualifikasiSeeder extends Seeder
{
    private const KUALIFIKASI = [
        // Penyidikan, penuntutan, atau vonis pidana.
        'pidana' => [
            'CASE-N5VSE', 'CASE-XLTLR', 'CASE-IG72B', 'CASE-YSSZC', 'CASE-EGRJQ', 'CASE-JOLOA', 'CASE-JON6R',
            'CASE-PVT8Q', 'CASE-W9D2W', 'CASE-ROCFI', 'CASE-2024-001',
            // Tumpahan minyak Balikpapan juga punya sanksi administratif & gugatan warga; jalur utamanya vonis pidana.
            'CASE-QG0WF',
            'CASE-QTDJX', 'CASE-B4SBS', 'CASE-UFEH8', 'CASE-PJOV4', 'CASE-ZNCOA', 'CASE-WZPA3', 'CASE-TEYYN',
            'CASE-CBSBQ', 'CASE-U1KTN', 'CASE-Y75W6', 'CASE-MWTO1', 'CASE-VQ2RE', 'CASE-EJYRE', 'CASE-QKLVX',
            'CASE-HMWUP', 'CASE-2XY0I', 'CASE-IGFWD', 'CASE-ELIVL', 'CASE-WE216', 'CASE-UKF3D', 'CASE-NV4PJ',
            'CASE-DNT2R', 'CASE-HJRZQ', 'CASE-WF7N6', 'CASE-IR2JU', 'CASE-SWHTY', 'CASE-XRXFY', 'CASE-E2AWA',
            'CASE-QUVSU', 'CASE-S6IH9', 'CASE-FARHM', 'CASE-FLFK7', 'CASE-5BI9F', 'CASE-CHKO6', 'CASE-1DUZQ',
            'CASE-NYOTE', 'CASE-B3QMT', 'CASE-30WAA', 'CASE-9W8W7', 'CASE-TTGRH',
        ],

        // Gugatan perdata: ganti rugi, pemulihan lingkungan, gugatan warga negara.
        'perdata' => [
            'CASE-BVZKR', 'CASE-SWHUQ', 'CASE-BRWQO', 'CASE-1AECP', 'CASE-CDTBA', 'CASE-SEGII', 'CASE-6VFST',
            'CASE-D2HG7',
        ],

        // Tindakan pemerintah: pencabutan izin, penyegelan, perintah penghentian, penertiban kawasan hutan, reekspor,
        // atau sengketa tata usaha negara.
        'administrasi' => [
            'CASE-TQ3VI', 'CASE-IVVBQ', 'CASE-QOD2P', 'CASE-PPPOH', 'CASE-ORZAN', 'CASE-WE4MI', 'CASE-ZRNXH',
        ],
    ];

    public function run(): void
    {
        foreach (self::KUALIFIKASI as $kualifikasi => $caseNumbers) {
            $updated = DB::table('cases')
                ->whereIn('case_number', $caseNumbers)
                ->whereNull('kualifikasi')
                ->update(['kualifikasi' => $kualifikasi]);

            $this->command?->info("{$kualifikasi}: {$updated} kasus diisi");
        }
    }
}
