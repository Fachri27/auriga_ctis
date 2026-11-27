<?php

namespace Tests\Feature;

use App\Livewire\Cases\CaseForm;
use App\Models\{CaseModel, User};
use App\Services\ReverseGeocoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lokasi kasus ditampilkan sesuai teks dokumen/CMS; nama wilayah dari titik peta hanya cadangan.
 */
class CaseLokasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reverse geocoding titik rata-rata kabupaten bisa jatuh di desa sembarang.
        $this->mock(ReverseGeocoder::class, function ($mock) {
            $mock->shouldReceive('getLocation')->andReturn(['province' => 'Kalimantan Timur', 'district' => 'Kota Balikpapan', 'village' => 'Desa Acak']);
        });
    }

    public function test_public_page_shows_the_document_location_text(): void
    {
        $case = $this->publicCase(['lokasi' => 'Teluk Balikpapan, Kalimantan Timur', 'latitude' => -1.228, 'longitude' => 116.859]);

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSee('id="map"', false)
            ->assertSeeInOrder(['Fakta Kasus', 'Lokasi', 'Teluk Balikpapan, Kalimantan Timur', 'Dipublikasikan'])
            ->assertSee('Lokasi: Teluk Balikpapan, Kalimantan Timur.', false)
            ->assertDontSee('Desa Acak');
    }

    public function test_location_text_is_shown_without_a_map_point(): void
    {
        $case = $this->publicCase(['lokasi' => 'Perairan Selat Malaka, WPP-NRI 571']);

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertDontSee('id="map"', false)
            ->assertSee('Perairan Selat Malaka, WPP-NRI 571');
    }

    public function test_cases_without_text_use_the_map_point_names(): void
    {
        $case = $this->publicCase(['latitude' => -1.228, 'longitude' => 116.859]);

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder(['Provinsi', 'Kalimantan Timur', 'Kab/Kota', 'Kota Balikpapan'])
            ->assertSee('Desa Acak, Kota Balikpapan, Kalimantan Timur');
    }

    public function test_case_form_saves_the_location_text(): void
    {
        $case = $this->publicCase(['lokasi' => 'Teluk Balikpapan']);

        Livewire::actingAs(User::factory()->create())->test(CaseForm::class, ['caseId' => $case->id])
            ->assertSet('lokasi', 'Teluk Balikpapan')
            ->set('lokasi', '  Teluk Balikpapan, Kalimantan Timur  ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Teluk Balikpapan, Kalimantan Timur', $case->fresh()->lokasi);
    }

    private function publicCase(array $attributes = []): CaseModel
    {
        $statusId = DB::table('statuses')->insertGetId(['key' => 'open', 'name' => 'Terbuka', 'created_at' => now(), 'updated_at' => now()]);
        $categoryId = DB::table('categories')->insertGetId(['slug' => 'pencemaran', 'created_at' => now(), 'updated_at' => now()]);
        $case = CaseModel::create($attributes + [
            'case_number' => 'CASE-LOKAS',
            'status_id' => $statusId,
            'category_ids' => [$categoryId],
            'event_date' => '2018-03-31',
            'is_public' => true,
            'published_at' => now(),
        ]);
        $case->translations()->create(['locale' => 'id', 'title' => 'Tumpahan Minyak di Teluk Balikpapan', 'description' => '<p>Pipa patah.</p>']);
        $case->translations()->create(['locale' => 'en', 'title' => 'Oil Spill in Balikpapan Bay', 'description' => '<p>Pipe broke.</p>']);

        return $case;
    }
}
