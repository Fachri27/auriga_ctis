<?php

namespace Database\Seeders;

use App\Services\CaseTaskGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * RealisticCaseSeeder
 *
 * Seeder untuk kasus realistis yang terhubung dengan laporan real-life
 * Menggunakan action-based workflow yang telah diimplementasikan
 */
class RealisticCaseSeeder extends Seeder
{
    public function run(): void
    {
        // Get status IDs
        $statusOpen = DB::table('statuses')->where('key', 'open')->value('id');
        $statusInvestigation = DB::table('statuses')->where('key', 'investigation')->value('id');
        $statusProsecution = DB::table('statuses')->where('key', 'prosecution')->value('id');
        $statusTrial = DB::table('statuses')->where('key', 'trial')->value('id');
        $statusExecuted = DB::table('statuses')->where('key', 'executed')->value('id');
        $statusClosed = DB::table('statuses')->where('key', 'closed')->value('id');

        // Get verified/converted reports
        $reports = DB::table('reports')
            ->whereIn('status_id', [
                DB::table('statuses')->where('key', 'verified')->value('id'),
                DB::table('statuses')->where('key', 'converted')->value('id'),
            ])
            ->get();

        // Get first user
        $firstUser = DB::table('users')->value('id');

        // Get categories
        $categories = DB::table('categories')->get()->keyBy('slug');

        // Real-life case scenarios based on reports
        $caseScenarios = [
            [
                'report_id' => $reports->where('report_code', 'RPT-2024-001')->first()?->id,
                'category_slug' => 'corruption',
                'case_number' => 'CASE-2024-001',
                'title_id' => 'Kasus Korupsi Proyek Pembangunan Jalan Jakarta Selatan',
                'title_en' => 'South Jakarta Road Construction Corruption Case',
                'summary_id' => 'Kasus dugaan korupsi dalam proyek pembangunan jalan dengan indikasi mark-up harga material dan penyimpangan anggaran sebesar Rp 5 miliar.',
                'summary_en' => 'Alleged corruption case in road construction project with indications of material price mark-ups and budget deviation of Rp 5 billion.',
                'description_id' => 'Kasus ini bermula dari laporan masyarakat mengenai dugaan korupsi dalam proyek pembangunan jalan di Jakarta Selatan. Investigasi mengungkap adanya mark-up harga bahan material hingga 30% dari harga pasar. Total penyimpangan anggaran yang ditemukan mencapai Rp 5 miliar. Kasus ini sedang dalam tahap investigasi untuk mengumpulkan bukti lebih lanjut.',
                'description_en' => 'This case originated from public reports of alleged corruption in road construction project in South Jakarta. Investigation revealed material price mark-ups up to 30% above market prices. Total budget deviation found reached Rp 5 billion. This case is currently in investigation phase to gather further evidence.',
                'event_date' => now()->subMonths(3)->format('Y-m-d'),
                'lat' => -6.2297,
                'lng' => 106.7985,
                'status_key' => 'investigation',
                'is_public' => false,
                'timeline_entries' => [
                    ['days_offset' => 0, 'notes' => 'Action: Convert to Case - Kasus dibuat dari laporan RPT-2024-001'],
                    ['days_offset' => 15, 'notes' => 'Action: Status changed - Investigasi awal dimulai, tim investigasi dibentuk'],
                    ['days_offset' => 30, 'notes' => 'Bukti awal dikumpulkan: dokumen tender, laporan keuangan proyek'],
                ],
                'actors' => [
                    ['type' => 'government', 'name' => 'Kepala Dinas PUPR Jakarta Selatan', 'description' => 'Terduga pelaku utama'],
                    ['type' => 'corporate', 'name' => 'PT Jaya Konstruksi', 'description' => 'Kontraktor proyek'],
                ],
            ],
            [
                'report_id' => $reports->where('report_code', 'RPT-2024-002')->first()?->id,
                'category_slug' => 'environment',
                'case_number' => 'CASE-2024-002',
                'title_id' => 'Kasus Pencemaran Sungai Ciliwung oleh Pabrik Tekstil',
                'title_en' => 'Ciliwung River Pollution Case by Textile Factory',
                'summary_id' => 'Kasus pencemaran sungai Ciliwung oleh pabrik tekstil yang membuang limbah berbahaya tanpa pengolahan yang memadai.',
                'summary_en' => 'Ciliwung River pollution case by textile factory disposing hazardous waste without adequate treatment.',
                'description_id' => 'Pabrik tekstil di Jakarta Selatan diduga membuang limbah berbahaya langsung ke sungai Ciliwung tanpa pengolahan. Air sungai berubah menjadi hitam dan berbau menyengat. Dampaknya sangat mempengaruhi kualitas air untuk kebutuhan sehari-hari warga. Sampel air telah diambil untuk uji laboratorium.',
                'description_en' => 'Textile factory in South Jakarta allegedly disposed hazardous waste directly into Ciliwung River without treatment. River water turned black and emitted strong odor. Impact severely affects water quality for daily needs of residents. Water samples have been taken for laboratory testing.',
                'event_date' => now()->subMonths(2)->format('Y-m-d'),
                'lat' => -6.2088,
                'lng' => 106.8456,
                'status_key' => 'prosecution',
                'is_public' => true,
                'published_at' => now()->subMonths(1),
                'timeline_entries' => [
                    ['days_offset' => 0, 'notes' => 'Action: Convert to Case - Kasus dibuat dari laporan RPT-2024-002'],
                    ['days_offset' => 10, 'notes' => 'Action: Complete Investigation - Investigasi selesai, bukti lengkap'],
                    ['days_offset' => 12, 'notes' => 'Action: Start Prosecution - Kasus diajukan ke kejaksaan'],
                    ['days_offset' => 20, 'notes' => 'Hasil uji laboratorium menunjukkan pencemaran berat'],
                ],
                'actors' => [
                    ['type' => 'corporate', 'name' => 'PT Tekstil Maju', 'description' => 'Pabrik yang diduga mencemari sungai'],
                    ['type' => 'government', 'name' => 'Dinas Lingkungan Hidup DKI Jakarta', 'description' => 'Pengawas lingkungan'],
                ],
            ],
            [
                'report_id' => $reports->where('report_code', 'RPT-2024-004')->first()?->id,
                'category_slug' => 'fraud',
                'case_number' => 'CASE-2024-003',
                'title_id' => 'Kasus Penipuan Investasi Emas PT Berkah Emas',
                'title_en' => 'PT Berkah Emas Gold Investment Fraud Case',
                'summary_id' => 'Kasus penipuan investasi bodong dengan modus investasi emas yang merugikan puluhan investor dengan total kerugian ratusan juta rupiah.',
                'summary_en' => 'Gold investment fraud case that harmed dozens of investors with total losses of hundreds of millions rupiah.',
                'description_id' => 'PT Berkah Emas menjalankan bisnis investasi emas ilegal dengan menjanjikan return 5% per bulan. Setelah beroperasi selama setahun, perusahaan tidak pernah memberikan keuntungan kepada investor. Total korban mencapai 50 orang dengan total kerugian lebih dari Rp 500 juta. Direktur perusahaan telah ditangkap.',
                'description_en' => 'PT Berkah Emas operated illegal gold investment business promising 5% monthly returns. After operating for one year, the company never provided profits to investors. Total victims reached 50 people with total losses over Rp 500 million. Company director has been arrested.',
                'event_date' => now()->subMonths(4)->format('Y-m-d'),
                'lat' => -6.2088,
                'lng' => 106.8456,
                'status_key' => 'trial',
                'is_public' => true,
                'published_at' => now()->subMonths(3),
                'timeline_entries' => [
                    ['days_offset' => 0, 'notes' => 'Action: Convert to Case - Kasus dibuat dari laporan RPT-2024-004'],
                    ['days_offset' => 20, 'notes' => 'Action: Complete Investigation - Investigasi selesai, 50 korban teridentifikasi'],
                    ['days_offset' => 25, 'notes' => 'Action: Start Prosecution - Kasus diajukan ke kejaksaan'],
                    ['days_offset' => 30, 'notes' => 'Action: Start Trial - Sidang dimulai, terdakwa ditangkap'],
                    ['days_offset' => 45, 'notes' => 'Sidang pertama: saksi korban memberikan kesaksian'],
                    ['days_offset' => 60, 'notes' => 'Sidang kedua: bukti dokumen diperiksa'],
                ],
                'actors' => [
                    ['type' => 'corporate', 'name' => 'PT Berkah Emas', 'description' => 'Perusahaan penipu'],
                    ['type' => 'citizen', 'name' => 'Dewi Sartika', 'description' => 'Korban penipuan, pelapor'],
                ],
            ],
            [
                'report_id' => $reports->where('report_code', 'RPT-2024-006')->first()?->id,
                'category_slug' => 'environment',
                'case_number' => 'CASE-2024-004',
                'title_id' => 'Kasus Pembakaran Hutan Ilegal di Pinggiran Jakarta',
                'title_en' => 'Illegal Forest Burning Case in Jakarta Outskirts',
                'summary_id' => 'Kasus pembakaran hutan ilegal untuk pembukaan lahan yang menyebabkan polusi asap dan gangguan kesehatan masyarakat.',
                'summary_en' => 'Illegal forest burning case for land clearing causing smoke pollution and public health problems.',
                'description_id' => 'Pembakaran hutan ilegal dilakukan di kawasan pinggiran Jakarta untuk membuka lahan baru. Area yang dibakar mencapai 10 hektar. Asap tebal yang dihasilkan mengganggu aktivitas warga dan menyebabkan gangguan pernafasan. Pelaku telah diidentifikasi dan akan diproses secara hukum.',
                'description_en' => 'Illegal forest burning conducted in Jakarta outskirts to clear new land. Burned area reached 10 hectares. Thick smoke generated disrupted resident activities and caused respiratory problems. Perpetrators have been identified and will be legally processed.',
                'event_date' => now()->subMonths(1)->format('Y-m-d'),
                'lat' => -6.2920,
                'lng' => 106.8014,
                'status_key' => 'executed',
                'is_public' => true,
                'published_at' => now()->subWeeks(3),
                'timeline_entries' => [
                    ['days_offset' => 0, 'notes' => 'Action: Convert to Case - Kasus dibuat dari laporan RPT-2024-006'],
                    ['days_offset' => 7, 'notes' => 'Action: Complete Investigation - Investigasi selesai, pelaku teridentifikasi'],
                    ['days_offset' => 10, 'notes' => 'Action: Start Prosecution - Kasus diajukan'],
                    ['days_offset' => 15, 'notes' => 'Action: Start Trial - Sidang dimulai'],
                    ['days_offset' => 20, 'notes' => 'Action: Execute Verdict - Putusan: 2 tahun penjara + denda'],
                    ['days_offset' => 22, 'notes' => 'Vonis dijalankan, pelaku dimasukkan ke penjara'],
                ],
                'actors' => [
                    ['type' => 'citizen', 'name' => 'Petani Lokal', 'description' => 'Pelaku pembakaran'],
                ],
            ],
        ];

        // Add procurement corruption example: Laporan dugaan korupsi pengadaan alat kesehatan
        $caseScenarios[] = [
            'report_id' => $reports->where('report_code', 'RPT-2024-010')->first()?->id,
            'category_slug' => 'corruption',
            'case_number' => 'CASE-2024-010',
            'title_id' => 'Laporan dugaan korupsi pengadaan alat kesehatan',
            'title_en' => 'Alleged Corruption in Medical Equipment Procurement',
            'summary_id' => 'Laporan dugaan korupsi pada pengadaan alat kesehatan dengan indikasi mark-up harga dan kolusi.',
            'summary_en' => 'Alleged corruption in procurement of medical equipment with indications of price mark-ups and collusion.',
            'description_id' => 'Laporan ini terkait dugaan mark-up harga pada proyek pengadaan alat kesehatan provinsi dengan indikasi collusion antara penyedia dan panitia pengadaan. Diperlukan pengumpulan kontrak, faktur, dan bukti aliran dana.',
            'description_en' => 'This report concerns alleged price mark-ups in a provincial procurement of medical equipment with suspected collusion between supplier and procurement committee. Requires collecting contracts, invoices, and financial flow evidence.',
            'event_date' => now()->subWeeks(6)->format('Y-m-d'),
            'lat' => -6.2000,
            'lng' => 106.8166,
            'status_key' => 'investigation',
            'is_public' => false,
            'timeline_entries' => [
                ['days_offset' => 0, 'notes' => 'Action: Convert to Case - Kasus dibuat dari laporan terkait pengadaan alat kesehatan'],
            ],
            'actors' => [],
        ];

        foreach ($caseScenarios as $caseData) {
            $categoryId = $categories[$caseData['category_slug']]->id ?? 1;
            $statusId = DB::table('statuses')->where('key', $caseData['status_key'])->value('id') ?? $statusInvestigation;

            $eventDate = \Carbon\Carbon::parse($caseData['event_date']);
            $createdAt = $eventDate->copy();

            // Create or update case (idempotent)
            $existingCase = DB::table('cases')->where('case_number', $caseData['case_number'])->first();
            if ($existingCase) {
                $caseId = $existingCase->id;
                DB::table('cases')->where('id', $caseId)->update([
                    'report_id' => $caseData['report_id'],
                    'category_id' => $categoryId,
                    'status_id' => $statusId,
                    'event_date' => $caseData['event_date'],
                    'latitude' => $caseData['lat'],
                    'longitude' => $caseData['lng'],
                    'is_public' => $caseData['is_public'] ?? false,
                    'published_at' => $caseData['published_at'] ?? null,
                    'updated_at' => now(),
                ]);
            } else {
                $caseId = DB::table('cases')->insertGetId([
                    'case_number' => $caseData['case_number'],
                    'report_id' => $caseData['report_id'],
                    'category_id' => $categoryId,
                    'status_id' => $statusId,
                    'event_date' => $caseData['event_date'],
                    'latitude' => $caseData['lat'],
                    'longitude' => $caseData['lng'],
                    'is_public' => $caseData['is_public'] ?? false,
                    'published_at' => $caseData['published_at'] ?? null,
                    'is_tasks_completed' => false,
                    'tasks_completed_at' => null,
                    'created_by' => $firstUser,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);
            }

            // Insert or update translations (idempotent)
            DB::table('case_translations')->updateOrInsert(
                ['case_id' => $caseId, 'locale' => 'id'],
                [
                    'title' => $caseData['title_id'],
                    'summary' => $caseData['summary_id'],
                    'description' => $caseData['description_id'],
                    'updated_at' => now(),
                ]
            );

            DB::table('case_translations')->updateOrInsert(
                ['case_id' => $caseId, 'locale' => 'en'],
                [
                    'title' => $caseData['title_en'],
                    'summary' => $caseData['summary_en'],
                    'description' => $caseData['description_en'] ?? $caseData['summary_en'],
                    'updated_at' => now(),
                ]
            );

            // Insert or update timeline entries (idempotent)
            foreach ($caseData['timeline_entries'] as $timeline) {
                $timelineDate = $eventDate->copy()->addDays($timeline['days_offset']);

                DB::table('case_timelines')->updateOrInsert(
                    ['case_id' => $caseId, 'notes' => $timeline['notes'], 'created_at' => $timelineDate],
                    ['actor_id' => $firstUser, 'updated_at' => $timelineDate]
                );
            }

            // Insert or update actors (idempotent)
            foreach ($caseData['actors'] as $actor) {
                DB::table('case_actors')->updateOrInsert(
                    ['case_id' => $caseId, 'type' => $actor['type'], 'name' => $actor['name']],
                    ['description' => $actor['description'], 'metadata' => json_encode([]), 'updated_at' => now()]
                );
            }

            // Create geometry for public cases
            if ($caseData['is_public'] && ! empty($caseData['lat']) && ! empty($caseData['lng'])) {
                $wkt = sprintf('POINT(%.15f %.15f)', (float) $caseData['lat'], (float) $caseData['lng']);

                DB::statement(
                    'INSERT INTO case_geometries (case_id, geom, title, category, status, is_public, created_at, updated_at) VALUES (?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, ?)',
                    [
                        $caseId,
                        $wkt,
                        $caseData['title_id'],
                        $caseData['category_slug'],
                        'published',
                        $caseData['is_public'] ? 1 : 0,
                        $createdAt,
                        now(),
                    ]
                );
            }

            // Auto-generate case tasks from process/task templates (idempotent)
            $generatedCount = CaseTaskGenerator::generate($caseId, $categoryId);
            if ($generatedCount > 0) {
                DB::table('case_timelines')->insert([
                    'case_id' => $caseId,
                    'actor_id' => $firstUser,
                    'notes' => "Auto-generated {$generatedCount} tasks from templates for category {$caseData['category_slug']}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Update report status to 'converted' for reports that were converted to cases
        foreach ($caseScenarios as $caseData) {
            if ($caseData['report_id']) {
                $convertedStatusId = DB::table('statuses')->where('key', 'converted')->value('id');
                if ($convertedStatusId) {
                    DB::table('reports')
                        ->where('id', $caseData['report_id'])
                        ->update(['status_id' => $convertedStatusId]);
                }
            }
        }
    }
}
