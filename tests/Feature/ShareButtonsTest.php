<?php

namespace Tests\Feature;

use App\Models\Artikel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareButtonsTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_page_shares_its_clean_url(): void
    {
        $artikel = Artikel::create(['slug' => 'efek-jera-penegakan-hukum', 'type' => 'internal', 'status' => 'active', 'published_at' => now()]);
        foreach (['id', 'en'] as $locale) {
            $artikel->translation()->create(['locale' => $locale, 'title' => 'Efek Jera Penegakan Hukum', 'excerpt' => 'Ringkasan.', 'content' => '<p>Isi</p>']);
        }
        $clean = route('public.artikel.detail', ['locale' => 'id', 'slug' => 'efek-jera-penegakan-hukum']);

        $html = $this->get('/id/artikel/efek-jera-penegakan-hukum?fbclid=IwAR3abc&utm_source=facebook')
            ->assertOk()
            ->assertSee('Bagikan artikel ini')
            ->assertSee('https://wa.me/?text=Efek+Jera+Penegakan+Hukum%20'.urlencode($clean), false)
            ->assertSee('https://www.facebook.com/sharer/sharer.php?u='.urlencode($clean), false)
            ->assertSee('data-url="'.$clean.'"', false)
            ->assertSee('aria-label="Bagikan ke Instagram"', false)
            ->assertDontSee('IwAR3abc')
            ->getContent();

        // Dua deret tombol (di bawah judul & di akhir artikel), skripnya cukup sekali.
        $this->assertSame(2, substr_count($html, 'data-share-instagram aria-label'));
        $this->assertSame(1, substr_count($html, "closest('[data-share]')"));
    }
}
