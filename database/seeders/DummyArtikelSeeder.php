<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\CaseTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyArtikelSeeder extends Seeder
{
    public function run(): void
    {
        $case = CaseModel::create([
            'case_number' => 'CASE-' . strtoupper(Str::random(5)),
            'category_ids' => [2], // perusakan-lingkungan
            'status_id' => 11, // published
            'event_date' => '2024-01-01',
            'is_public' => true,
            'latitude' => -1.8825,
            'longitude' => 121.3542,
            'created_by' => 1,
            'verified_by' => 1,
            'pelapor' => 'WALHI Sulawesi Tengah',
            'terlapor' => 'Perusahaan Nikel di Morowali Utara',
        ]);

        CaseTranslation::create([
            'case_id' => $case->id,
            'locale' => 'id',
            'title' => 'WALHI Sulteng & Gugatan Pencemaran Industri Nikel Morowali Utara Putusan PN Poso No. 202/Pdt.Sus-LH/2024/PN Pso',
            'summary' => 'Gugatan perwakilan kelompok (class action) yang diajukan oleh WALHI Sulawesi Tengah terkait pencemaran lingkungan akibat aktivitas industri nikel di Kabupaten Morowali Utara.',
            'description' => '<p><strong>Kronologi Kasus</strong></p>
<p>Kasus ini bermula pada tahun 2022, ketika masyarakat di Kecamatan Petasia Timur, Kabupaten Morowali Utara, mulai mengalami berbagai gangguan kesehatan dan kerusakan lingkungan yang diduga kuat disebabkan oleh limbah industri nikel. WALHI Sulawesi Tengah menerima laporan dari warga yang mengeluhkan air sumur yang berubah warna menjadi kuning kecoklatan, ikan-ikan di sungai sekitar yang mati massal, serta debu nikel yang beterbangan di pemukiman warga.</p>
<p>Temuan awal WALHI Sulteng menunjukkan bahwa beberapa perusahaan nikel di wilayah Morowali Utara telah beroperasi tanpa Analisis Mengenai Dampak Lingkungan (AMDAL) yang memadai. Pengelolaan limbah cair dan padat tidak sesuai standar, dengan indikasi pembuangan limbah langsung ke badan sungai. WALHI kemudian melakukan serangkaian uji laboratorium terhadap sampel air dan tanah yang hasilnya menunjukkan kadar logam berat seperti nikel, kromium, dan mangan jauh melampaui baku mutu lingkungan yang ditetapkan pemerintah.</p>
<p>Setelah mediasi dengan pemerintah daerah dan perusahaan tidak membuahkan hasil, WALHI Sulteng bersama masyarakat terdampak mengajukan gugatan class action ke Pengadilan Negeri Poso pada awal tahun 2023. Gugatan tersebut mendaftar dengan nomor 202/Pdt.Sus-LH/2024/PN Pso, dengan tuntutan ganti rugi materil dan immateril, serta perintah penghentian operasi sampai dengan dipenuhinya ketentuan lingkungan yang berlaku.</p>
<p>Persidangan berlangsung selama kurang lebih satu tahun dengan menghadirkan puluhan saksi dan ahli dari berbagai disiplin ilmu, termasuk ahli lingkungan, ahli kesehatan masyarakat, dan ahli toksikologi. Perusahaan tergugat berupaya menolak gugatan dengan argumen bahwa kegiatan operasional mereka telah sesuai izin dan tidak menimbulkan pencemaran.</p>
<p>Pada akhirnya, Majelis Hakim Pengadilan Negeri Poso mengeluarkan putusan yang mengabulkan sebagian gugatan masyarakat. Putusan ini menjadi salah satu tonggak penting dalam penegakan hukum lingkungan di Indonesia, khususnya terkait pertanggungjawaban perusahaan nikel terhadap dampak lingkungan dan kesehatan masyarakat.</p>',
            'perkembangan' => '',
            'pembelajaran' => '',
            'dugaan_permasalahan' => '',
        ]);

        $this->command->info('Dummy case created: ID ' . $case->id);
    }
}
