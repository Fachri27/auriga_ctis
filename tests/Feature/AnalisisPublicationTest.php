<?php

namespace Tests\Feature;

use App\Livewire\Artikels\ArtikelForm;
use App\Models\{Artikel, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnalisisPublicationTest extends TestCase
{
    use RefreshDatabase;

    private function analisis(array $attributes = []): Artikel
    {
        $artikel = Artikel::create($attributes + [
            'slug' => 'enhancing-the-capacity-of-ppns',
            'type' => 'internal',
            'status' => 'active',
            'published_at' => '2026-11-01',
            'image' => 'pages/cover.jpg',
            'link' => 'https://gakkum-sda.id/id/publikasi/detail/enhancing-the-capacity',
            'pdf_id' => 'publikasi/laporan-id.pdf',
            'pdf_en' => 'publikasi/laporan-en.pdf',
        ]);
        foreach (['id' => 'Situasi pandemi juga tak terhindarkan oleh tim proyek ini.', 'en' => 'The pandemic also affected this project team.'] as $locale => $text) {
            $artikel->translation()->create([
                'locale' => $locale,
                'title' => 'Enhancing the Capacity of Law Enforcement Agencies',
                'excerpt' => 'Ringkasan.',
                'content' => "<p>{$text}</p>",
            ]);
        }

        return $artikel;
    }

    public function test_analysis_page_shows_cover_description_link_and_pdf_downloads(): void
    {
        $this->analisis();

        $this->get('/id/artikel/enhancing-the-capacity-of-ppns')->assertOk()
            ->assertSee(asset('storage/pages/cover.jpg'), false)
            ->assertSeeInOrder(['Unduh PDF', 'Indonesia', 'English', 'Enhancing the Capacity of Law Enforcement Agencies', 'Situasi pandemi juga tak terhindarkan', 'https://gakkum-sda.id/id/publikasi/detail/enhancing-the-capacity'])
            ->assertSee('href="'.asset('storage/publikasi/laporan-id.pdf').'"', false)
            ->assertSee('href="'.asset('storage/publikasi/laporan-en.pdf').'"', false);

        $this->get('/en/artikel/enhancing-the-capacity-of-ppns')->assertOk()
            ->assertSee('Download PDF')
            ->assertSee('The pandemic also affected this project team.');
    }

    public function test_downloads_and_link_are_hidden_when_empty_or_unsafe(): void
    {
        $this->analisis(['pdf_id' => null, 'pdf_en' => 'publikasi/laporan-en.pdf', 'link' => 'javascript:alert(1)', 'image' => null]);

        $this->get('/id/artikel/enhancing-the-capacity-of-ppns')->assertOk()
            ->assertSee('Unduh PDF')
            ->assertDontSee('laporan-id.pdf')
            ->assertSee('laporan-en.pdf')
            ->assertDontSee('javascript:alert(1)');

        Artikel::query()->update(['pdf_en' => null]);
        $this->get('/id/artikel/enhancing-the-capacity-of-ppns')->assertOk()->assertDontSee('Unduh PDF');
    }

    public function test_unknown_analysis_is_not_found(): void
    {
        $this->get('/id/artikel/tidak-ada')->assertNotFound();
    }

    public function test_demo_seeder_creates_an_analysis_with_cover_and_pdfs(): void
    {
        Storage::fake('public');

        $this->seed(\Database\Seeders\AnalisisDemoSeeder::class);
        $this->seed(\Database\Seeders\AnalisisDemoSeeder::class);

        // Dijalankan ulang tidak menambah artikel baru.
        $this->assertSame(1, Artikel::count());
        $artikel = Artikel::first();
        $disk = Storage::disk('public');
        $this->assertStringStartsWith('%PDF-1.4', $disk->get($artikel->pdf_id));
        $this->assertStringEndsWith("%%EOF\n", $disk->get($artikel->pdf_en));
        $this->assertSame(IMAGETYPE_JPEG, getimagesizefromstring($disk->get($artikel->image))[2]);

        $this->get('/id/artikel/'.$artikel->slug)->assertOk()
            ->assertSeeInOrder(['Unduh PDF', 'Indonesia', 'English', '[Contoh] Enhancing the Capacity', 'Situasi pandemi', 'gakkum-sda.id']);
        $this->get('/en/artikel/'.$artikel->slug)->assertOk()->assertSee('The pandemic also affected this project team');
    }

    public function test_cms_form_uploads_replaces_and_removes_pdfs(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $artikel = $this->analisis(['pdf_id' => null, 'pdf_en' => null]);
        $form = fn () => Livewire::actingAs($user)->test(ArtikelForm::class, ['artikelId' => $artikel->id]);

        $form()->set('pdf_id', UploadedFile::fake()->create('laporan.pdf', 2048, 'application/pdf'))
            ->set('pdf_en', UploadedFile::fake()->create('report.pdf', 2048, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();
        $artikel->refresh();
        Storage::disk('public')->assertExists([$artikel->pdf_id, $artikel->pdf_en]);
        $this->assertMatchesRegularExpression('#^publikasi/enhancing-the-capacity-of-law-enforcement-agencies-id-[a-z0-9]{8}\.pdf$#', $artikel->pdf_id);
        $this->assertMatchesRegularExpression('#^publikasi/enhancing-the-capacity-of-law-enforcement-agencies-en-[a-z0-9]{8}\.pdf$#', $artikel->pdf_en);

        // Ganti PDF Indonesia: berkas lama dihapus. Hapus PDF English.
        $oldId = $artikel->pdf_id;
        $oldEn = $artikel->pdf_en;
        $form()->set('pdf_id', UploadedFile::fake()->create('laporan-baru.pdf', 1024, 'application/pdf'))
            ->set('removePdf.en', true)
            ->call('save')
            ->assertHasNoErrors();
        $artikel->refresh();
        Storage::disk('public')->assertMissing([$oldId, $oldEn]);
        Storage::disk('public')->assertExists($artikel->pdf_id);
        $this->assertNull($artikel->pdf_en);
    }

    public function test_cms_form_rejects_non_pdf_files_and_invalid_links(): void
    {
        Storage::fake('public');
        $artikel = $this->analisis(['pdf_id' => null, 'pdf_en' => null]);

        Livewire::actingAs(User::factory()->create())->test(ArtikelForm::class, ['artikelId' => $artikel->id])
            ->set('pdf_id', UploadedFile::fake()->image('bukan-pdf.jpg'))
            ->set('link', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['pdf_id' => 'mimes', 'link' => 'url']);
        $this->assertNull($artikel->fresh()->pdf_id);
    }
}
