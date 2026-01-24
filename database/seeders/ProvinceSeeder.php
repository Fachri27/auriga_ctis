<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\District;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['code' => 'ID-JK', 'name' => 'DKI Jakarta'],
            ['code' => 'ID-JB', 'name' => 'Jawa Barat'],
            ['code' => 'ID-JT', 'name' => 'Jawa Tengah'],
        ];

        foreach ($provinces as $provData) {
            $province = Province::create($provData);

            District::insert([
                [
                    'province_id' => $province->id,
                    'code' => $provData['code'] . '-01',
                    'name' => $provData['name'] . ' - District A'
                ],
                [
                    'province_id' => $province->id,
                    'code' => $provData['code'] . '-02',
                    'name' => $provData['name'] . ' - District B'
                ],
            ]);
        }
    }
}
