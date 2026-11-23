<?php

namespace Database\Seeders;

use App\Models\{CaseModel, Category};
use App\Services\{CasePublisher, CaseWriter};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Kasus contoh untuk melihat tahapan kasus per kualifikasi di halaman publik (lihat App\Services\CaseStages):
 * pidana, perdata (gugatan → pembuktian → vonis), dan administrasi (sanksi), masing-masing di tahap berbeda.
 *
 *   php artisan db:seed --class=KualifikasiDemoSeeder
 *
 * Kasusnya fiktif dan ditandai "[Contoh]", jadi tidak dijalankan di production. Aman diulang: kasus dikenali dari
 * source_ref, sehingga menjalankan ulang memperbarui kasus yang sama, bukan menambah kasus baru.
 */
class KualifikasiDemoSeeder extends Seeder
{
    private const CASES = [
        [
            'ref' => 'pidana-penuntutan',
            'kualifikasi' => 'pidana',
            'status' => 'prosecution',
            'lat' => -0.5022, 'lng' => 101.4478,
            'id' => ['Pembalakan Liar di Hutan Lindung Bukit Batabuh', 'Penyidik menetapkan pemodal dan dua operator alat berat sebagai tersangka pembalakan liar di hutan lindung. Berkas perkara telah dilimpahkan ke kejaksaan dan jaksa sedang menyusun surat dakwaan.'],
            'en' => ['Illegal Logging in the Bukit Batabuh Protected Forest', 'Investigators named a financier and two heavy-equipment operators as suspects for illegal logging in a protected forest. The case file has been handed to prosecutors, who are drafting the indictment.'],
        ],
        [
            'ref' => 'perdata-gugatan',
            'kualifikasi' => 'perdata',
            'status' => 'gugatan',
            'lat' => -6.9147, 'lng' => 107.6098,
            'id' => ['Gugatan Ganti Rugi Pencemaran Sungai oleh Pabrik Tekstil', 'Warga dan organisasi lingkungan menggugat pabrik tekstil yang membuang limbah cair tanpa diolah ke sungai. Penggugat menuntut ganti rugi bagi petani dan nelayan serta pemulihan kualitas air sungai.'],
            'en' => ['Compensation Lawsuit over River Pollution by a Textile Factory', 'Residents and an environmental organization sued a textile factory that discharged untreated wastewater into a river. The plaintiffs seek compensation for farmers and fishers and restoration of the river\'s water quality.'],
        ],
        [
            'ref' => 'perdata-pembuktian',
            'kualifikasi' => 'perdata',
            'status' => 'pembuktian',
            'lat' => 0.5071, 'lng' => 101.4478,
            'id' => ['Gugatan Pemulihan Lahan Gambut Terbakar di Konsesi Sawit', 'Pemerintah menggugat perusahaan sawit atas kebakaran lahan gambut di konsesinya. Persidangan memasuki tahap pembuktian: ahli kebakaran dan ahli gambut dihadirkan untuk menilai luas kerusakan dan biaya pemulihan.'],
            'en' => ['Lawsuit to Restore Burned Peatland in a Palm Oil Concession', 'The government sued a palm oil company over peatland fires in its concession. The trial has entered the evidentiary stage, with fire and peat experts testifying on the extent of the damage and the cost of restoration.'],
            // Keterangan custom per tahapan; tahapan lain memakai keterangan standar.
            'tahapan' => [
                'id' => [
                    'gugatan' => 'Gugatan didaftarkan Kementerian Lingkungan Hidup di Pengadilan Negeri Pekanbaru pada 3 Februari 2026, menuntut ganti rugi dan biaya pemulihan Rp250 miliar.',
                    'pembuktian' => 'Sidang pembuktian berjalan sejak Mei 2026. Penggugat menghadirkan ahli kebakaran hutan dan ahli gambut.',
                ],
                'en' => [
                    'gugatan' => 'The Ministry of Environment filed the lawsuit at the Pekanbaru District Court on 3 February 2026, claiming Rp250 billion in damages and restoration costs.',
                    'pembuktian' => 'Evidentiary hearings have been under way since May 2026. The plaintiff has called forest fire and peat experts.',
                ],
            ],
        ],
        [
            'ref' => 'perdata-vonis',
            'kualifikasi' => 'perdata',
            'status' => 'vonis',
            'lat' => -0.4948, 'lng' => 117.1436,
            'id' => ['Gugatan Warga atas Banjir Lumpur dari Tambang Batu Bara', 'Pengadilan mengabulkan sebagian gugatan warga atas banjir lumpur dari kolam tambang batu bara yang jebol. Perusahaan dihukum membayar ganti rugi dan memulihkan sawah serta saluran irigasi yang tertimbun lumpur.'],
            'en' => ['Residents\' Lawsuit over Mudflows from a Coal Mine', 'The court partly granted residents\' lawsuit over mudflows from a breached coal mine pond. The company was ordered to pay compensation and restore the rice fields and irrigation channels buried in mud.'],
        ],
        [
            'ref' => 'administrasi-terbuka',
            'kualifikasi' => 'administrasi',
            'status' => 'open',
            'lat' => -2.2161, 'lng' => 113.9135,
            'id' => ['Pembuangan Limbah Tanpa Izin oleh Pabrik Kelapa Sawit', 'Warga melaporkan pabrik kelapa sawit yang membuang limbah cair ke anak sungai tanpa izin pembuangan. Dinas lingkungan hidup menjadwalkan pengawasan lapangan sebelum menentukan sanksi.'],
            'en' => ['Unpermitted Waste Discharge by a Palm Oil Mill', 'Residents reported a palm oil mill discharging wastewater into a tributary without a discharge permit. The environmental agency has scheduled a field inspection before deciding on sanctions.'],
        ],
        [
            'ref' => 'administrasi-sanksi',
            'kualifikasi' => 'administrasi',
            'status' => 'sanksi',
            'lat' => -8.5833, 'lng' => 116.1167,
            'id' => ['Pencabutan Izin Tambang Pasir di Kawasan Pesisir', 'Pemerintah provinsi mencabut izin usaha tambang pasir yang menambang di luar wilayah izinnya dan merusak sempadan pantai. Perusahaan juga diwajibkan memulihkan bekas galian.'],
            'en' => ['Sand Mining Permit Revoked in a Coastal Area', 'The provincial government revoked the permit of a sand mining company that mined outside its permitted area and damaged the coastal buffer zone. The company must also restore the excavated land.'],
        ],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('KualifikasiDemoSeeder berisi kasus fiktif, tidak dijalankan di production.');

            return;
        }

        $categoryId = Category::where('slug', 'environment')->value('id') ?? Category::value('id');
        $statusIds = DB::table('statuses')->pluck('id', 'key');

        foreach (self::CASES as $item) {
            $case = app(CaseWriter::class)->save(
                CaseModel::where('source_ref', 'demo:kualifikasi:'.$item['ref'])->first(),
                [
                    'source_ref' => 'demo:kualifikasi:'.$item['ref'],
                    'kualifikasi' => $item['kualifikasi'],
                    'status_id' => $statusIds->get($item['status']),
                    'category_ids' => $categoryId ? [$categoryId] : [],
                    'event_date' => '2026-06-01',
                    'latitude' => $item['lat'],
                    'longitude' => $item['lng'],
                    'sumber' => 'Data contoh',
                ],
                [
                    'id' => ['title' => '[Contoh] '.$item['id'][0], 'description' => '<p>'.$item['id'][1].'</p>', 'keterangan_tahapan' => $item['tahapan']['id'] ?? null],
                    'en' => ['title' => '[Example] '.$item['en'][0], 'description' => '<p>'.$item['en'][1].'</p>', 'keterangan_tahapan' => $item['tahapan']['en'] ?? null],
                ],
            );

            app(CasePublisher::class)->publish($case->id);

            $this->command?->info("{$case->case_number}: {$item['kualifikasi']} / {$item['status']}");
        }
    }
}
