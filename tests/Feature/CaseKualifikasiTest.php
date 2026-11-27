<?php

namespace Tests\Feature;

use App\Livewire\Cases\CaseForm;
use App\Models\{CaseModel, User};
use App\Services\{CasePublisher, ReverseGeocoder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CaseKualifikasiTest extends TestCase
{
    use RefreshDatabase;

    private int $statusId;

    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();

        // Halaman detail memanggil geocoder (Nominatim/PostGIS); di test diganti data tetap.
        $this->mock(ReverseGeocoder::class, function ($mock) {
            $mock->shouldReceive('getLocation')->andReturn(['province' => 'Kalimantan Timur', 'district' => 'Samarinda', 'village' => null]);
        });

        $this->statusId = DB::table('statuses')->insertGetId(['key' => 'investigation', 'name' => 'Investigasi', 'created_at' => now(), 'updated_at' => now()]);
        $this->categoryId = DB::table('categories')->insertGetId(['slug' => 'tambang', 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_case_form_saves_the_kualifikasi(): void
    {
        $case = $this->publicCase();
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(CaseForm::class, ['caseId' => $case->id])
            ->assertSet('kualifikasi', null)
            ->set('kualifikasi', 'perdata')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame('perdata', $case->fresh()->kualifikasi);

        Livewire::actingAs($user)->test(CaseForm::class, ['caseId' => $case->id])
            ->assertSet('kualifikasi', 'perdata')
            ->set('kualifikasi', 'tata-usaha')
            ->call('save')
            ->assertHasErrors(['kualifikasi' => 'in']);
        $this->assertSame('perdata', $case->fresh()->kualifikasi);
    }

    public function test_seeder_fills_only_empty_kualifikasi(): void
    {
        $empty = $this->publicCase(['case_number' => 'CASE-1AECP']);
        $chosen = $this->publicCase(['case_number' => 'CASE-XLTLR', 'kualifikasi' => 'administrasi']);

        $this->seed(\Database\Seeders\CaseKualifikasiSeeder::class);

        $this->assertSame('perdata', $empty->fresh()->kualifikasi);
        // Pilihan admin di CMS tidak tertimpa.
        $this->assertSame('administrasi', $chosen->fresh()->kualifikasi);
    }

    public function test_demo_seeder_publishes_example_cases_for_every_kualifikasi(): void
    {
        DB::table('categories')->where('id', $this->categoryId)->update(['slug' => 'environment']);
        foreach (['open', 'prosecution', 'vonis'] as $key) {
            DB::table('statuses')->insert(['key' => $key, 'name' => ucfirst($key), 'created_at' => now(), 'updated_at' => now()]);
        }
        // Titik peta disimpan dengan fungsi spasial MySQL; di SQLite cukup tandai publik.
        $this->mock(CasePublisher::class, function ($mock) {
            $mock->shouldReceive('publish')->andReturnUsing(
                fn (int $id) => (bool) DB::table('cases')->where('id', $id)->update(['is_public' => true, 'published_at' => now()])
            );
        });

        $this->seed(\Database\Seeders\KualifikasiDemoSeeder::class);
        $this->seed(\Database\Seeders\KualifikasiDemoSeeder::class);

        // Dijalankan ulang tidak menambah kasus baru.
        $demo = CaseModel::with(['translations', 'status'])->where('source_ref', 'like', 'demo:kualifikasi:%')->get();
        $this->assertCount(6, $demo);
        $this->assertTrue($demo->every->is_public);
        $this->assertEqualsCanonicalizing(['pidana', 'perdata', 'administrasi'], $demo->pluck('kualifikasi')->unique()->values()->all());
        $this->assertEqualsCanonicalizing(['prosecution', 'gugatan', 'pembuktian', 'vonis', 'open', 'sanksi'], $demo->pluck('status.key')->all());

        $pembuktian = $demo->firstWhere('source_ref', 'demo:kualifikasi:perdata-pembuktian');
        $html = $this->get($pembuktian->publicUrl('id'))->assertOk()
            ->assertSee('[Contoh] Gugatan Pemulihan Lahan Gambut Terbakar di Konsesi Sawit')
            ->assertSeeInOrder(['Kualifikasi Kasus', 'Perdata', 'Tahapan Kasus', 'Gugatan', 'Gugatan didaftarkan Kementerian Lingkungan Hidup', 'Pembuktian', 'Sidang pembuktian berjalan sejak Mei 2026', 'Vonis'])
            ->getContent();
        $this->assertMatchesRegularExpression('/font-bold">\s*Pembuktian\s*</', $html);
    }

    public function test_public_detail_shows_the_kualifikasi_above_the_case_stages(): void
    {
        $case = $this->publicCase(['kualifikasi' => 'pidana']);

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder(['Kualifikasi Kasus', 'Pidana', 'Ditangani lewat jalur pidana', 'Tahapan Kasus']);

        $this->get($case->publicUrl('en'))->assertOk()
            ->assertSeeInOrder(['Case Qualification', 'Criminal', 'Case Stages']);
    }

    public function test_public_detail_shows_an_empty_kualifikasi_as_undetermined(): void
    {
        $case = $this->publicCase();

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder(['Kualifikasi Kasus', 'Belum ditentukan', 'Tahapan Kasus']);

        $this->get($case->publicUrl('en'))->assertOk()
            ->assertSeeInOrder(['Case Qualification', 'Not yet determined', 'Case Stages']);
    }

    public function test_every_case_stage_shows_its_description(): void
    {
        $case = $this->publicCase();

        // Keterangan tampil di semua tahapan, termasuk yang sudah lewat dan yang belum dicapai (bukan tooltip saat hover).
        $this->get($case->publicUrl('id'))->assertOk()->assertDontSee('group-hover/step', false)->assertSeeInOrder([
            'Tahapan Kasus',
            'Terbuka', 'Laporan atau kasus baru masuk dan telah didaftarkan ke sistem.',
            'Penyelidikan', 'Penegak hukum sedang mengumpulkan bukti awal untuk menentukan apakah ada tindak pidana.',
            'Penuntutan', 'Jaksa menyusun surat dakwaan dan mempersiapkan kasus untuk dibawa ke pengadilan.',
            'Vonis', 'Hakim telah menjatuhkan putusan bersalah atau tidak bersalah kepada terdakwa.',
        ]);
    }

    public function test_case_stages_end_at_the_verdict(): void
    {
        $case = $this->publicCase();

        // Tahapan setelah Vonis (Hukum Tetap, Dijalankan, Selesai) tidak ditampilkan.
        $this->get($case->publicUrl('id'))->assertOk()
            ->assertDontSee('Putusan telah berkekuatan hukum tetap (inkracht)')
            ->assertDontSee('Terpidana sedang menjalani hukuman sesuai putusan pengadilan.')
            ->assertDontSee('Seluruh proses hukum telah selesai.');
    }

    public function test_perdata_case_shows_the_lawsuit_stages(): void
    {
        $case = $this->publicCase(['kualifikasi' => 'perdata', 'status_id' => $this->statusId('gugatan')]);

        $html = $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder(['Tahapan Kasus', 'Terbuka', 'Dipublikasikan', 'Gugatan', 'Pembuktian', 'Vonis', 'Fakta Kasus'])
            ->assertSee('Hakim memutus gugatan, misalnya menghukum tergugat membayar ganti rugi atau memulihkan lingkungan.')
            ->assertDontSee('Penyidik resmi mengusut tersangka')
            ->assertDontSee('Jaksa menyusun surat dakwaan')
            ->getContent();
        $this->assertMatchesRegularExpression('/font-bold">\s*Gugatan\s*</', $html);

        $this->get($case->publicUrl('en'))->assertOk()
            ->assertSeeInOrder(['Case Stages', 'Lawsuit', 'Evidence', 'Verdict']);
    }

    public function test_administrasi_case_shows_the_sanction_stage(): void
    {
        $case = $this->publicCase(['kualifikasi' => 'administrasi', 'status_id' => $this->statusId('sanksi')]);

        $html = $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder(['Tahapan Kasus', 'Terbuka', 'Dipublikasikan', 'Sanksi', 'Fakta Kasus'])
            ->assertSee('Pemerintah menjatuhkan sanksi administratif')
            ->assertDontSee('Penyidik resmi mengusut tersangka')
            ->assertDontSee('Para pihak mengajukan bukti')
            ->getContent();
        $this->assertMatchesRegularExpression('/font-bold">\s*Sanksi\s*</', $html);
    }

    public function test_pidana_case_keeps_the_criminal_stages(): void
    {
        $case = $this->publicCase(['kualifikasi' => 'pidana']);

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder(['Tahapan Kasus', 'Terbuka', 'Dipublikasikan', 'Penyelidikan', 'Penyidikan', 'Penuntutan', 'Persidangan', 'Vonis', 'Fakta Kasus'])
            ->assertDontSee('Para pihak mengajukan bukti');
    }

    public function test_case_form_saves_custom_stage_notes_per_language(): void
    {
        $case = $this->publicCase(['kualifikasi' => 'perdata']);
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(CaseForm::class, ['caseId' => $case->id])
            ->assertSee('Keterangan Tahapan')
            ->assertSee('Pembuktian')
            ->set('tahapan_id.gugatan', "  Gugatan didaftarkan 12 Januari 2026 di PN Jakarta Pusat.\nNomor perkara 123/Pdt.G/2026.  ")
            ->set('tahapan_id.pembuktian', '   ')
            ->set('tahapan_en.gugatan', 'The lawsuit was filed on 12 January 2026.')
            ->call('save')
            ->assertHasNoErrors();

        $notes = $case->translations()->pluck('keterangan_tahapan', 'locale');
        // Isian kosong tidak disimpan, spasi di tepi dibuang.
        $this->assertSame(['gugatan' => "Gugatan didaftarkan 12 Januari 2026 di PN Jakarta Pusat.\nNomor perkara 123/Pdt.G/2026."], $notes['id']);
        $this->assertSame(['gugatan' => 'The lawsuit was filed on 12 January 2026.'], $notes['en']);

        Livewire::actingAs($user)->test(CaseForm::class, ['caseId' => $case->id])
            ->assertSet('tahapan_id.gugatan', "Gugatan didaftarkan 12 Januari 2026 di PN Jakarta Pusat.\nNomor perkara 123/Pdt.G/2026.")
            ->set('tahapan_id.gugatan', str_repeat('Keterangan panjang. ', 75))
            ->call('save')
            ->assertHasNoErrors();

        // Keterangan tahapan tidak dibatasi 500 karakter lagi.
        $this->assertSame(trim(str_repeat('Keterangan panjang. ', 75)),
            $case->translations()->where('locale', 'id')->first()->keterangan_tahapan['gugatan']);
    }

    public function test_stage_notes_follow_the_selected_kualifikasi_in_the_form(): void
    {
        $case = $this->publicCase();
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(CaseForm::class, ['caseId' => $case->id])
            ->assertSee('Penyidikan')
            ->set('kualifikasi', 'administrasi')
            ->assertSee('Sanksi')
            ->assertDontSee('Penyidikan');
    }

    public function test_public_detail_shows_custom_stage_notes_and_defaults_for_the_rest(): void
    {
        $case = $this->publicCase(['kualifikasi' => 'perdata', 'status_id' => $this->statusId('gugatan')]);
        $case->translations()->where('locale', 'id')->update(['keterangan_tahapan' => json_encode([
            'gugatan' => "Gugatan didaftarkan 12 Januari 2026.\nNomor perkara <123>/Pdt.G/2026.",
        ])]);

        $this->get($case->publicUrl('id'))->assertOk()
            ->assertSeeInOrder([
                'Gugatan', 'Gugatan didaftarkan 12 Januari 2026.<br />',
                'Pembuktian', 'Para pihak mengajukan bukti, saksi, dan ahli di persidangan untuk menguatkan dalil masing-masing.',
            ], false)
            // Teks admin di-escape.
            ->assertSee('Nomor perkara &lt;123&gt;/Pdt.G/2026.', false)
            ->assertDontSee('Gugatan perdata didaftarkan ke pengadilan oleh pihak yang dirugikan');

        // Halaman EN tidak memakai keterangan berbahasa Indonesia; tanpa versi EN tampil keterangan standar.
        $this->get($case->publicUrl('en'))->assertOk()
            ->assertDontSee('Gugatan didaftarkan 12 Januari 2026')
            ->assertSee('A civil lawsuit is filed in court by the injured party');
    }

    public function test_auto_translate_fills_english_stage_notes(): void
    {
        config(['services.deepl.key' => 'test-key:fx', 'services.deepl.target_lang' => 'EN-US']);
        \Illuminate\Support\Facades\Http::fake([
            '*/v2/translate' => fn ($request) => \Illuminate\Support\Facades\Http::response([
                'translations' => array_map(fn ($text) => ['detected_source_language' => 'ID', 'text' => '[EN] '.$text], $request['text']),
            ]),
        ]);
        $case = $this->publicCase(['kualifikasi' => 'perdata']);

        Livewire::actingAs(User::factory()->create())->test(CaseForm::class, ['caseId' => $case->id])
            ->set('tahapan_id.gugatan', 'Gugatan didaftarkan.')
            ->set('tahapan_id.vonis', '')
            ->call('autoTranslate')
            ->assertSet('tahapan_en.gugatan', '[EN] Gugatan didaftarkan.')
            ->assertSet('title_en', '[EN] Tambang Ilegal di Hutan Lindung')
            ->assertNotSet('tahapan_en.vonis', '[EN] ');
    }

    public function test_case_card_links_to_the_clean_url_and_clamps_the_description(): void
    {
        $case = $this->publicCase();

        $this->get('/id/verified-cases')->assertOk()
            ->assertSee('href="'.$case->publicUrl('id').'"', false)
            ->assertSee('-webkit-line-clamp:3', false)
            // Batas paragraf diganti spasi agar kalimat tidak menempel.
            ->assertSee('Paragraf pertama. Paragraf kedua.');
    }

    /** Status gugatan, pembuktian, dan sanksi diisi migration. */
    private function statusId(string $key): int
    {
        return DB::table('statuses')->where('key', $key)->value('id');
    }

    private function publicCase(array $attributes = []): CaseModel
    {
        $case = CaseModel::create($attributes + [
            'case_number' => 'CASE-KUALF',
            'status_id' => $this->statusId,
            'category_ids' => [$this->categoryId],
            'event_date' => '2026-03-01',
            'latitude' => -0.5022,
            'longitude' => 117.1536,
            'is_public' => true,
            'published_at' => now(),
        ]);
        $case->translations()->create(['locale' => 'id', 'title' => 'Tambang Ilegal di Hutan Lindung', 'description' => '<p>Paragraf pertama.</p><p>Paragraf kedua.</p>']);
        $case->translations()->create(['locale' => 'en', 'title' => 'Illegal Mining in a Protected Forest', 'description' => '<p>First paragraph.</p>']);

        return $case;
    }
}
