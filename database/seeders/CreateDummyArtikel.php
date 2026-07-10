<?php

namespace Database\Seeders;

use App\Models\Artikel;
use Illuminate\Database\Seeder;

class CreateDummyArtikel extends Seeder
{
    public function run(): void
    {
        $artikel = Artikel::create([
            'slug' => 'walhi-sulteng-gugatan-nikel-morowali',
            'type' => 'artikel',
            'image' => null,
            'published_at' => now(),
            'status' => 'active',
            'link' => null,
            'user_id' => 1,
            'category_id' => 2,
        ]);

        $artikel->translation()->create([
            'locale' => 'id',
            'title' => 'WALHI Sulteng & Gugatan Pencemaran Industri Nikel Morowali Utara Putusan PN Poso No. 202/Pdt.Sus-LH/2024/PN Pso',
            'excerpt' => 'Gugatan perwakilan kelompok (class action) yang diajukan oleh WALHI Sulawesi Tengah terkait pencemaran lingkungan akibat aktivitas industri nikel di Kabupaten Morowali Utara, dengan putusan akhir di Pengadilan Negeri Poso.',
            'content' => '<p><strong>Kronologi Kasus</strong></p>
<p>Kasus ini bermula pada tahun 2022, ketika masyarakat di Kecamatan Petasia Timur, Kabupaten Morowali Utara, mulai mengalami berbagai gangguan kesehatan dan kerusakan lingkungan yang diduga kuat disebabkan oleh limbah industri nikel. WALHI Sulawesi Tengah menerima laporan dari warga yang mengeluhkan air sumur yang berubah warna menjadi kuning kecoklatan, ikan-ikan di sungai sekitar yang mati massal, serta debu nikel yang beterbangan di pemukiman warga.</p>
<p>Temuan awal WALHI Sulteng menunjukkan bahwa beberapa perusahaan nikel di wilayah Morowali Utara telah beroperasi tanpa Analisis Mengenai Dampak Lingkungan (AMDAL) yang memadai. Pengelolaan limbah cair dan padat tidak sesuai standar, dengan indikasi pembuangan limbah langsung ke badan sungai. WALHI kemudian melakukan serangkaian uji laboratorium terhadap sampel air dan tanah yang hasilnya menunjukkan kadar logam berat seperti nikel, kromium, dan mangan jauh melampaui baku mutu lingkungan yang ditetapkan pemerintah.</p>
<p>Setelah mediasi dengan pemerintah daerah dan perusahaan tidak membuahkan hasil, WALHI Sulteng bersama masyarakat terdampak mengajukan gugatan class action ke Pengadilan Negeri Poso pada awal tahun 2023. Gugatan tersebut mendaftar dengan nomor 202/Pdt.Sus-LH/2024/PN Pso, dengan tuntutan ganti rugi materil dan immateril, serta perintah penghentian operasi sampai dengan dipenuhinya ketentuan lingkungan yang berlaku.</p>
<p>Persidangan berlangsung selama kurang lebih satu tahun dengan menghadirkan puluhan saksi dan ahli dari berbagai disiplin ilmu, termasuk ahli lingkungan, ahli kesehatan masyarakat, dan ahli toksikologi. Perusahaan tergugat berupaya menolak gugatan dengan argumen bahwa kegiatan operasional mereka telah sesuai izin dan tidak menimbulkan pencemaran.</p>
<p>Pada akhirnya, Majelis Hakim Pengadilan Negeri Poso mengeluarkan putusan yang mengabulkan sebagian gugatan masyarakat. Putusan ini menjadi salah satu tonggak penting dalam penegakan hukum lingkungan di Indonesia, khususnya terkait pertanggungjawaban perusahaan nikel terhadap dampak lingkungan dan kesehatan masyarakat.</p>',
        ]);

        $this->command->info('Dummy artikel created: ID ' . $artikel->id);
    }
}
