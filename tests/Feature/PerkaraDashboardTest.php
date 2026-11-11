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
