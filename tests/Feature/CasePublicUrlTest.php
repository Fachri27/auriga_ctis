<?php

namespace Tests\Feature;

use App\Models\CaseModel;
use App\Services\ReverseGeocoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CasePublicUrlTest extends TestCase
{
    use RefreshDatabase;

    private CaseModel $case;

    protected function setUp(): void
    {
        parent::setUp();

        // Halaman detail memanggil geocoder (Nominatim/PostGIS); di test diganti data tetap.
        $this->mock(ReverseGeocoder::class, function ($mock) {
            $mock->shouldReceive('getLocation')->andReturn(['province' => 'Kalimantan Timur', 'district' => 'Samarinda', 'village' => null]);
            $mock->shouldReceive('cached')->andReturn(null);
        });

        $this->case = $this->publicCase('CASE-XLTLR', 'Suap Perizinan Tambang Ilegal', 'Illegal Mining Permit Bribery');
    }

    public function test_url_is_title_plus_short_code_per_language(): void
    {
        $this->assertSame('suap-perizinan-tambang-ilegal-xltlr', CaseModel::publicSlug('CASE-XLTLR', 'Suap Perizinan Tambang Ilegal'));
        $this->assertSame('xltlr', CaseModel::publicSlug('CASE-XLTLR', null));

        // Judul panjang dipotong di batas kata; kode tetap utuh di akhir.
        $long = CaseModel::publicSlug('CASE-AB12C', str_repeat('Penebangan liar di kawasan hutan lindung ', 4));
        $this->assertSame('penebangan-liar-di-kawasan-hutan-lindung-penebangan-liar-di-ab12c', $long);

        $this->assertStringEndsWith('/id/kasus/suap-perizinan-tambang-ilegal-xltlr', $this->case->publicUrl('id'));
        $this->assertStringEndsWith('/en/cases/illegal-mining-permit-bribery-xltlr', $this->case->publicUrl('en'));
    }

    public function test_clean_urls_open_the_case_in_each_language(): void
    {
        $this->get('/id/kasus/suap-perizinan-tambang-ilegal-xltlr')->assertOk()->assertSee('Suap Perizinan Tambang Ilegal');
        $this->get('/en/cases/illegal-mining-permit-bribery-xltlr')->assertOk()->assertSee('Illegal Mining Permit Bribery');

        // Kode yang memuat tanda hubung (kasus lama "CASE-2024-001").
        $old = $this->publicCase('CASE-2024-001', 'Korupsi Proyek Jalan', null);
        $this->get('/id/kasus/korupsi-proyek-jalan-2024-001')->assertOk();
        $this->assertStringEndsWith('/id/kasus/korupsi-proyek-jalan-2024-001', $old->publicUrl('id'));
    }

    public function test_old_and_non_canonical_urls_redirect_permanently(): void
    {
        $id = '/id/kasus/suap-perizinan-tambang-ilegal-xltlr';
        $en = '/en/cases/illegal-mining-permit-bribery-xltlr';

        // Alamat lama tetap jalan.
        $this->get('/id/detail-case/CASE-XLTLR')->assertStatus(301)->assertRedirect($id);
        $this->get('/en/detail-case/CASE-XLTLR')->assertStatus(301)->assertRedirect($en);
        // Judul lama (setelah judul diubah), segmen bahasa lain, atau huruf besar → alamat resmi.
        $this->get('/id/kasus/judul-lama-xltlr')->assertStatus(301)->assertRedirect($id);
        $this->get('/en/kasus/suap-perizinan-tambang-ilegal-xltlr')->assertStatus(301)->assertRedirect($en);
        $this->get('/id/kasus/suap-perizinan-tambang-ilegal-XLTLR')->assertStatus(301)->assertRedirect($id);
    }

    public function test_unknown_or_unpublished_cases_are_not_found(): void
    {
        $this->publicCase('CASE-HIDDN', 'Kasus Internal', null, public: false);

        $this->get('/id/kasus/kasus-internal-hiddn')->assertNotFound();
        $this->get('/id/detail-case/CASE-HIDDN')->assertNotFound();
        $this->get('/id/kasus/tidak-ada-zzzzz')->assertNotFound();
    }

    public function test_share_links_and_language_switch_use_the_clean_url(): void
    {
        $clean = $this->case->publicUrl('id');

        $this->get('/id/kasus/suap-perizinan-tambang-ilegal-xltlr?fbclid=IwAR3abc&utm_source=facebook')
            ->assertOk()
            ->assertSee('https://wa.me/?text=Suap+Perizinan+Tambang+Ilegal%20'.urlencode($clean), false)
            ->assertSee('data-url="'.$clean.'"', false)
            ->assertDontSee('IwAR3abc')
            // Tombol EN di menu langsung ke alamat Inggris, tanpa pengalihan.
            ->assertSee('href="'.$this->case->publicUrl('en').'"', false);
    }

    private function publicCase(string $number, string $titleId, ?string $titleEn, bool $public = true): CaseModel
    {
        $statusId = DB::table('statuses')->value('id')
            ?? DB::table('statuses')->insertGetId(['key' => 'investigation', 'name' => 'Penyidikan', 'created_at' => now(), 'updated_at' => now()]);

        $case = CaseModel::create([
            'case_number' => $number,
            'status_id' => $statusId,
            'event_date' => '2026-03-01',
            'is_public' => $public,
            'published_at' => now(),
        ]);
        $case->translations()->create(['locale' => 'id', 'title' => $titleId, 'description' => '<p>Ringkasan.</p>']);
        if ($titleEn) {
            $case->translations()->create(['locale' => 'en', 'title' => $titleEn, 'description' => '<p>Summary.</p>']);
        }

        return $case->load('translations');
    }
}
