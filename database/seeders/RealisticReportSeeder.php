<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RealisticReportSeeder
 *
 * Seeder untuk laporan masyarakat realistis berdasarkan kehidupan nyata
 * Berisi laporan berbagai jenis kasus dengan data yang detail dan realistis
 */
class RealisticReportSeeder extends Seeder
{
    public function run(): void
    {
        // Get status IDs
        $statusOpen = DB::table('statuses')->where('key', 'open')->value('id');
        $statusVerified = DB::table('statuses')->where('key', 'verified')->value('id');
        $statusConverted = DB::table('statuses')->where('key', 'converted')->value('id');
        $statusRejected = DB::table('statuses')->where('key', 'rejected')->value('id');

        // Get category IDs
        $categoryCorruption = DB::table('categories')->where('slug', 'corruption')->value('id');
        $categoryEnvironment = DB::table('categories')->where('slug', 'environment')->value('id');
        $categoryViolence = DB::table('categories')->where('slug', 'violence')->value('id');
        $categoryFraud = DB::table('categories')->where('slug', 'fraud')->value('id');
        $categoryLabor = DB::table('categories')->where('slug', 'labor')->value('id');

        // Get province/district IDs (Jakarta)
        $provinceJakarta = DB::table('provinces')->where('code', 'ID-JK')->value('id');
        $districtJakartaSelatan = DB::table('districts')->where('code', 'ID-JK-04')->value('id');
        $districtJakartaPusat = DB::table('districts')->where('code', 'ID-JK-01')->value('id');

        // Get user IDs (if any)
        $firstUser = DB::table('users')->value('id');

        // Real-life report scenarios
        $reports = [
            [
                'report_code' => 'RPT-2024-001',
                'category_id' => $categoryCorruption,
                'status_key' => 'verified',
                'nama_lengkap' => 'Budi Santoso',
                'nik' => '3174031501890001',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1989-01-15',
                'alamat' => 'Jl. Kebayoran Baru No. 45, Jakarta Selatan',
                'no_hp' => '081234567890',
                'email' => 'budi.santoso@email.com',
                'pekerjaan' => 'Karyawan Swasta',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Menikah',
                'lat' => -6.2297,
                'lng' => 106.7985,
                'description_id' => 'Saya melaporkan dugaan korupsi dalam proyek pembangunan jalan di Jakarta Selatan. Terdapat indikasi mark-up harga bahan material dan penyimpangan anggaran sebesar Rp 5 miliar. Saya memiliki beberapa bukti dokumentasi berupa foto dan dokumen tender.',
                'description_en' => 'I report alleged corruption in road construction project in South Jakarta. There are indications of material price mark-ups and budget deviation of Rp 5 billion. I have some documentary evidence in the form of photos and tender documents.',
                'evidence' => ['photo1.jpg', 'tender_doc.pdf', 'budget_comparison.xlsx'],
                'created_at' => now()->subMonths(3),
            ],
            [
                'report_code' => 'RPT-2024-002',
                'category_id' => $categoryEnvironment,
                'status_key' => 'converted',
                'nama_lengkap' => 'Siti Nurhaliza',
                'nik' => '3275032203920002',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '1992-03-22',
                'alamat' => 'Jl. Rasuna Said Kav. C-22, Jakarta Selatan',
                'no_hp' => '082345678901',
                'email' => 'siti.nurhaliza@email.com',
                'pekerjaan' => 'Wiraswasta',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Belum Menikah',
                'lat' => -6.2088,
                'lng' => 106.8456,
                'description_id' => 'Melaporkan pencemaran sungai Ciliwung oleh pabrik tekstil di daerah Jakarta Selatan. Air sungai berubah warna menjadi hitam dan berbau menyengat. Telah mempengaruhi kualitas air untuk kebutuhan sehari-hari warga sekitar. Ada foto dan video dokumentasi.',
                'description_en' => 'Reporting Ciliwung River pollution by textile factory in South Jakarta area. River water has turned black and emits strong odor. Has affected water quality for daily needs of surrounding residents. Have photos and video documentation.',
                'evidence' => ['river_pollution.jpg', 'water_sample.mp4', 'factory_location.pdf'],
                'created_at' => now()->subMonths(2),
            ],
            [
                'report_code' => 'RPT-2024-003',
                'category_id' => $categoryViolence,
                'status_key' => 'open',
                'nama_lengkap' => 'Ahmad Hidayat',
                'nik' => '3175011805910003',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1991-05-18',
                'alamat' => 'Jl. Gatot Subroto No. 88, Jakarta Selatan',
                'no_hp' => '083456789012',
                'email' => 'ahmad.hidayat@email.com',
                'pekerjaan' => 'Pegawai Negeri Sipil',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Menikah',
                'lat' => -6.2297,
                'lng' => 106.7985,
                'description_id' => 'Laporan kekerasan terhadap pekerja di proyek pembangunan apartemen. Terjadi pemukulan dan intimidasi terhadap pekerja yang menuntut hak mereka. Beberapa pekerja terluka dan memerlukan perawatan medis.',
                'description_en' => 'Report of violence against workers in apartment construction project. There was beating and intimidation of workers demanding their rights. Several workers were injured and required medical treatment.',
                'evidence' => ['incident_photo.jpg', 'medical_report.pdf', 'witness_statement.pdf'],
                'created_at' => now()->subWeeks(2),
            ],
            [
                'report_code' => 'RPT-2024-004',
                'category_id' => $categoryFraud,
                'status_key' => 'verified',
                'nama_lengkap' => 'Dewi Sartika',
                'nik' => '3175022508930004',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '1993-08-25',
                'alamat' => 'Jl. HR Rasuna Said No. 1, Jakarta Selatan',
                'no_hp' => '084567890123',
                'email' => 'dewi.sartika@email.com',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Menikah',
                'lat' => -6.2088,
                'lng' => 106.8456,
                'description_id' => 'Melaporkan penipuan investasi bodong dengan modus investasi emas. Perusahaan PT Berkah Emas menjanjikan return 5% per bulan namun setelah setahun tidak pernah memberikan keuntungan. Total kerugian mencapai Rp 50 juta.',
                'description_en' => 'Reporting investment fraud with gold investment scheme. PT Berkah Emas promised 5% monthly return but after one year never provided profits. Total loss reached Rp 50 million.',
                'evidence' => ['investment_contract.pdf', 'bank_transfer_receipts.pdf', 'company_profile.pdf'],
                'created_at' => now()->subMonths(4),
            ],
            [
                'report_code' => 'RPT-2024-005',
                'category_id' => $categoryLabor,
                'status_key' => 'open',
                'nama_lengkap' => 'Joko Widodo',
                'nik' => '3175041204850005',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1985-04-12',
                'alamat' => 'Jl. Jenderal Sudirman No. 52, Jakarta Pusat',
                'no_hp' => '085678901234',
                'email' => 'joko.widodo@email.com',
                'pekerjaan' => 'Buruh Pabrik',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Menikah',
                'lat' => -6.2088,
                'lng' => 106.8456,
                'description_id' => 'Laporan pelanggaran hak pekerja di pabrik garmen. Upah tidak dibayar selama 3 bulan, jam kerja berlebihan tanpa uang lembur, dan tidak ada asuransi kesehatan. Total pekerja yang terkena dampak 50 orang.',
                'description_en' => 'Report of workers rights violations at garment factory. Wages unpaid for 3 months, excessive working hours without overtime pay, and no health insurance. Total affected workers 50 people.',
                'evidence' => ['payroll_records.pdf', 'work_schedule.pdf', 'worker_list.pdf'],
                'created_at' => now()->subWeeks(3),
            ],
            [
                'report_code' => 'RPT-2024-006',
                'category_id' => $categoryEnvironment,
                'status_key' => 'verified',
                'nama_lengkap' => 'Maya Sari',
                'nik' => '3175051506900006',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '1990-06-15',
                'alamat' => 'Jl. TB Simatupang No. 32, Jakarta Selatan',
                'no_hp' => '086789012345',
                'email' => 'maya.sari@email.com',
                'pekerjaan' => 'Guru',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Belum Menikah',
                'lat' => -6.2920,
                'lng' => 106.8014,
                'description_id' => 'Laporan pembakaran hutan ilegal di kawasan pinggiran Jakarta untuk pembukaan lahan. Asap tebal mengganggu aktivitas warga dan menyebabkan gangguan pernafasan. Area yang dibakar sekitar 10 hektar.',
                'description_en' => 'Report of illegal forest burning in Jakarta outskirts for land clearing. Thick smoke disrupts resident activities and causes respiratory problems. Burned area approximately 10 hectares.',
                'evidence' => ['burning_area.jpg', 'smoke_video.mp4', 'location_map.pdf'],
                'created_at' => now()->subMonths(1),
            ],
            [
                'report_code' => 'RPT-2024-007',
                'category_id' => $categoryCorruption,
                'status_key' => 'rejected',
                'nama_lengkap' => 'Andi Pratama',
                'nik' => '3175062003870007',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1987-03-20',
                'alamat' => 'Jl. Kemang Raya No. 12, Jakarta Selatan',
                'no_hp' => '087890123456',
                'email' => 'andi.pratama@email.com',
                'pekerjaan' => 'Pengusaha',
                'kewarganegaraan' => 'Indonesia',
                'status_perkawinan' => 'Menikah',
                'lat' => -6.2615,
                'lng' => 106.8106,
                'description_id' => 'Laporan dugaan korupsi dalam pengadaan barang dan jasa di instansi pemerintah. Namun setelah verifikasi, laporan ini ditolak karena bukti tidak cukup dan tidak dapat diverifikasi kebenarannya.',
                'description_en' => 'Report of alleged corruption in government procurement. However after verification, this report was rejected due to insufficient evidence and cannot be verified.',
                'evidence' => [],
                'created_at' => now()->subMonths(5),
            ],
        ];

        foreach ($reports as $reportData) {
            $statusId = DB::table('statuses')->where('key', $reportData['status_key'])->value('id') ?? $statusOpen;

            $reportId = DB::table('reports')->insertGetId([
                'report_code' => $reportData['report_code'],
                'category_id' => $reportData['category_id'],
                'status_id' => $statusId,
                'nama_lengkap' => $reportData['nama_lengkap'],
                'nik' => $reportData['nik'],
                'jenis_kelamin' => $reportData['jenis_kelamin'],
                'tanggal_lahir' => $reportData['tanggal_lahir'],
                'alamat' => $reportData['alamat'],
                'no_hp' => $reportData['no_hp'],
                'email' => $reportData['email'],
                'pekerjaan' => $reportData['pekerjaan'],
                'kewarganegaraan' => $reportData['kewarganegaraan'],
                'status_perkawinan' => $reportData['status_perkawinan'],
                'lat' => $reportData['lat'],
                'lng' => $reportData['lng'],
                'evidence' => json_encode($reportData['evidence']),
                'created_by' => $firstUser,
                'created_at' => $reportData['created_at'],
                'updated_at' => $reportData['created_at'],
            ]);

            // Insert translations
            DB::table('report_translations')->insert([
                [
                    'report_id' => $reportId,
                    'locale' => 'id',
                    'description' => $reportData['description_id'],
                    'created_at' => $reportData['created_at'],
                    'updated_at' => $reportData['created_at'],
                ],
                [
                    'report_id' => $reportId,
                    'locale' => 'en',
                    'description' => $reportData['description_en'],
                    'created_at' => $reportData['created_at'],
                    'updated_at' => $reportData['created_at'],
                ],
            ]);
        }
    }
}

