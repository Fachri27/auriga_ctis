<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['code' => 'ID-AC', 'name' => 'Aceh'],
            ['code' => 'ID-SU', 'name' => 'Sumatera Utara'],
            ['code' => 'ID-SB', 'name' => 'Sumatera Barat'],
            ['code' => 'ID-RI', 'name' => 'Riau'],
            ['code' => 'ID-JA', 'name' => 'Jambi'],
            ['code' => 'ID-SS', 'name' => 'Sumatera Selatan'],
            ['code' => 'ID-BE', 'name' => 'Bengkulu'],
            ['code' => 'ID-LA', 'name' => 'Lampung'],
            ['code' => 'ID-BB', 'name' => 'Kepulauan Bangka Belitung'],
            ['code' => 'ID-KR', 'name' => 'Kepulauan Riau'],
            ['code' => 'ID-JK', 'name' => 'DKI Jakarta'],
            ['code' => 'ID-JB', 'name' => 'Jawa Barat'],
            ['code' => 'ID-JT', 'name' => 'Jawa Tengah'],
            ['code' => 'ID-YO', 'name' => 'Daerah Istimewa Yogyakarta'],
            ['code' => 'ID-JI', 'name' => 'Jawa Timur'],
            ['code' => 'ID-BT', 'name' => 'Banten'],
            ['code' => 'ID-BA', 'name' => 'Bali'],
            ['code' => 'ID-NB', 'name' => 'Nusa Tenggara Barat'],
            ['code' => 'ID-NT', 'name' => 'Nusa Tenggara Timur'],
            ['code' => 'ID-KB', 'name' => 'Kalimantan Barat'],
            ['code' => 'ID-KT', 'name' => 'Kalimantan Tengah'],
            ['code' => 'ID-KS', 'name' => 'Kalimantan Selatan'],
            ['code' => 'ID-KI', 'name' => 'Kalimantan Timur'],
            ['code' => 'ID-KU', 'name' => 'Kalimantan Utara'],
            ['code' => 'ID-SA', 'name' => 'Sulawesi Utara'],
            ['code' => 'ID-ST', 'name' => 'Sulawesi Tengah'],
            ['code' => 'ID-SR', 'name' => 'Sulawesi Selatan'],
            ['code' => 'ID-SG', 'name' => 'Sulawesi Tenggara'],
            ['code' => 'ID-GO', 'name' => 'Gorontalo'],
            ['code' => 'ID-MA', 'name' => 'Sulawesi Barat'],
            ['code' => 'ID-MU', 'name' => 'Maluku Utara'],
            ['code' => 'ID-ML', 'name' => 'Maluku'],
            ['code' => 'ID-PA', 'name' => 'Papua'],
            ['code' => 'ID-PB', 'name' => 'Papua Barat'],
            ['code' => 'ID-PT', 'name' => 'Papua Tengah'],
            ['code' => 'ID-PS', 'name' => 'Papua Selatan'],
            ['code' => 'ID-PE', 'name' => 'Papua Pegunungan'],
            ['code' => 'ID-PD', 'name' => 'Papua Barat Daya'],
        ];

        foreach ($provinces as $p) {
            Province::firstOrCreate(['code' => $p['code']], $p);
        }

        $this->command->info('38 provinces seeded successfully.');
    }
}
