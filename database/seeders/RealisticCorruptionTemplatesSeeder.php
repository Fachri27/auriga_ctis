<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisticCorruptionTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $category = DB::table('categories')->where('slug', 'corruption')->first();
        if (! $category) {
            return;
        }

        // Define processes and tasks for corruption category
        $templates = [
            [
                'process' => ['order_no' => 1],
                'translations' => ['id' => 'Penelaahan Awal', 'en' => 'Preliminary Review'],
                'tasks' => [
                    [
                        'name_id' => 'Kumpulkan Bukti Transaksi',
                        'name_en' => 'Collect Transaction Evidence',
                        'due_days' => 7,
                        'requirements' => [
                            ['name' => 'Upload bukti transfer (PDF/JPG)', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Catatan singkat bukti', 'field_type' => 'text', 'is_required' => 0],
                            ['name' => 'Tanggal transaksi', 'field_type' => 'date', 'is_required' => 1],
                        ],
                    ],
                    [
                        'name_id' => 'Verifikasi Dokumen Lelang',
                        'name_en' => 'Verify Procurement Documents',
                        'due_days' => 5,
                        'requirements' => [
                            ['name' => 'Upload dokumen lelang', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Catatan pemeriksaan', 'field_type' => 'text', 'is_required' => 0],
                        ],
                    ],
                ],
            ],
            [
                'process' => ['order_no' => 2],
                'translations' => ['id' => 'Wawancara & Klarifikasi', 'en' => 'Interviews & Clarification'],
                'tasks' => [
                    [
                        'name_id' => 'Wawancara Saksi Utama',
                        'name_en' => 'Interview Key Witness',
                        'due_days' => 7,
                        'requirements' => [
                            ['name' => 'Nama saksi', 'field_type' => 'text', 'is_required' => 1],
                            ['name' => 'Tanggal wawancara', 'field_type' => 'date', 'is_required' => 1],
                            ['name' => 'Catatan wawancara', 'field_type' => 'text', 'is_required' => 1],
                        ],
                    ],
                    [
                        'name_id' => 'Analisis Legal',
                        'name_en' => 'Legal Analysis',
                        'due_days' => 5,
                        'requirements' => [
                            ['name' => 'Upload memo legal', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Ringkasan hasil analisis', 'field_type' => 'text', 'is_required' => 1],
                        ],
                    ],
                    [
                        'name_id' => 'Draft Laporan Eksternal',
                        'name_en' => 'Draft External Report',
                        'due_days' => 3,
                        'requirements' => [
                            ['name' => 'Upload draft laporan', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Checklist persetujuan', 'field_type' => 'text', 'is_required' => 0],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($templates as $tpl) {
            $processId = DB::table('processes')->insertGetId([
                'category_id' => $category->id,
                'order_no' => $tpl['process']['order_no'],
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('process_translations')->insert([
                ['process_id' => $processId, 'locale' => 'id', 'name' => $tpl['translations']['id'], 'created_at' => now(), 'updated_at' => now()],
                ['process_id' => $processId, 'locale' => 'en', 'name' => $tpl['translations']['en'], 'created_at' => now(), 'updated_at' => now()],
            ]);

            foreach ($tpl['tasks'] as $t) {
                $taskId = DB::table('tasks')->insertGetId([
                    'process_id' => $processId,
                    'due_days' => $t['due_days'],
                    'is_required' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('task_translations')->insert([
                    ['task_id' => $taskId, 'locale' => 'id', 'name' => $t['name_id'], 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                    ['task_id' => $taskId, 'locale' => 'en', 'name' => $t['name_en'], 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                ]);

                foreach ($t['requirements'] as $req) {
                    DB::table('task_requirements')->insert([
                        'task_id' => $taskId,
                        'name' => $req['name'],
                        'field_type' => $req['field_type'],
                        'is_required' => $req['is_required'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
