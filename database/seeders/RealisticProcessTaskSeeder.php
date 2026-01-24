<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisticProcessTaskSeeder extends Seeder
{
    public function run(): void
    {
        $categorySlugs = [
            'corruption',
            'environment',
            'violence',
            'fraud',
            'labor',
            'consumer',
        ];

        $templates = [
            'corruption' => [
                [
                    'process' => ['order_no' => 1, 'name_id' => 'Penelaahan Awal', 'name_en' => 'Preliminary Review'],
                    'tasks' => [
                        ['name_id' => 'Kumpulkan Bukti Transaksi', 'name_en' => 'Collect Transaction Evidence', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Upload bukti transfer (PDF/JPG)', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Tanggal transaksi', 'field_type' => 'date', 'is_required' => 1],
                        ]],
                        ['name_id' => 'Verifikasi Dokumen Lelang', 'name_en' => 'Verify Procurement Documents', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Upload dokumen lelang', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Catatan pemeriksaan', 'field_type' => 'text', 'is_required' => 0],
                        ]],
                    ],
                ],
                [
                    'process' => ['order_no' => 2, 'name_id' => 'Wawancara & Klarifikasi', 'name_en' => 'Interviews & Clarification'],
                    'tasks' => [
                        ['name_id' => 'Wawancara Saksi Utama', 'name_en' => 'Interview Key Witness', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Nama saksi', 'field_type' => 'text', 'is_required' => 1],
                            ['name' => 'Tanggal wawancara', 'field_type' => 'date', 'is_required' => 1],
                            ['name' => 'Hasil wawancara', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                        ['name_id' => 'Analisis Legal', 'name_en' => 'Legal Analysis', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Upload memo legal', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Ringkasan hasil analisis', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                    ],
                ],
                [
                    'process' => ['order_no' => 3, 'name_id' => 'Analisis Keuangan & Bukti Digital', 'name_en' => 'Financial Analysis & Digital Evidence'],
                    'tasks' => [
                        ['name_id' => 'Periksa Rekening & Aliran Dana', 'name_en' => 'Review Bank Accounts & Financial Flows', 'due_days' => 10, 'requirements' => [
                            ['name' => 'Upload rekening bank (file)', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Ringkasan aliran dana', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                        ['name_id' => 'Ambil Bukti Digital', 'name_en' => 'Collect Digital Evidence', 'due_days' => 3, 'requirements' => [
                            ['name' => 'Upload bukti digital (screenshot/metadata)', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Hash/metadata', 'field_type' => 'text', 'is_required' => 0],
                        ]],
                        ['name_id' => 'Susun Laporan Awal Penyelidikan', 'name_en' => 'Draft Initial Investigation Report', 'due_days' => 14, 'requirements' => [
                            ['name' => 'Draft laporan (file)', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Rekomendasi awal', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                    ],
                ],
            ],

            'environment' => [
                [
                    'process' => ['order_no' => 1, 'name_id' => 'Inspeksi Lapangan', 'name_en' => 'Field Inspection'],
                    'tasks' => [
                        ['name_id' => 'Ambil Sampel Air', 'name_en' => 'Collect Water Samples', 'due_days' => 3, 'requirements' => [
                            ['name' => 'Foto lokasi', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Hasil uji awal', 'field_type' => 'file', 'is_required' => 0],
                        ]],
                        ['name_id' => 'Pemeriksaan Izin', 'name_en' => 'Permit Check', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Upload izin usaha', 'field_type' => 'file', 'is_required' => 1],
                        ]],
                    ],
                ],
                [
                    'process' => ['order_no' => 2, 'name_id' => 'Analisis Dampak', 'name_en' => 'Impact Analysis'],
                    'tasks' => [
                        ['name_id' => 'Analisis Laboratorium', 'name_en' => 'Laboratory Analysis', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Laporan laboratorium', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Kesimpulan singkat', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                    ],
                ],
            ],

            'violence' => [
                [
                    'process' => ['order_no' => 1, 'name_id' => 'Penanganan Korban', 'name_en' => 'Victim Handling'],
                    'tasks' => [
                        ['name_id' => 'Dokumentasi Luka', 'name_en' => 'Document Injuries', 'due_days' => 2, 'requirements' => [
                            ['name' => 'Foto luka', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Keterangan medis', 'field_type' => 'file', 'is_required' => 0],
                        ]],
                        ['name_id' => 'Pendampingan Psikologis', 'name_en' => 'Psychological Support', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Catatan sesi', 'field_type' => 'text', 'is_required' => 0],
                        ]],
                    ],
                ],
                [
                    'process' => ['order_no' => 2, 'name_id' => 'Pengumpulan Bukti', 'name_en' => 'Evidence Collection'],
                    'tasks' => [
                        ['name_id' => 'Kumpulkan Bukti Digital', 'name_en' => 'Collect Digital Evidence', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Logging chat/rekaman', 'field_type' => 'file', 'is_required' => 1],
                        ]],
                    ],
                ],
            ],

            'fraud' => [
                [
                    'process' => ['order_no' => 1, 'name_id' => 'Analisis Transaksi', 'name_en' => 'Transaction Analysis'],
                    'tasks' => [
                        ['name_id' => 'Review Rekening Korban', 'name_en' => 'Review Victim Accounts', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Bank statement (file)', 'field_type' => 'file', 'is_required' => 1],
                            ['name' => 'Jumlah kerugian (nominal)', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                        ['name_id' => 'Verifikasi Identitas Pelaku', 'name_en' => 'Verify Perpetrator Identity', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Dokumen identitas', 'field_type' => 'file', 'is_required' => 1],
                        ]],
                    ],
                ],
            ],

            'labor' => [
                [
                    'process' => ['order_no' => 1, 'name_id' => 'Klarifikasi Pekerjaan', 'name_en' => 'Work Clarification'],
                    'tasks' => [
                        ['name_id' => 'Cek Kontrak Kerja', 'name_en' => 'Check Employment Contract', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Upload kontrak (file)', 'field_type' => 'file', 'is_required' => 1],
                        ]],
                        ['name_id' => 'Wawancara Pekerja', 'name_en' => 'Interview Worker', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Catatan wawancara', 'field_type' => 'text', 'is_required' => 1],
                        ]],
                    ],
                ],
            ],

            'consumer' => [
                [
                    'process' => ['order_no' => 1, 'name_id' => 'Verifikasi Produk', 'name_en' => 'Product Verification'],
                    'tasks' => [
                        ['name_id' => 'Uji Kualitas Produk', 'name_en' => 'Product Quality Test', 'due_days' => 7, 'requirements' => [
                            ['name' => 'Laporan pengujian', 'field_type' => 'file', 'is_required' => 1],
                        ]],
                        ['name_id' => 'Verifikasi Izin Distribusi', 'name_en' => 'Verify Distribution Permit', 'due_days' => 5, 'requirements' => [
                            ['name' => 'Upload izin distribusi', 'field_type' => 'file', 'is_required' => 1],
                        ]],
                    ],
                ],
            ],
        ];

        foreach ($categorySlugs as $slug) {
            $category = DB::table('categories')->where('slug', $slug)->first();
            if (! $category) {
                $this->command->info("Skipping missing category: {$slug}");
                continue;
            }

            // Remove existing processes (cascade deletes tasks and requirements)
            DB::table('processes')->where('category_id', $category->id)->delete();

            if (! isset($templates[$slug])) {
                $this->command->info("No templates defined for {$slug}");
                continue;
            }

            foreach ($templates[$slug] as $procTpl) {
                $processId = DB::table('processes')->insertGetId([
                    'category_id' => $category->id,
                    'order_no' => $procTpl['process']['order_no'],
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // translations
                DB::table('process_translations')->insert([
                    ['process_id' => $processId, 'locale' => 'id', 'name' => $procTpl['process']['name_id'], 'created_at' => now(), 'updated_at' => now()],
                    ['process_id' => $processId, 'locale' => 'en', 'name' => $procTpl['process']['name_en'], 'created_at' => now(), 'updated_at' => now()],
                ]);

                foreach ($procTpl['tasks'] as $taskTpl) {
                    $taskId = DB::table('tasks')->insertGetId([
                        'process_id' => $processId,
                        'due_days' => $taskTpl['due_days'] ?? null,
                        'is_required' => $taskTpl['is_required'] ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('task_translations')->insert([
                        ['task_id' => $taskId, 'locale' => 'id', 'name' => $taskTpl['name_id'], 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                        ['task_id' => $taskId, 'locale' => 'en', 'name' => $taskTpl['name_en'], 'description' => null, 'created_at' => now(), 'updated_at' => now()],
                    ]);

                    foreach ($taskTpl['requirements'] as $req) {
                        DB::table('task_requirements')->insert([
                            'task_id' => $taskId,
                            'name' => $req['name'],
                            'field_type' => $req['field_type'] ?? 'text',
                            'is_required' => $req['is_required'] ?? 1,
                            'options' => isset($req['options']) ? json_encode($req['options']) : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $this->command->info("Seeded processes/tasks for category: {$slug}");
        }
    }
}
