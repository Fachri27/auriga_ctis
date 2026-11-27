<?php

namespace Tests\Feature;

use App\Models\{ChartDataset, RawCase, User};
use App\Services\GoogleSheetsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Data lama dari sync sebelumnya.
        ChartDataset::create(['dataset' => 'Perkara per Tahun', 'label' => '2020', 'year' => 2020, 'value' => 99]);
        ChartDataset::create(['dataset' => 'Kabupaten', 'label' => 'KABUPATEN LAMA', 'year' => null, 'value' => 7]);
        RawCase::insert(['no_perkara' => 'lama/Pid.B/2020/PN', 'tahun' => 2020]);
    }

    private function fakeSheets(array $kpiRow = ['Total Perkara', '3']): void
    {
        $longVerdict = 'Putusan sela: '.str_repeat('pemeriksaan perkara ditangguhkan ', 12);

        $this->mock(GoogleSheetsService::class, function ($mock) use ($kpiRow, $longVerdict) {
            $mock->shouldReceive('getSheetNames')->andReturn(['CleanData', 'DashboardData', 'Dashboard']);
            $mock->shouldReceive('getDataAsJson')->with('CleanData!A1:Z')->andReturn([
                ['No Perkara' => '1/Pid.B/2026/PN', 'Tahun' => '2026', 'Kabupaten/Kota' => 'KABUPATEN KETAPANG', 'Terdakwa' => 'A', 'Jumlah Terdakwa' => '1',
                    'Tuntutan Penjara' => '18', 'Vonis Penjara' => '12', 'Tuntutan Denda' => '50000000000000', 'Vonis Denda' => '500000000'],
                ['No Perkara' => '2/Pid.B/2026/PN', 'Tahun' => '2026', 'Kabupaten/Kota' => 'Kabupaten Ketapang', 'Terdakwa' => 'B', 'Jumlah Terdakwa' => '2'],
                ['No Perkara' => '3/Pid.B/PN', 'Tahun' => '', 'Kabupaten/Kota' => 'KABUPATEN SINTANG', 'Terdakwa' => 'C', 'Jumlah Terdakwa' => '1'],
            ]);
            $mock->shouldReceive('getData')->with('DashboardData!A1:Z')->andReturn([
                array_fill(0, 21, 'header'),
                ['2026', '2', '', 'MINERBA', '3', '', 'Bersalah', '2'],
                ['', '', '', '', '', '', $longVerdict, '1'],
            ]);
            $mock->shouldReceive('getData')->with('Dashboard!A1:Z')->andReturn([$kpiRow]);
        });
    }

    public function test_sync_replaces_charts_and_raw_cases(): void
    {
        $this->fakeSheets();

        $this->artisan('chart:sync')->assertSuccessful();

        $this->assertSame(['1/Pid.B/2026/PN', '2/Pid.B/2026/PN', '3/Pid.B/PN'], RawCase::orderBy('no_perkara')->pluck('no_perkara')->all());
        // Tuntutan ikut diimpor; tuntutan denda puluhan triliun (ada di sheet) muat di kolomnya.
        $this->assertEquals([18, 12, 50_000_000_000_000, 500_000_000], array_values(RawCase::where('no_perkara', '1/Pid.B/2026/PN')
            ->first(['tuntutan_penjara', 'vonis_penjara', 'tuntutan_denda', 'vonis_denda'])->toArray()));
        $this->assertSame([2026 => 2.0], ChartDataset::where('dataset', 'Perkara per Tahun')->pluck('value', 'year')->map(fn ($v) => (float) $v)->all());
        // Kabupaten yang hanya beda huruf besar/kecil digabung.
        $this->assertEquals(2, ChartDataset::where('dataset', 'Kabupaten')->whereNull('year')->where('label', 'KABUPATEN KETAPANG')->value('value'));
        $this->assertFalse(ChartDataset::where('label', 'KABUPATEN LAMA')->exists());
        // Teks putusan yang panjang dipotong sesuai kolom label.
        $this->assertSame(255, mb_strlen(ChartDataset::where('dataset', 'Vonis Putusan')->where('label', 'like', 'Putusan sela:%')->value('label')));
    }

    public function test_sync_stores_the_dashboard_tab_kpis_by_key(): void
    {
        $this->fakeSheets(['Total Perkara', '11620', 'Total Terdakwa', '15814', '% Bersalah (dari perkara putus)', '98.6%',
            'Rata-rata Vonis Penjara (bulan)', '12.2', 'Rata-rata Vonis Denda (Rp)', 'Rp896,174,508', 'Jumlah Pengadilan', '357']);

        $this->artisan('chart:sync')->assertSuccessful();

        $this->assertEquals([
            'jumlah_pengadilan' => 357, 'pct_vonis_bersalah' => 98.6, 'rata_vonis_denda' => 896174508,
            'rata_vonis_penjara' => 12.2, 'total_perkara' => 11620, 'total_terdakwa' => 15814,
        ], ChartDataset::where('dataset', 'KPI')->orderBy('label')->pluck('value', 'label')->map(fn ($v) => (float) $v)->all());
    }

    public function test_failed_sync_keeps_the_previous_data(): void
    {
        // Sel KPI yang rusak membuat sync gagal di tengah jalan.
        $this->fakeSheets(['Total Perkara', ['bukan teks']]);

        $this->artisan('chart:sync')->assertFailed();

        $this->assertSame(['lama/Pid.B/2020/PN'], RawCase::pluck('no_perkara')->all());
        $this->assertEquals(99, ChartDataset::where('dataset', 'Perkara per Tahun')->value('value'));
        $this->assertTrue(ChartDataset::where('label', 'KABUPATEN LAMA')->exists());
    }

    public function test_cms_sync_button_runs_the_sync_right_away(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->fakeSheets();

        // Tidak lewat antrean: data langsung berganti dan hasilnya tampil di halaman chart.
        $this->actingAs($admin)->get('/cms/charts/sync')
            ->assertRedirect(route('charts.dashboard'))
            ->assertSessionHas('success', 'Sync selesai: 3 perkara dari spreadsheet.');
        $this->assertSame(3, RawCase::count());
        // Pesannya tampil di halaman chart (tujuan redirect).
        $this->get(route('charts.dashboard'))->assertOk()->assertSee('Sync selesai: 3 perkara dari spreadsheet.');

        $this->fakeSheets(['Total Perkara', ['bukan teks']]);
        $this->actingAs($admin)->get('/cms/charts/sync')
            ->assertSessionHas('error', fn ($message) => str_starts_with($message, 'Sync gagal, data lama tetap dipakai'));
        $this->assertSame(3, RawCase::count());
    }
}
