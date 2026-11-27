<?php

namespace Tests\Feature;

use App\Services\CaseImport\LocationMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerkaraDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Basis data wilayah (PostGIS) tidak dipakai di test: setiap kabupaten mendapat titik tiruan.
        $this->mock(LocationMatcher::class, fn ($mock) => $mock->shouldReceive('match')
            ->andReturn(['label' => 'Kabupaten', 'lat' => -1.0, 'lng' => 115.0, 'precision' => 'kabupaten']));

        $rows = [
            ['MINERBA', 2021, 'Kalimantan', 'KABUPATEN BARITO UTARA', 'Perorangan', 'Bersalah', 2],
            ['MINERBA', 2022, 'Kalimantan', 'KABUPATEN BARITO UTARA', 'Korporasi', 'Bersalah', 1],
            ['ILLEGAL LOGGING', 2022, 'Sumatera', 'KABUPATEN MUSI BANYUASIN', 'Perorangan', 'Bebas', 3],
            // Di luar rentang tahun dashboard: tidak dihitung.
            ['MINERBA', 2015, 'Kalimantan', 'KABUPATEN BARITO UTARA', 'Perorangan', 'Bersalah', 1],
        ];
        foreach ($rows as $i => [$klasifikasi, $tahun, $pulau, $kabupaten, $subjek, $vonis, $terdakwa]) {
            DB::table('raw_cases')->insert([
                'no_perkara' => "{$i}/Pid.B/{$tahun}/PN", 'pengadilan' => "PN {$i}", 'klasifikasi_clean' => $klasifikasi,
                'tahun' => $tahun, 'pulau' => $pulau, 'kabupaten' => $kabupaten, 'subjek_hukum' => $subjek,
                'vonis_putusan' => $vonis, 'jumlah_terdakwa' => $terdakwa, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function test_perkara_mode_shows_kpis_classifications_and_yearly_bars(): void
    {
        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            ->assertSee('Dashboard Perkara Publik')
            ->assertSee('Klasifikasi Perkara')
            ->assertSeeInOrder(['MINERBA', 'ILLEGAL LOGGING'])
            ->assertSee('id="perkara-perkara-tahun"', false)
            ->assertSee('id="perkara-terdakwa-tahun"', false)
            ->assertSee('id="perkara-pengadilan-tahun"', false)
            ->assertSee('id="perkaraMap"', false)
            // Filter & tautan klasifikasi ditangani skrip tanpa reload; data peta dibaca dari tag JSON.
            ->assertSee('data-dash-filter', false)
            ->assertSee('id="perkara-map-data"', false)
            ->assertSee('data-mode="perkara"', false)
            // Peta & daftar klasifikasi bisa dibuat layar penuh; kontrol peta bersama ada di gdDashMap.
            ->assertSee('data-map-card', false)
            ->assertSee('window.gdDashMap', false)
            // Mode Kasus tidak dirender di mode Perkara.
            ->assertDontSee('id="caseList"', false)
            ->assertViewHas('regencies', fn ($r) => count($r['points']) === 2 && $r['points'][0]['name'] === 'Kabupaten Barito Utara'
                && $r['points'][0]['total'] === 2 && $r['unmapped'] === 0)
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->pluck('display', 'title')->only(['Perkara', 'Terdakwa', 'Korporasi', 'Vonis Bebas'])->all()
                === ['Perkara' => '3', 'Terdakwa' => '6', 'Korporasi' => '1', 'Vonis Bebas' => '1']);
    }

    public function test_defendant_breakdown_and_criminal_sentences(): void
    {
        $sentences = [
            // [no_perkara, tahun, vonis_putusan, tuntutan & vonis penjara (bulan), tuntutan & vonis denda (Rp)]
            ['0/Pid.B/2021/PN', 2021, 'Bersalah', [18, 12], [1_000_000_000, 500_000_000]],
            ['1/Pid.B/2022/PN', 2022, 'Bersalah', [12, 6], [2_000_000_000, 1_500_000_000]],
            ['2/Pid.B/2022/PN', 2022, 'Bebas', [10, 0], [1_000_000_000, 0]],
        ];
        foreach ($sentences as [$noPerkara, $tahun, $vonis, [$tuntutanPenjara, $penjara], [$tuntutanDenda, $denda]]) {
            DB::table('raw_cases')->where('no_perkara', $noPerkara)->update([
                'vonis_putusan' => $vonis, 'tuntutan_penjara' => $tuntutanPenjara, 'vonis_penjara' => $penjara,
                'tuntutan_denda' => $tuntutanDenda, 'vonis_denda' => $denda,
            ]);
        }

        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            // Perorangan/Korporasi merinci 6 terdakwa (5 + 1), bukan jumlah perkara.
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->keyBy('title')->only(['Perorangan', 'Korporasi', 'Rata-rata Penjara', 'Rata-rata Denda', 'Total Denda'])
                ->map(fn ($k) => [$k['display'], $k['share'] ?? null])->all() === [
                    'Perorangan' => ['5', '83,3%'],
                    'Korporasi' => ['1', '16,7%'],
                    // Seperti tab Dashboard spreadsheet: perkara bebas ikut dirata-rata sebagai 0.
                    'Rata-rata Penjara' => ['6 bulan', null],
                    // Rupiah tampil lengkap, tidak diringkas ke juta/miliar.
                    'Rata-rata Denda' => ['Rp666.666.667', null],
                    'Total Denda' => ['Rp2.000.000.000', null],
                ])
            // Angka tampil di atas labelnya.
            ->assertSeeInOrder(['Keterangan Terdakwa', 'Perorangan', 'Korporasi', 'Vonis Putusan', 'Vonis Pidana', '6 bulan', 'Rata-rata Penjara', 'Rp666.666.667', 'Rata-rata Denda', 'Rp2.000.000.000', 'Total Denda'])
            // Grafik rata-rata vonis yang sudah ada sekaligus memuat batang tuntutan per tahun.
            ->assertViewHas('yearlyCharts', fn ($charts) => collect($charts)->keyBy('id')->only(['perkara-penjara-tahun', 'perkara-denda-tahun'])
                ->map(fn ($c) => [$c['unit'], array_column($c['series'], 'key'), $c['data']])->all() === [
                    'perkara-penjara-tahun' => ['bulan', ['tuntutan', 'vonis'], [
                        ['label' => '2021', 'tuntutan' => 18.0, 'vonis' => 12.0],
                        ['label' => '2022', 'tuntutan' => 11.0, 'vonis' => 3.0],
                    ]],
                    'perkara-denda-tahun' => ['rupiah', ['tuntutan', 'vonis'], [
                        ['label' => '2021', 'tuntutan' => 1000000000.0, 'vonis' => 500000000.0],
                        ['label' => '2022', 'tuntutan' => 1500000000.0, 'vonis' => 750000000.0],
                    ]],
                ] && count($charts) === 5)
            ->assertSee('Rata-rata Tuntutan &amp; Vonis Denda per Tahun', false)
            ->assertSee('data-series="', false)
            ->assertSee('data-ratio-label="Vonis :percent dari tuntutan"', false)
            ->assertSee('Rata-rata tuntutan dihitung dari perkara yang tuntutan jaksanya tercatat.')
            ->assertSee('data-unit="rupiah"', false)
            ->assertSee('data-unit-label="bulan"', false);

        $this->get('/en/dashboard?mode=perkara')
            ->assertOk()
            ->assertSee('Defendant Breakdown')
            ->assertSee('6 months')
            ->assertSee('Rp666,666,667')
            ->assertSee('Prosecution Demand')
            ->assertSee('data-unit-label="months"', false);
    }

    public function test_years_without_demands_have_no_demand_bar(): void
    {
        // Sebelum kolom tuntutan terisi sync: batang tuntutan kosong (null), batang vonis tetap tampil.
        DB::table('raw_cases')->update(['vonis_penjara' => 12, 'vonis_denda' => 500_000_000]);

        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            ->assertViewHas('yearlyCharts', fn ($charts) => collect($charts)->firstWhere('id', 'perkara-denda-tahun')['data']
                === [['label' => '2021', 'tuntutan' => null, 'vonis' => 500000000.0], ['label' => '2022', 'tuntutan' => null, 'vonis' => 500000000.0]]);
    }

    public function test_rows_without_year_count_in_totals_but_not_in_yearly_bars(): void
    {
        // Tab Dashboard spreadsheet juga menghitung baris CleanData yang tahunnya kosong.
        DB::table('raw_cases')->insert([
            'no_perkara' => '9/Pid.B/PN', 'pengadilan' => 'PN 9', 'klasifikasi_clean' => 'MINERBA', 'tahun' => null,
            'pulau' => 'Kalimantan', 'subjek_hukum' => 'Perorangan', 'vonis_putusan' => 'Bersalah', 'jumlah_terdakwa' => 4,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->pluck('display', 'title')->only(['Perkara', 'Terdakwa', 'Vonis Bersalah'])->all()
                === ['Perkara' => '4', 'Terdakwa' => '10', 'Vonis Bersalah' => '3'])
            ->assertViewHas('classifications', fn ($c) => $c[0] === ['label' => 'MINERBA', 'value' => 3])
            ->assertViewHas('yearlyCharts', fn ($charts) => $charts[0]['data'] === [['label' => '2021', 'value' => 1.0], ['label' => '2022', 'value' => 2.0]]);

        // Dengan filter tahun, baris tanpa tahun tidak ikut.
        $this->get('/id/dashboard?mode=perkara&tahun=2022')
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->firstWhere('title', 'Perkara')['display'] === '2');
    }

    public function test_period_runs_to_the_latest_year_in_the_data(): void
    {
        // Tab Dashboard spreadsheet (filter tahun "Semua") ikut menghitung perkara tahun terbaru;
        // tahun setelah tahun berjalan dianggap salah ketik.
        foreach ([now()->year, now()->year + 5] as $i => $tahun) {
            DB::table('raw_cases')->insert([
                'no_perkara' => "1{$i}/Pid.B/{$tahun}/PN", 'pengadilan' => "PN 1{$i}", 'klasifikasi_clean' => 'MINERBA', 'tahun' => $tahun,
                'pulau' => 'Kalimantan', 'subjek_hukum' => 'Perorangan', 'vonis_putusan' => 'Bersalah', 'jumlah_terdakwa' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->firstWhere('title', 'Perkara')['display'] === '4')
            ->assertViewHas('filterOptions', fn ($o) => (int) $o['tahun']->first() === now()->year);

        $this->getJson('/id')
            ->assertOk()
            ->assertJsonPath('kpiData.0', ['title' => 'Perkara', 'display' => '4']);
    }

    public function test_sentence_charts_are_hidden_without_sentence_data(): void
    {
        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            ->assertViewHas('yearlyCharts', fn ($charts) => array_column($charts, 'id') === ['perkara-perkara-tahun', 'perkara-terdakwa-tahun', 'perkara-pengadilan-tahun'])
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->firstWhere('title', 'Rata-rata Penjara')['display'] === '–');
    }

    public function test_court_count_without_filters_follows_the_spreadsheet(): void
    {
        // Angka "Jumlah Pengadilan" tab Dashboard spreadsheet (disimpan chart:sync) dipakai apa adanya.
        \App\Models\ChartDataset::create(['dataset' => 'KPI', 'label' => 'jumlah_pengadilan', 'year' => null, 'value' => 7]);

        $court = fn ($kpis) => collect($kpis)->firstWhere('title', 'Pengadilan')['display'];
        $this->get('/id/dashboard?mode=perkara')->assertViewHas('kpiData', fn ($kpis) => $court($kpis) === '7');
        $this->getJson('/id')->assertJsonPath('kpiData.2', ['title' => 'Pengadilan', 'display' => '7']);

        // Dengan filter, dihitung dari data seperti grafik Pengadilan per Tahun.
        $this->get('/id/dashboard?mode=perkara&pulau=Sumatera')->assertViewHas('kpiData', fn ($kpis) => $court($kpis) === '1');
    }

    public function test_filters_narrow_the_figures_but_keep_every_classification_listed(): void
    {
        $this->get('/id/dashboard?mode=perkara&klasifikasi=MINERBA')
            ->assertOk()
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->firstWhere('title', 'Perkara')['display'] === '2')
            ->assertViewHas('classifications', fn ($c) => count($c) === 2)
            ->assertSee('aria-current="true"', false);

        $this->get('/id/dashboard?mode=perkara&pulau=Sumatera')
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->firstWhere('title', 'Perkara')['display'] === '1');
    }

    public function test_regency_recorded_by_too_many_courts_is_left_off_the_map(): void
    {
        // Satu kabupaten dicatat oleh 6 pengadilan berbeda: diduga salah isi di sumber data.
        foreach (range(1, 6) as $i) {
            DB::table('raw_cases')->insert([
                'no_perkara' => "{$i}/Pid.Sus/2023/PN", 'pengadilan' => "PN Lain {$i}", 'klasifikasi_clean' => 'MINERBA',
                'tahun' => 2023, 'pulau' => 'Kalimantan', 'kabupaten' => 'KABUPATEN SALAH ISI', 'jumlah_terdakwa' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->get('/id/dashboard?mode=perkara')
            ->assertOk()
            ->assertSee('6 perkara berlabel Kabupaten Salah Isi tidak dipetakan')
            ->assertViewHas('regencies', fn ($r) => count($r['points']) === 2
                && $r['doubtful'] === ['count' => 6, 'regencies' => ['Kabupaten Salah Isi'], 'courts' => 6])
            // KPI tetap menghitungnya: perkaranya ada, hanya lokasinya yang diragukan.
            ->assertViewHas('kpiData', fn ($kpis) => collect($kpis)->firstWhere('title', 'Perkara')['display'] === '9');
    }

    public function test_default_mode_is_still_the_case_dashboard(): void
    {
        $this->get('/id/dashboard')
            ->assertOk()
            ->assertViewHas('mode', 'kasus')
            ->assertSee('id="caseList"', false)
            ->assertSee('id="kasus-data"', false)
            ->assertSee('data-map-card', false)
            ->assertSee('id="mapEmpty"', false)
            ->assertSee('Kasus di Area Peta')
            ->assertDontSee('id="perkaraMap"', false);
    }
}
