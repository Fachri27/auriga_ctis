<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseModel;
use App\Models\CaseTranslation;
use App\Models\Category;
use Illuminate\Support\Str;

class CaseSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::pluck('id')->toArray();
        
        if (empty($categories)) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        $statusIds = \DB::table('statuses')->pluck('id')->toArray();
        
        if (empty($statusIds)) {
            $this->command->warn('No statuses found. Please create statuses first.');
            return;
        }

        $cases = [
            [
                'title_id' => 'Kasus Korupsi Pengadaan Alat Kesehatan',
                'title_en' => 'Medical Equipment Procurement Corruption Case',
                'desc_id' => '<p>Kasus dugaan korupsi dalam pengadaan alat kesehatan senilai Rp 15 miliar di Rumah Sakit Umum Daerah. Diduga terjadi mark-up harga hingga 300% dari harga pasar.</p><p>Penyidik telah menetapkan 3 tersangka yang terdiri dari pejabat pengadaan, direktur rumah sakit, dan pemilik perusahaan penyedia alat kesehatan.</p>',
                'desc_en' => '<p>Alleged corruption case in medical equipment procurement worth Rp 15 billion at Regional General Hospital. Suspected price mark-up up to 300% from market price.</p><p>Investigators have named 3 suspects consisting of procurement officials, hospital directors, and medical equipment supplier company owners.</p>',
                'province' => 'Jawa Barat',
                'district' => 'Bandung',
                'lat' => -6.9175,
                'lng' => 107.6191,
            ],
            [
                'title_id' => 'Suap Perizinan Tambang Ilegal',
                'title_en' => 'Illegal Mining Permit Bribery',
                'desc_id' => '<p>Kasus suap dalam pengurusan izin tambang ilegal di kawasan hutan lindung. Total suap yang diterima mencapai Rp 8 miliar.</p><p>Kasus ini melibatkan oknum pejabat daerah dan pengusaha tambang. Operasi tambang ilegal telah merusak 500 hektare hutan lindung.</p>',
                'desc_en' => '<p>Bribery case in illegal mining permit processing in protected forest areas. Total bribes received reached Rp 8 billion.</p><p>This case involves regional officials and mining entrepreneurs. Illegal mining operations have damaged 500 hectares of protected forest.</p>',
                'province' => 'Kalimantan Timur',
                'district' => 'Samarinda',
                'lat' => -0.5022,
                'lng' => 117.1536,
            ],
            [
                'title_id' => 'Korupsi Dana Bantuan Sosial COVID-19',
                'title_en' => 'COVID-19 Social Assistance Fund Corruption',
                'desc_id' => '<p>Dugaan korupsi dana bantuan sosial COVID-19 senilai Rp 12 miliar. Dana yang seharusnya disalurkan kepada 50.000 keluarga miskin tidak sampai ke penerima.</p><p>Tersangka diduga melakukan manipulasi data penerima bantuan dan mengalihkan dana untuk kepentingan pribadi.</p>',
                'desc_en' => '<p>Alleged corruption of COVID-19 social assistance funds worth Rp 12 billion. Funds that should have been distributed to 50,000 poor families did not reach recipients.</p><p>Suspects allegedly manipulated recipient data and diverted funds for personal gain.</p>',
                'province' => 'DKI Jakarta',
                'district' => 'Jakarta Selatan',
                'lat' => -6.2615,
                'lng' => 106.8106,
            ],
            [
                'title_id' => 'Penggelapan Pajak Perusahaan Multinasional',
                'title_en' => 'Multinational Company Tax Evasion',
                'desc_id' => '<p>Kasus penggelapan pajak oleh perusahaan multinasional dengan kerugian negara mencapai Rp 500 miliar. Perusahaan diduga melakukan transfer pricing dan manipulasi laporan keuangan.</p><p>Direktorat Jenderal Pajak telah melakukan pemeriksaan mendalam dan menemukan bukti kuat penggelapan pajak selama 5 tahun terakhir.</p>',
                'desc_en' => '<p>Tax evasion case by multinational company with state losses reaching Rp 500 billion. Company allegedly conducted transfer pricing and financial report manipulation.</p><p>Directorate General of Taxes has conducted in-depth examination and found strong evidence of tax evasion over the past 5 years.</p>',
                'province' => 'DKI Jakarta',
                'district' => 'Jakarta Pusat',
                'lat' => -6.1751,
                'lng' => 106.8650,
            ],
            [
                'title_id' => 'Korupsi Proyek Infrastruktur Jalan Tol',
                'title_en' => 'Toll Road Infrastructure Project Corruption',
                'desc_id' => '<p>Dugaan korupsi dalam proyek pembangunan jalan tol sepanjang 50 km dengan nilai proyek Rp 2 triliun. Diduga terjadi mark-up anggaran dan penggunaan material di bawah standar.</p><p>Jaksa telah menyita aset tersangka senilai Rp 100 miliar termasuk properti dan kendaraan mewah.</p>',
                'desc_en' => '<p>Alleged corruption in 50 km toll road construction project worth Rp 2 trillion. Suspected budget mark-up and use of substandard materials.</p><p>Prosecutors have seized suspects assets worth Rp 100 billion including properties and luxury vehicles.</p>',
                'province' => 'Jawa Tengah',
                'district' => 'Semarang',
                'lat' => -6.9932,
                'lng' => 110.4203,
            ],
            [
                'title_id' => 'Suap Hakim dalam Kasus Perdata',
                'title_en' => 'Judge Bribery in Civil Case',
                'desc_id' => '<p>Kasus suap kepada hakim pengadilan negeri dalam penanganan perkara perdata sengketa tanah. Suap diberikan sebesar Rp 2 miliar untuk memenangkan perkara.</p><p>Komisi Yudisial telah merekomendasikan pemberhentian hakim yang bersangkutan dan kasus dilimpahkan ke Mahkamah Agung.</p>',
                'desc_en' => '<p>Bribery case to district court judge in handling civil land dispute case. Bribe given amounting to Rp 2 billion to win the case.</p><p>Judicial Commission has recommended dismissal of the judge concerned and case referred to Supreme Court.</p>',
                'province' => 'Sumatera Utara',
                'district' => 'Medan',
                'lat' => 3.5952,
                'lng' => 98.6722,
            ],
            [
                'title_id' => 'Korupsi Anggaran Pendidikan Daerah',
                'title_en' => 'Regional Education Budget Corruption',
                'desc_id' => '<p>Dugaan korupsi dana pendidikan daerah senilai Rp 20 miliar. Dana yang seharusnya untuk renovasi 100 sekolah dan pengadaan buku pelajaran tidak direalisasikan dengan baik.</p><p>Audit BPK menemukan banyak sekolah yang tidak direnovasi dan buku pelajaran tidak pernah diterima siswa.</p>',
                'desc_en' => '<p>Alleged corruption of regional education funds worth Rp 20 billion. Funds that should have been for renovation of 100 schools and procurement of textbooks were not properly realized.</p><p>BPK audit found many schools not renovated and textbooks never received by students.</p>',
                'province' => 'Sulawesi Selatan',
                'district' => 'Makassar',
                'lat' => -5.1477,
                'lng' => 119.4327,
            ],
            [
                'title_id' => 'Pencucian Uang Hasil Korupsi',
                'title_en' => 'Money Laundering from Corruption Proceeds',
                'desc_id' => '<p>Kasus pencucian uang hasil korupsi senilai Rp 50 miliar melalui berbagai perusahaan cangkang dan investasi properti. Tersangka adalah mantan pejabat tinggi pemerintahan.</p><p>PPATK telah melacak aliran dana ke berbagai negara dan bekerja sama dengan otoritas internasional untuk pemblokiran aset.</p>',
                'desc_en' => '<p>Money laundering case from corruption proceeds worth Rp 50 billion through various shell companies and property investments. Suspect is former high-ranking government official.</p><p>PPATK has traced fund flows to various countries and cooperated with international authorities for asset freezing.</p>',
                'province' => 'Bali',
                'district' => 'Denpasar',
                'lat' => -8.6705,
                'lng' => 115.2126,
            ],
            [
                'title_id' => 'Gratifikasi Pejabat Imigrasi',
                'title_en' => 'Immigration Official Gratuity',
                'desc_id' => '<p>Kasus gratifikasi yang diterima pejabat imigrasi dari sindikat penyelundupan manusia. Total gratifikasi mencapai Rp 5 miliar selama 3 tahun.</p><p>Tersangka memfasilitasi penerbitan dokumen keimigrasian ilegal untuk warga negara asing yang akan bekerja secara ilegal di Indonesia.</p>',
                'desc_en' => '<p>Gratuity case received by immigration officials from human smuggling syndicate. Total gratuity reached Rp 5 billion over 3 years.</p><p>Suspects facilitated issuance of illegal immigration documents for foreign nationals who would work illegally in Indonesia.</p>',
                'province' => 'Kepulauan Riau',
                'district' => 'Batam',
                'lat' => 1.0456,
                'lng' => 104.0305,
            ],
            [
                'title_id' => 'Korupsi Subsidi Pupuk Pertanian',
                'title_en' => 'Agricultural Fertilizer Subsidy Corruption',
                'desc_id' => '<p>Dugaan korupsi dalam penyaluran subsidi pupuk pertanian senilai Rp 30 miliar. Pupuk bersubsidi tidak sampai ke petani dan dijual ke pasar bebas dengan harga tinggi.</p><p>Kasus ini merugikan 100.000 petani yang tidak mendapatkan pupuk bersubsidi dan harus membeli dengan harga mahal.</p>',
                'desc_en' => '<p>Alleged corruption in distribution of agricultural fertilizer subsidies worth Rp 30 billion. Subsidized fertilizer did not reach farmers and was sold to free market at high prices.</p><p>This case harmed 100,000 farmers who did not receive subsidized fertilizer and had to buy at expensive prices.</p>',
                'province' => 'Jawa Timur',
                'district' => 'Surabaya',
                'lat' => -7.2575,
                'lng' => 112.7521,
            ],
        ];

        foreach ($cases as $index => $caseData) {
            // Random category (1-3 categories per case)
            $numCategories = rand(1, min(3, count($categories)));
            $selectedCategories = array_rand(array_flip($categories), $numCategories);
            if (!is_array($selectedCategories)) {
                $selectedCategories = [$selectedCategories];
            }

            $case = CaseModel::create([
                'case_number' => 'CASE-' . strtoupper(Str::random(5)),
                'category_ids' => $selectedCategories,
                'status_id' => $statusIds[array_rand($statusIds)],
                'event_date' => now()->subDays(rand(30, 365))->format('Y-m-d'),
                'verified_by' => 1, // Assuming admin user ID is 1
                'latitude' => $caseData['lat'],
                'longitude' => $caseData['lng'],
                'is_public' => true,
                'published_at' => now()->subDays(rand(1, 30)),
                'korban' => rand(1, 100),
                'jumlah_korban' => rand(1, 100),
                'konflik' => ['Korupsi', 'Suap', 'Gratifikasi'][rand(0, 2)],
                'pelapor' => 'Masyarakat',
                'terlapor' => 'Pejabat Publik',
                'sumber' => ['KPK', 'Kejaksaan', 'Kepolisian'][rand(0, 2)],
                'instansi' => ['Pemerintah Daerah', 'BUMN', 'Kementerian'][rand(0, 2)],
                'status_narasi' => 'Dalam proses penyidikan',
            ]);

            // Create translations
            foreach (['id', 'en'] as $locale) {
                CaseTranslation::create([
                    'case_id' => $case->id,
                    'locale' => $locale,
                    'title' => $locale === 'id' ? $caseData['title_id'] : $caseData['title_en'],
                    'summary' => Str::limit(strip_tags($locale === 'id' ? $caseData['desc_id'] : $caseData['desc_en']), 200),
                    'description' => $locale === 'id' ? $caseData['desc_id'] : $caseData['desc_en'],
                    'perkembangan' => json_encode([
                        [
                            'title' => 'Laporan Diterima',
                            'notes' => 'Laporan kasus diterima dan diverifikasi oleh tim penyidik.',
                            'created_at' => now()->subDays(rand(20, 60))->toDateTimeString(),
                        ],
                        [
                            'title' => 'Penetapan Tersangka',
                            'notes' => 'Penyidik telah menetapkan tersangka berdasarkan bukti yang terkumpul.',
                            'created_at' => now()->subDays(rand(5, 19))->toDateTimeString(),
                        ],
                    ]),
                    'pembelajaran' => '<p>Kasus ini menunjukkan pentingnya transparansi dalam pengelolaan anggaran publik.</p><p>Pengawasan yang ketat dan partisipasi masyarakat sangat diperlukan untuk mencegah korupsi.</p>',
                ]);
            }

            $this->command->info("Created case: {$caseData['title_id']}");
        }

        $this->command->info('Successfully created 10 cases with translations!');
    }
}
