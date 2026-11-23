<?php

namespace Tests\Feature;

use App\Models\{AboutPage, AboutPageTranslation};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_shows_document_text_in_both_languages(): void
    {
        $this->get('/id/about')->assertOk()->assertSeeInOrder([
            'Tentang greendefender',
            'Memantau perkara, mengungkap pola, dan mengawal penegakan hukum.',
            'greendefender merupakan platform informasi dan data',
            'Tujuan', 'Komitmen Kami',
            'Apa yang Kami Pantau?', 'Kejahatan tumbuhan dan satwa liar',
            'Bagaimana Kami Bekerja?', 'Publikasi data dan informasi',
            'Siapa Penerima Manfaat?', 'Pemerintah dan pembuat kebijakan',
            'Terminologi', 'Sumber dan Metode', 'Batasan Pemantauan', 'Artikel dan Berita', 'Disclaimer',
        ]);

        $this->get('/en/about')->assertOk()->assertSeeInOrder([
            'About greendefender', 'Purpose', 'Our Commitment', 'What Do We Monitor?', 'How Do We Work?',
            'Who Benefits?', 'Terminology', 'Sources and Methods', 'Monitoring Limitations', 'Disclaimer',
        ])->assertDontSee('Komitmen Kami');
    }

    public function test_cms_text_replaces_intro_purpose_and_commitment(): void
    {
        $about = AboutPage::create();
        AboutPageTranslation::create([
            'about_page_id' => $about->id, 'locale' => 'id', 'title' => 'Tentang',
            'content' => '<p>Intro dari CMS</p>', 'vision' => '<p>Tujuan dari CMS</p>', 'mission' => '<p></p>',
        ]);

        $this->get('/id/about')->assertOk()
            ->assertSee('Intro dari CMS')
            ->assertDontSee('greendefender merupakan platform informasi dan data')
            ->assertSeeInOrder(['Tujuan', 'Tujuan dari CMS', 'Komitmen Kami', 'greendefender berkomitmen untuk menyajikan informasi']);
    }
}
