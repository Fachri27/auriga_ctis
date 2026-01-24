<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\CategoryTranslation;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'corruption',  'icon' => 'shield', 'is_active' => 1],
            ['slug' => 'environment', 'icon' => 'leaf',   'is_active' => 1],
            ['slug' => 'violence',    'icon' => 'alert',  'is_active' => 1],
            ['slug' => 'fraud',       'icon' => 'credit-card', 'is_active' => 1],
            ['slug' => 'labor',       'icon' => 'briefcase', 'is_active' => 1],
            ['slug' => 'consumer',    'icon' => 'shopping-cart', 'is_active' => 1],
        ];

        $names = [
            'corruption'  => ['Korupsi', 'Corruption'],
            'environment' => ['Lingkungan', 'Environment'],
            'violence'    => ['Kekerasan', 'Violence'],
            'fraud'       => ['Penipuan', 'Fraud'],
            'labor'       => ['Ketenagakerjaan', 'Labor'],
            'consumer'    => ['Perlindungan Konsumen', 'Consumer Protection'],
        ];

        foreach ($categories as $catData) {
            // Make idempotent: insert or update category
            $categoryId = DB::table('categories')->updateOrInsert(
                ['slug' => $catData['slug']],
                [
                    'icon' => $catData['icon'],
                    'is_active' => $catData['is_active'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $category = DB::table('categories')->where('slug', $catData['slug'])->first();

            // translations (idempotent)
            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'id'],
                ['name' => $names[$catData['slug']][0], 'description' => 'Deskripsi ID untuk ' . $catData['slug'], 'updated_at' => now(), 'created_at' => now()]
            );

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'en'],
                ['name' => $names[$catData['slug']][1], 'description' => 'English description for ' . $catData['slug'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
