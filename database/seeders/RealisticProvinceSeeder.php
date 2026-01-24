<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * RealisticProvinceSeeder
 *
 * Seeder untuk provinsi dan kabupaten/kota di Indonesia
 * Berdasarkan data real-life untuk pengujian sistem CTIS
 */
class RealisticProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            [
                'code' => 'ID-JK',
                'name' => 'DKI Jakarta',
                'districts' => [
                    ['code' => 'ID-JK-01', 'name' => 'Jakarta Pusat'],
                    ['code' => 'ID-JK-02', 'name' => 'Jakarta Utara'],
                    ['code' => 'ID-JK-03', 'name' => 'Jakarta Barat'],
                    ['code' => 'ID-JK-04', 'name' => 'Jakarta Selatan'],
                    ['code' => 'ID-JK-05', 'name' => 'Jakarta Timur'],
                ],
            ],
            [
                'code' => 'ID-JB',
                'name' => 'Jawa Barat',
                'districts' => [
                    ['code' => 'ID-JB-01', 'name' => 'Kota Bandung'],
                    ['code' => 'ID-JB-02', 'name' => 'Kota Bekasi'],
                    ['code' => 'ID-JB-03', 'name' => 'Kota Depok'],
                    ['code' => 'ID-JB-04', 'name' => 'Kabupaten Bogor'],
                    ['code' => 'ID-JB-05', 'name' => 'Kabupaten Karawang'],
                ],
            ],
            [
                'code' => 'ID-JT',
                'name' => 'Jawa Tengah',
                'districts' => [
                    ['code' => 'ID-JT-01', 'name' => 'Kota Semarang'],
                    ['code' => 'ID-JT-02', 'name' => 'Kota Surakarta'],
                    ['code' => 'ID-JT-03', 'name' => 'Kabupaten Magelang'],
                    ['code' => 'ID-JT-04', 'name' => 'Kabupaten Banyumas'],
                ],
            ],
            [
                'code' => 'ID-JI',
                'name' => 'Jawa Timur',
                'districts' => [
                    ['code' => 'ID-JI-01', 'name' => 'Kota Surabaya'],
                    ['code' => 'ID-JI-02', 'name' => 'Kota Malang'],
                    ['code' => 'ID-JI-03', 'name' => 'Kabupaten Sidoarjo'],
                    ['code' => 'ID-JI-04', 'name' => 'Kabupaten Gresik'],
                ],
            ],
            [
                'code' => 'ID-BA',
                'name' => 'Bali',
                'districts' => [
                    ['code' => 'ID-BA-01', 'name' => 'Kota Denpasar'],
                    ['code' => 'ID-BA-02', 'name' => 'Kabupaten Badung'],
                    ['code' => 'ID-BA-03', 'name' => 'Kabupaten Gianyar'],
                ],
            ],
        ];

        foreach ($provinces as $provData) {
            $provinceId = DB::table('provinces')->insertGetId([
                'code' => $provData['code'],
                'name' => $provData['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($provData['districts'] as $distData) {
                DB::table('districts')->insert([
                    'province_id' => $provinceId,
                    'code' => $distData['code'],
                    'name' => $distData['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

