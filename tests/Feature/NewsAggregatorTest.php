<?php

namespace Tests\Feature;

use App\Livewire\Artikels\ArtikelTable;
use App\Models\Artikel;
use App\Models\User;
use App\Services\News\{NewsAggregator, NewsEnricher};
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Livewire\Livewire;
use Tests\TestCase;

class NewsAggregatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sleep::fake();

        config([
            'news.search_queries' => ['"penegakan hukum" lingkungan'],
            'news.search_engines' => ['google'],
            'news.rss_feeds' => ['Mongabay Indonesia' => 'https://mongabay.co.id/feed/'],
        ]);

        $id = DB::table('categories')->insertGetId(['slug' => 'pertambangan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('category_translations')->insert(['category_id' => $id, 'locale' => 'id', 'name' => 'Pertambangan', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function rss(array $items): string
    {
        $xml = '';
        foreach ($items as $i) {
            $xml .= '<item><title>'.htmlspecialchars($i['title']).'</title><link>'.htmlspecialchars($i['link']).'</link>'
                .'<guid>'.htmlspecialchars($i['guid'] ?? $i['link']).'</guid><pubDate>'.($i['date'] ?? now()->subHours(2)->toRfc2822String()).'</pubDate>'
                .(isset($i['source']) ? '<source url="https://media.test">'.$i['source'].'</source>' : '')
                .(isset($i['image']) ? '<enclosure url="'.$i['image'].'" type="image/jpeg" length="0"/>' : '')
                .'<description><![CDATA['.($i['description'] ?? '').']]></description></item>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Feed</title>'.$xml.'</channel></rss>';
    }

    private function fakeFeeds(): void
    {
        Http::fake([
            'www.bing.com/*' => Http::response($this->rss([])),
            'news.google.com/*' => Http::response($this->rss([
                // Relevan: penegakan hukum + tambang. Akhiran " - disway.id - Disway" dibuang.
                ['title' => 'Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel - disway.id - Disway', 'link' => 'https://news.google.com/rss/articles/AAA?oc=5', 'guid' => 'AAA', 'source' => 'Disway'],
                // Peristiwa yang sama dari media lain → dilewati.
                ['title' => 'PT BSJ Tersangka Korupsi Tambang Nikel di Sultra - Tempo.co', 'link' => 'https://news.google.com/rss/articles/BBB?oc=5', 'guid' => 'BBB', 'source' => 'Tempo.co'],
                // Tidak ada kata penegakan hukum → dilewati.
                ['title' => 'Harga Nikel Dunia Naik Tajam - Kontan', 'link' => 'https://news.google.com/rss/articles/CCC?oc=5', 'guid' => 'CCC', 'source' => 'Kontan'],
            ])),
            'mongabay.co.id/*' => Http::response($this->rss([
                ['title' => 'Polda Kalteng Tetapkan Korporasi Tersangka Karhutla', 'link' => 'https://mongabay.co.id/2026/11/10/karhutla/', 'description' => '<p>Penyidik menetapkan dua perusahaan sebagai <b>tersangka</b> kebakaran lahan gambut.</p>', 'image' => 'https://img.mongabay.test/karhutla.jpg'],
                ['title' => 'Menakar PLTS Terapung di Malang', 'link' => 'https://mongabay.co.id/2026/11/10/plts/', 'description' => '<p>Energi terbarukan.</p>'],
            ])),
        ]);
    }

    public function test_relevant_news_is_published_as_external_articles(): void
    {
        $this->fakeFeeds();

        $result = app(NewsAggregator::class)->pull();

        $this->assertSame(['fetched' => 5, 'relevant' => 3, 'created' => 2, 'upgraded' => 0, 'failed_sources' => []], $result);

        $nikel = Artikel::where('source_name', 'Disway')->with('translation')->first();
        $this->assertSame('eksternal', $nikel->type);
        $this->assertSame('active', $nikel->status);
        $this->assertSame('https://news.google.com/rss/articles/AAA?oc=5', $nikel->link);
        $this->assertSame('Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel', $nikel->translation->firstWhere('locale', 'id')->title);
        $this->assertSame('Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel', $nikel->translation->firstWhere('locale', 'en')->title);
        $this->assertSame(DB::table('categories')->where('slug', 'pertambangan')->value('id'), $nikel->category_id);

        $karhutla = Artikel::where('source_name', 'Mongabay Indonesia')->with('translation')->first();
        $this->assertSame('Penyidik menetapkan dua perusahaan sebagai tersangka kebakaran lahan gambut.', $karhutla->translation->first()->excerpt);
        $this->assertSame('https://img.mongabay.test/karhutla.jpg', $karhutla->image);
        $this->assertNull($nikel->image);

        // Penarikan berikutnya tidak menggandakan berita.
        $this->assertSame(0, app(NewsAggregator::class)->pull()['created']);
        $this->assertSame(2, Artikel::count());
    }

    public function test_google_news_story_is_replaced_by_media_version_with_image(): void
    {
        $google = $this->rss([['title' => 'Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel - Disway', 'link' => 'https://news.google.com/rss/articles/AAA?oc=5', 'guid' => 'AAA', 'source' => 'Disway']]);
        $media = $this->rss([['title' => 'Kejagung Tetapkan PT BSJ Tersangka Korporasi Korupsi Tambang Nikel', 'link' => 'https://www.cnnindonesia.com/nasional/bsj',
            'description' => '<img src="https://akcdn.test/bsj.jpg"> Kejaksaan Agung menetapkan PT BSJ sebagai tersangka korporasi.']]);
        config(['news.rss_feeds' => ['CNN Indonesia' => 'https://www.cnnindonesia.com/nasional/rss']]);

        // Tarikan pertama: hanya versi Google News (tanpa gambar). Tarikan berikutnya: media menerbitkan versi bergambar.
        Http::fake([
            'news.google.com/*' => Http::response($google),
            'www.cnnindonesia.com/*' => Http::sequence()->push($this->rss([]))->push($media),
        ]);
        app(NewsAggregator::class)->pull();

        // Berita yang sama diperbarui dengan gambar, tidak digandakan.
        $result = app(NewsAggregator::class)->pull();

        $this->assertSame([0, 1], [$result['created'], $result['upgraded']]);
        $artikel = Artikel::with('translation')->sole();
        $this->assertSame('https://akcdn.test/bsj.jpg', $artikel->image);
        $this->assertSame('https://www.cnnindonesia.com/nasional/bsj', $artikel->link);
        $this->assertSame('CNN Indonesia', $artikel->source_name);
        $this->assertSame('Kejaksaan Agung menetapkan PT BSJ sebagai tersangka korporasi.', $artikel->translation->firstWhere('locale', 'id')->excerpt);
    }

    public function test_only_indonesian_news_from_indonesian_media_is_kept(): void
    {
        config(['news.rss_feeds' => []]);
        Http::fake(['news.google.com/*' => Http::response($this->rss([
            ['title' => 'KPK Tetapkan Bupati Tersangka Korupsi Izin Tambang - Kompas.com', 'link' => 'https://news.google.com/rss/articles/ID1', 'source' => 'Kompas.com'],
            ['title' => 'Indonesia arrests palm oil executives over forest fires - Reuters', 'link' => 'https://news.google.com/rss/articles/EN1', 'source' => 'Reuters'],
            ['title' => 'إندونيسيا: اعتقال مسؤولين بتهمة تهريب الأخشاب من الغابات - الجزيرة', 'link' => 'https://news.google.com/rss/articles/AR1', 'source' => 'الجزيرة'],
            // Berbahasa Indonesia, tetapi disalin situs luar bernama huruf Arab.
            ['title' => 'Terdampak Karhutla, Jalan Hukum bagi Masyarakat untuk Menggugat Negara - شبكة تواصل', 'link' => 'https://news.google.com/rss/articles/AR2', 'source' => 'شبكة تواصل الإخبارية'],
        ]))]);

        $result = app(NewsAggregator::class)->pull();

        $this->assertSame([1, 1], [$result['relevant'], $result['created']]);
        $this->assertSame(['Kompas.com'], Artikel::pluck('source_name')->all());
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: string}>  $items  judul, tautan asli, media, gambar
     */
    private function bingRss(array $items): string
    {
        $xml = '';
        foreach ($items as [$title, $url, $source, $image]) {
            $xml .= '<item><title>'.$title.'</title>'
                .'<link>http://www.bing.com/news/apiclick.aspx?ref=FexRss&amp;aid=&amp;tid=abc&amp;url='.urlencode($url).'&amp;c=1&amp;mkt=id-id</link>'
                .'<description>Ringkasan '.$title.'.</description><pubDate>'.now()->subHour()->toRfc2822String().'</pubDate>'
                .'<News:Source>'.$source.'</News:Source><News:Image>'.$image.'</News:Image></item>';
        }

        return '<?xml version="1.0" encoding="utf-8"?><rss xmlns:News="https://www.bing.com:443/news/search?q=x&amp;format=rss" version="2.0">'
            .'<channel><title>Bing</title>'.$xml.'</channel></rss>';
    }

    private function externalArtikel(string $link, ?string $image = null): Artikel
    {
        $artikel = Artikel::create(['slug' => 'berita-'.sha1($link), 'type' => 'eksternal', 'status' => 'active', 'published_at' => now(),
            'link' => $link, 'image' => $image, 'source_name' => 'Media', 'external_id' => 'x:'.sha1($link)]);
        foreach (['id', 'en'] as $locale) {
            $artikel->translation()->create(['locale' => $locale, 'title' => 'Judul', 'excerpt' => '', 'content' => null]);
        }

        return $artikel;
    }

    public function test_news_without_image_gets_preview_from_original_article(): void
    {
        $google = $this->externalArtikel('https://news.google.com/rss/articles/CBMiAAA?oc=5');
        $blocked = $this->externalArtikel('https://tribun.test/berita/1');
        $withImage = $this->externalArtikel('https://mongabay.co.id/2026/11/10/karhutla/', 'https://img.mongabay.test/karhutla.jpg');

        Http::fake([
            'www.bing.com/*' => Http::response($this->rss([])),
            'news.google.com/articles/CBMiAAA' => Http::response('<c-wiz><div jscontroller="x" data-n-a-sg="SIG123" data-n-a-ts="1794328163"></div></c-wiz>'),
            'news.google.com/_/DotsSplashUi/*' => Http::response(")]}'\n\n".json_encode([
                ['wrb.fr', 'Fbv4je', json_encode(['garturlres', 'https://disway.test/read/1/nikel', 1]), null, null, null, 'generic'],
                ['di', 42],
            ])),
            // Atribut content sebelum property, dan entitas HTML di ringkasan.
            'disway.test/*' => Http::response('<html><head><meta content="https://cdn.disway.test/foto.jpg" property="og:image">'
                .'<meta property="og:description" content="Kejagung memeriksa 38 saksi &amp; menyita   dokumen."></head><body></body></html>'),
            'tribun.test/*' => Http::response('Forbidden', 403),
        ]);

        $this->assertSame(['checked' => 2, 'images' => 1, 'throttled' => false], app(NewsEnricher::class)->enrich());

        Http::assertSent(fn ($request) => str_contains($request->url(), 'batchexecute') && str_contains($request['f.req'], 'SIG123'));

        $google->refresh();
        $this->assertSame('https://disway.test/read/1/nikel', $google->link);
        $this->assertSame('https://cdn.disway.test/foto.jpg', $google->image);
        $this->assertSame(['Kejagung memeriksa 38 saksi & menyita dokumen.'], $google->translation()->pluck('excerpt')->unique()->values()->all());

        // Berita Google dicari dulu di Bing; situs yang menolak bot juga dicari di Bing.
        $this->assertSame(2, Http::recorded(fn ($request) => str_contains($request->url(), 'www.bing.com/news/search'))->count());

        // Situs yang menolak (dan tidak ada di Bing): tidak dicoba lagi, tetap tampil dengan gambar cadangan.
        $this->assertNotNull($blocked->fresh()->enriched_at);
        $this->assertNull($blocked->fresh()->image);
        $this->assertNull($withImage->fresh()->enriched_at);

        $this->assertSame(0, app(NewsEnricher::class)->enrich()['checked']);
    }

    public function test_hero_image_is_found_without_og_image(): void
    {
        $quoted = $this->externalArtikel('https://a.test/berita');
        $amp = $this->externalArtikel('https://b.test/amp/berita');
        $lazy = $this->externalArtikel('https://c.test/berita');

        Http::fake([
            // Nama berkas bertanda kutip lengkung.
            'a.test/*' => Http::response('<head><meta property="og:image" content="https://a.test/img/Poster_“HGU”.webp"></head>'),
            // Halaman AMP: tanpa og:image, gambar di JSON-LD (rujukan @id ala Yoast); meta description berisi slogan situs.
            'b.test/*' => Http::response('<head><title>93 Korporasi Masuk Penyidikan Kasus Karhutla</title>'
                .'<meta name="description" content="Portal berita dan video terkini hari ini seputar peristiwa nasional.">'
                .'<script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@graph' => [
                    ['@type' => 'Organization', '@id' => '#org', 'image' => 'https://b.test/logo.png'],
                    ['@type' => 'NewsArticle', 'image' => ['@id' => '#primaryimage'], 'description' => 'Sebanyak 93 korporasi masuk proses hukum karhutla.'],
                    ['@type' => 'ImageObject', '@id' => '#primaryimage', 'url' => 'https://b.test/foto/karhutla.webp'],
                ]]).'</script></head>'),
            // Tanpa meta gambar: gambar lazy-load pertama setelah judul, bukan logo di atasnya.
            'c.test/*' => Http::response('<head><title>Kritik Yusril soal Tambang Ilegal dan Pencurian Ikan</title>'
                .'<meta name="description" content="Portal berita dan video terkini hari ini seputar peristiwa nasional."></head>'
                .'<body><img src="https://c.test/logo.png"><h1>Kritik Yusril</h1>'
                .'<img src="data:image/svg+xml;base64,AAA" data-src="https://c.test/uploads/foto-696x464.png" width="696" height="464"></body>'),
        ]);

        $this->assertSame(['checked' => 3, 'images' => 3, 'throttled' => false], app(NewsEnricher::class)->enrich());

        $this->assertSame('https://a.test/img/Poster_%E2%80%9CHGU%E2%80%9D.webp', $quoted->fresh()->image);
        $this->assertSame('https://b.test/foto/karhutla.webp', $amp->fresh()->image);
        $this->assertSame('Sebanyak 93 korporasi masuk proses hukum karhutla.', $amp->translation()->value('excerpt'));
        $this->assertSame('https://c.test/uploads/foto-696x464.png', $lazy->fresh()->image);
        // Slogan situs tidak dipakai sebagai ringkasan.
        $this->assertSame('', $lazy->translation()->value('excerpt'));
    }

    public function test_enrichment_stops_and_retries_later_when_google_throttles(): void
    {
        $google = $this->externalArtikel('https://news.google.com/rss/articles/CBMiAAA?oc=5');
        Http::fake([
            'www.bing.com/*' => Http::response($this->rss([])),
            'news.google.com/*' => Http::response('Too Many Requests', 429),
        ]);
        $googleRequests = fn () => Http::recorded(fn ($request) => str_contains($request->url(), 'news.google.com'))->count();

        $this->assertSame(['checked' => 0, 'images' => 0, 'throttled' => true], app(NewsEnricher::class)->enrich());
        $this->assertSame('https://news.google.com/rss/articles/CBMiAAA?oc=5', $google->fresh()->link);

        // Tidak langsung diulang (agar Google tidak makin membatasi), tetapi dicoba lagi beberapa jam kemudian.
        app(NewsEnricher::class)->enrich();
        $this->assertSame(1, $googleRequests());

        $this->travel(7)->hours();
        app(NewsEnricher::class)->enrich();
        $this->assertSame(2, $googleRequests());
    }

    public function test_bing_news_gives_direct_links_and_images_and_replaces_google_version(): void
    {
        config(['news.search_engines' => ['bing', 'google'], 'news.rss_feeds' => []]);

        // Tarikan sebelumnya: versi Google News tanpa gambar.
        $old = $this->externalArtikel('https://news.google.com/rss/articles/OLD?oc=5');
        $old->translation()->update(['title' => 'Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel']);

        Http::fake([
            'www.bing.com/*' => Http::response($this->bingRss([
                ['PT BSJ Tersangka Korupsi Tambang Nikel, Kejagung Periksa Direksi', 'https://www.biem.co/read/2026/11/10/bsj', 'biem.co', 'http://www.bing.com/th?id=ONUT.abc&amp;pid=News'],
                ['Polda Kalteng Tetapkan Korporasi Tersangka Karhutla', 'https://kalteng.test/karhutla', 'Kalteng Pos', 'http://www.bing.com/th?id=ONUT.def&amp;pid=News'],
            ])),
            'news.google.com/*' => Http::response($this->rss([
                ['title' => 'Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel - Disway', 'link' => 'https://news.google.com/rss/articles/NEW?oc=5', 'guid' => 'NEW', 'source' => 'Disway'],
            ])),
        ]);

        $result = app(NewsAggregator::class)->pull();

        $this->assertSame([1, 1], [$result['created'], $result['upgraded']]);
        $this->assertSame(2, Artikel::count());

        // Berita Google lama kini bergambar dan bertautan langsung ke berita aslinya.
        $old->refresh();
        $this->assertSame('https://www.biem.co/read/2026/11/10/bsj', $old->link);
        $this->assertSame('https://www.bing.com/th?id=ONUT.abc&pid=News', $old->image);
        $this->assertSame('biem.co', $old->source_name);

        $karhutla = Artikel::where('source_name', 'Kalteng Pos')->sole();
        $this->assertSame('https://kalteng.test/karhutla', $karhutla->link);
        $this->assertSame('https://www.bing.com/th?id=ONUT.def&pid=News', $karhutla->image);
    }

    public function test_google_news_without_image_is_found_on_bing_by_title(): void
    {
        $bareskrim = $this->externalArtikel('https://news.google.com/rss/articles/A1?oc=5');
        $bareskrim->translation()->update(['title' => 'Bareskrim Polri Tetapkan 245 Tersangka Perorangan dan 20 Korporasi dalam Kasus Karhutla']);
        $kalteng = $this->externalArtikel('https://news.google.com/rss/articles/A2?oc=5');
        $kalteng->translation()->update(['title' => 'Polda Kalteng Tetapkan 52 Tersangka Kasus Karhutla']);
        $duplikat = $this->externalArtikel('https://news.google.com/rss/articles/A3?oc=5');
        $duplikat->translation()->update(['title' => 'Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel']);
        $tampil = $this->externalArtikel('https://www.biem.co/read/bsj', 'https://img.test/bsj.jpg');

        Http::fake([
            'www.bing.com/*' => Http::sequence()
                // Media lain + media yang sama: yang dari media sama diutamakan.
                ->push($this->bingRss([
                    ['Kasus Karhutla, Bareskrim Polri Tetapkan 245 Tersangka Perorangan dan 20 Korporasi', 'https://niaga.test/karhutla', 'Niaga.Asia', 'http://www.bing.com/th?id=ONUT.n'],
                    ['Bareskrim Tetapkan 245 Tersangka Perorangan dan 20 Korporasi Kasus Karhutla', 'https://media.test/bareskrim', 'Media', 'http://www.bing.com/th?id=ONUT.m'],
                ]))
                // Mirip, tetapi jumlah tersangkanya lain: berita yang berbeda, tidak dipakai.
                ->push($this->bingRss([['Berpotensi Bertambah, Polda Kalteng Sudah Tetapkan 48 Tersangka Kasus Karhutla', 'https://konteks.test/48', 'Konteks', 'http://www.bing.com/th?id=ONUT.k']]))
                // Artikel temuan sudah tampil sebagai kartu lain.
                ->push($this->bingRss([['Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel', 'https://www.biem.co/read/bsj', 'biem.co', 'http://www.bing.com/th?id=ONUT.b']])),
            'news.google.com/*' => Http::response('Too Many Requests', 429),
        ]);

        $this->assertSame(['checked' => 2, 'images' => 1, 'throttled' => true], app(NewsEnricher::class)->enrich());

        $bareskrim->refresh();
        $this->assertSame('https://media.test/bareskrim', $bareskrim->link);
        $this->assertSame('https://www.bing.com/th?id=ONUT.m', $bareskrim->image);
        $this->assertSame('Bareskrim Tetapkan 245 Tersangka Perorangan dan 20 Korporasi Kasus Karhutla', $bareskrim->translation()->value('title'));

        $this->assertNull($kalteng->fresh()->image);
        $this->assertSame('https://news.google.com/rss/articles/A2?oc=5', $kalteng->fresh()->link);

        $this->assertNull($duplikat->fresh());
        $this->assertNotNull($tampil->fresh());
    }

    public function test_unreachable_source_is_reported(): void
    {
        Http::fake([
            'news.google.com/*' => Http::response('Service Unavailable', 503),
            'mongabay.co.id/*' => Http::response($this->rss([])),
        ]);

        $this->assertSame(['Google News'], app(NewsAggregator::class)->pull()['failed_sources']);
    }

    public function test_public_page_shows_analysis_and_news_in_one_list(): void
    {
        $this->fakeFeeds();
        app(NewsAggregator::class)->pull();

        $analisis = Artikel::create(['slug' => 'kajian-penegakan-hukum', 'type' => 'internal', 'status' => 'active', 'published_at' => now()]);
        foreach (['id', 'en'] as $locale) {
            $analisis->translation()->create(['locale' => $locale, 'title' => 'Kajian Auriga: Efek Jera Penegakan Hukum', 'excerpt' => 'Ringkasan kajian.', 'content' => '<p>Isi</p>']);
        }

        // Satu daftar, terbaru dulu: analisis (baru terbit) lalu berita (2 jam lalu).
        $this->get('/id/artikels')
            ->assertOk()
            ->assertSeeInOrder(['Kajian Auriga: Efek Jera Penegakan Hukum', 'Polda Kalteng Tetapkan Korporasi Tersangka Karhutla'])
            ->assertSee('src="https://img.mongabay.test/karhutla.jpg"', false)
            ->assertDontSee('Berita Penegakan Hukum Lingkungan')
            // Berita tanpa gambar (Google News) belum tampil sampai gambarnya ditemukan.
            ->assertDontSee('Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel');

        $this->get('/id/artikels?jenis=analisis')->assertSee('Kajian Auriga: Efek Jera Penegakan Hukum')->assertDontSee('Polda Kalteng Tetapkan');
        $this->get('/id/artikels?jenis=berita')->assertSee('Polda Kalteng Tetapkan')->assertDontSee('Kajian Auriga: Efek Jera');

        // Bila diizinkan tampil tanpa gambar: gambar cadangan, dan deskripsi singkat dari nama medianya.
        config(['news.require_image' => false]);
        $this->get('/id/artikels')
            ->assertSee('Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel')
            ->assertSee('Diberitakan oleh Disway');

        // Detail berita eksternal diarahkan ke berita aslinya; sitemap hanya memuat analisis.
        $berita = Artikel::where('source_name', 'Disway')->first();
        $this->get("/id/artikel/{$berita->slug}")->assertRedirect('https://news.google.com/rss/articles/AAA?oc=5');
        $this->get('/sitemap.xml')->assertSee('kajian-penegakan-hukum')->assertDontSee($berita->slug);
    }

    public function test_news_list_loads_next_cards_for_infinite_scroll(): void
    {
        foreach (range(1, 25) as $i) {
            $artikel = $this->externalArtikel("https://media.test/berita-{$i}", "https://img.test/{$i}.jpg");
            $artikel->update(['published_at' => now()->subMinutes($i)]);
            $artikel->translation()->update(['title' => "Berita Nomor {$i}"]);
        }

        $page = $this->get('/id/artikels')->assertOk()
            ->assertSee('Berita Nomor 20')
            ->assertDontSee('Berita Nomor 21')
            ->assertSee('id="artikel-more"', false)
            ->assertDontSee('Pagination Navigation');
        $this->assertStringContainsString('page=2', $page->viewData('artikels')->nextPageUrl());
        $this->assertStringContainsString('jenis=berita', $this->get('/id/artikels?jenis=berita')->viewData('artikels')->nextPageUrl());

        $this->get('/id/artikels?page=2&partial=1')
            ->assertOk()
            ->assertJson(['next' => null])
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'Berita Nomor 21') && str_contains($html, 'Berita Nomor 25')
                && ! str_contains($html, 'Berita Nomor 20') && ! str_contains($html, 'Analisis Auriga'));
    }

    public function test_cms_can_pull_filter_and_hide_news(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->fakeFeeds();

        $component = Livewire::actingAs($admin)->test(ArtikelTable::class)
            ->call('pullNews')
            ->assertSee('2 berita baru ditambahkan')
            ->set('type', 'eksternal')
            ->assertSee('Disway')
            ->set('type', 'internal')
            ->assertDontSee('Disway');

        $berita = Artikel::where('source_name', 'Disway')->first();
        $component->call('toggleActive', $berita->id);
        $this->assertSame('inactive', $berita->fresh()->status);
    }
    public function test_only_news_whose_headline_is_about_the_environment_is_pulled(): void
    {
        $aggregator = app(NewsAggregator::class);
        $relevant = fn (string $title, string $excerpt = '') => $aggregator->isRelevant(['title' => $title, 'excerpt' => $excerpt]);

        // Sesuai tema: isu lingkungan/SDA di judul, penegakan hukum di judul atau ringkasan.
        $this->assertTrue($relevant('Kejagung Tetapkan PT BSJ Tersangka Korupsi Tambang Nikel'));
        $this->assertTrue($relevant('Gakkum Kehutanan Amankan 343 Batang Kayu Ilegal di Banyuwangi'));
        $this->assertTrue($relevant('Polda Kalbar Bongkar 25 Kasus PETI, Sita 435,9 Gram Emas'));
        $this->assertTrue($relevant('Perburuan Gajah di Konsesi Sawit', 'Koalisi mendesak terdakwa dihukum berat.'));
        $this->assertTrue($relevant('Aktivis Lingkungan Dilaporkan atas Pencemaran Nama Baik'));

        // Tidak sesuai tema.
        $this->assertFalse($relevant('Penegakan Hukum di Lingkungan Pendidikan Harus Kedepankan Pendekatan Humanis'));
        $this->assertFalse($relevant('KPK Tanamkan Integritas Pelajar Sulteng untuk Menjaga Kekayaan Sumber Daya Alam'));
        $this->assertFalse($relevant('Alat Sadap Diduga dari Mafia Judol, Narkoba, dan Tambang Ilegal'));
        $this->assertFalse($relevant('Penumpang Selundupkan 2,6 Kg Emas dalam Korset, Ditangkap Bea Cukai'));
        $this->assertFalse($relevant('Narkoba dalam Peti Kemas Disita di Pelabuhan'));
        $this->assertFalse($relevant('Korban Diikat Tali Tambang, Pelaku Ditangkap Polres'));
        $this->assertFalse($relevant('Artis Dilaporkan atas Pencemaran Nama Baik'));
        // Isu lingkungan hanya disebut di ringkasan, bukan pokok berita.
        $this->assertFalse($relevant('MIND ID Gandeng KPK Perkuat Sistem Pelaporan', 'Hilirisasi tambang nikel dan sumber daya alam.'));
    }

    public function test_recheck_lists_then_hides_published_news_outside_the_theme(): void
    {
        $tambang = $this->externalArtikel('https://media.test/tambang', 'https://img.test/1.jpg');
        $tambang->translation()->update(['title' => 'Polres Tutup Tiga Tambang Ilegal di Cianjur']);
        $sekolah = $this->externalArtikel('https://media.test/sekolah', 'https://img.test/2.jpg');
        $sekolah->translation()->update(['title' => 'Penegakan Hukum di Lingkungan Pendidikan Harus Humanis']);

        $this->artisan('news:recheck')
            ->expectsOutputToContain('1 tidak sesuai tema')
            ->expectsOutputToContain('Lingkungan Pendidikan')
            ->assertSuccessful();
        $this->assertSame('active', $sekolah->fresh()->status);

        $this->artisan('news:recheck --hide')->expectsOutputToContain('1 berita disembunyikan')->assertSuccessful();
        $this->assertSame('inactive', $sekolah->fresh()->status);
        $this->assertSame('active', $tambang->fresh()->status);
    }

    public function test_articles_and_news_can_be_searched(): void
    {
        foreach (['Kejagung Sita Rp11 Miliar di Kasus Tambang Nikel Sultra' => 'Media A', 'Polda Kalteng Tetapkan Tersangka Karhutla' => 'Mongabay Indonesia',
            'Nikel Ilegal: Kejagung Geledah Enam Kantor' => 'Media B'] as $title => $source) {
            $artikel = $this->externalArtikel('https://media.test/'.sha1($title), 'https://img.test/'.sha1($title).'.jpg');
            $artikel->update(['source_name' => $source]);
            $artikel->translation()->update(['title' => $title]);
        }

        // Tidak peka huruf besar-kecil; setiap kata harus ada (judul, ringkasan, media, atau kategori).
        $this->get('/id/artikels?q=NIKEL')->assertOk()
            ->assertSee('Kejagung Sita Rp11 Miliar')->assertSee('Nikel Ilegal: Kejagung Geledah')->assertDontSee('Polda Kalteng Tetapkan')
            ->assertSee('hasil untuk', false)
            ->assertViewHas('artikels', fn ($artikels) => $artikels->total() === 2);
        $this->get('/id/artikels?q=nikel+sultra')->assertSee('Kejagung Sita Rp11 Miliar')->assertDontSee('Nikel Ilegal: Kejagung Geledah');
        $this->get('/id/artikels?q=mongabay')->assertSee('Polda Kalteng Tetapkan')->assertDontSee('Kejagung Sita');

        // Kata pencarian ikut di tautan jenis & muatan berikutnya; karakter pola LIKE dicari apa adanya.
        $this->get('/id/artikels?q=nikel&jenis=berita')->assertSee('q=nikel', false)->assertSee('Kejagung Sita Rp11 Miliar');
        $this->get('/id/artikels?q=%25')->assertOk()->assertDontSee('hasil untuk')->assertSee('Polda Kalteng Tetapkan');
        $this->get('/id/artikels?q=tidak-ada-yang-cocok')->assertOk()->assertSee('Tidak ada artikel atau berita yang cocok')->assertSee('Hapus pencarian');
    }
}
