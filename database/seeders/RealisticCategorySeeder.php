<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * RealisticCategorySeeder
 *
 * Seeder untuk kategori kasus realistis berdasarkan laporan kehidupan nyata
 * Sistem CTIS untuk melacak berbagai jenis kasus hukum/perkara
 */
class RealisticCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'corruption',
                'icon' => 'shield-alert',
                'is_active' => true,
                'translations' => [
                    ['locale' => 'id', 'name' => 'Korupsi', 'description' => 'Kasus korupsi, penyalahgunaan wewenang, dan tindak pidana korupsi lainnya'],
                    ['locale' => 'en', 'name' => 'Corruption', 'description' => 'Corruption cases, abuse of power, and other corruption crimes'],
                ],
            ],
            [
                'slug' => 'environment',
                'icon' => 'leaf',
                'is_active' => true,
                'translations' => [
                    ['locale' => 'id', 'name' => 'Lingkungan Hidup', 'description' => 'Kasus pencemaran lingkungan, illegal logging, dan pelanggaran lingkungan lainnya'],
                    ['locale' => 'en', 'name' => 'Environment', 'description' => 'Environmental pollution, illegal logging, and other environmental violations'],
                ],
            ],
            [
                'slug' => 'violence',
                'icon' => 'alert-triangle',
                'is_active' => true,
                'translations' => [
                    ['locale' => 'id', 'name' => 'Kekerasan', 'description' => 'Kasus kekerasan fisik, psikis, dan pelanggaran HAM'],
                    ['locale' => 'en', 'name' => 'Violence', 'description' => 'Physical violence, psychological violence, and human rights violations'],
                ],
            ],
            [
                'slug' => 'fraud',
                'icon' => 'credit-card',
                'is_active' => true,
                'translations' => [
                    ['locale' => 'id', 'name' => 'Penipuan', 'description' => 'Kasus penipuan, investasi bodong, dan tindakan menyesatkan lainnya'],
                    ['locale' => 'en', 'name' => 'Fraud', 'description' => 'Fraud cases, investment scams, and other deceptive practices'],
                ],
            ],
            [
                'slug' => 'labor',
                'icon' => 'briefcase',
                'is_active' => true,
                'translations' => [
                    ['locale' => 'id', 'name' => 'Ketenagakerjaan', 'description' => 'Kasus pelanggaran hak pekerja, upah tidak dibayar, dan perselisihan ketenagakerjaan'],
                    ['locale' => 'en', 'name' => 'Labor', 'description' => 'Workers rights violations, unpaid wages, and labor disputes'],
                ],
            ],
            [
                'slug' => 'consumer',
                'icon' => 'shopping-cart',
                'is_active' => true,
                'translations' => [
                    ['locale' => 'id', 'name' => 'Perlindungan Konsumen', 'description' => 'Kasus produk tidak layak, jasa menipu, dan pelanggaran hak konsumen'],
                    ['locale' => 'en', 'name' => 'Consumer Protection', 'description' => 'Defective products, deceptive services, and consumer rights violations'],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $categoryId = DB::table('categories')->insertGetId([
                'slug' => $catData['slug'],
                'icon' => $catData['icon'],
                'is_active' => $catData['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($catData['translations'] as $translation) {
                DB::table('category_translations')->insert([
                    'category_id' => $categoryId,
                    'locale' => $translation['locale'],
                    'name' => $translation['name'],
                    'description' => $translation['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

