<?php

namespace App\Services\News;

use App\Models\{Artikel, ArtikelTranslation};
use App\Services\CaseImport\CategoryGuesser;
use Illuminate\Http\Client\{ConnectionException, Pool};
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Http, Log};
use Illuminate\Support\Str;
use SimpleXMLElement;

/**
 * Menarik berita penegakan hukum lingkungan & SDA dari Bing News, Google News, dan RSS media
 * (config/news.php), lalu menyimpannya sebagai artikel "eksternal" yang langsung tampil.
 *
 * Yang disimpan hanya judul, ringkasan singkat dari feed, nama media, tanggal, dan
 * tautan ke berita asli — isi berita tidak disalin.
 */
class NewsAggregator
{
    /** Kemiripan kata judul (Jaccard) di atas ini dianggap peristiwa yang sama dari media lain. */
    private const SAME_STORY_SIMILARITY = 0.35;

    /** Lebih ketat: untuk mencari artikel yang sama persis berdasarkan judul (NewsEnricher). */
    private const SAME_ARTICLE_SIMILARITY = 0.5;

    private const STOPWORDS = ['yang', 'dalam', 'untuk', 'dengan', 'dari', 'pada', 'akan', 'oleh', 'atas', 'kasus', 'jadi',
        'menjadi', 'sebagai', 'para', 'soal', 'terkait', 'usai', 'kembali', 'tetap', 'masih', 'bisa', 'tidak', 'agar', 'karena'];

    /** Kata tugas untuk membedakan berita berbahasa Indonesia dari berbahasa Inggris. */
    private const INDONESIAN_WORDS = ['yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'dalam', 'ini', 'itu', 'tidak', 'akan',
        'pada', 'oleh', 'bagi', 'terhadap', 'serta', 'adalah', 'telah', 'sudah', 'juga', 'atas', 'karena', 'setelah', 'tersebut', 'hingga'];

    private const ENGLISH_WORDS = ['the', 'of', 'and', 'in', 'to', 'for', 'with', 'on', 'at', 'by', 'from', 'is', 'are', 'was', 'were',
        'has', 'have', 'had', 'its', 'their', 'after', 'over', 'into', 'as', 'be', 'been', 'says', 'said', 'against', 'amid'];

    public function __construct(private CategoryGuesser $categories) {}

    /**
     * @return array{fetched: int, relevant: int, created: int, failed_sources: list<string>}
     */
    public function pull(): array
    {
        [$items, $failed] = $this->fetchAll();

        $relevant = array_values(array_filter($items, fn ($item) => $this->isIndonesian($item) && $this->isRelevant($item)));
        $known = $this->recentStories();
        $minDate = now()->subDays((int) config('news.max_age_days', 14));
        $created = 0;
        $upgraded = 0;

        // Versi bergambar (RSS media) didahulukan atas versi Google News tanpa gambar, lalu yang terbaru.
        usort($relevant, fn ($a, $b) => [(bool) $b['image'], $b['published_at']] <=> [(bool) $a['image'], $a['published_at']]);

        foreach ($relevant as $item) {
            if ($created >= (int) config('news.max_per_run', 60)) {
                break;
            }

            if ($item['published_at']->lt($minDate)
                || Artikel::where('external_id', $item['external_id'])->orWhere('link', $item['link'])->exists()) {
                continue;
            }

            $story = ['id' => null, 'words' => $this->titleWords($item['title']), 'date' => $item['published_at'], 'image' => (bool) $item['image']];
            $same = $this->sameStory($story, $known);

            if ($same !== null) {
                // Peristiwa sudah tersimpan tanpa gambar (dari Google News): ganti dengan versi media yang bergambar.
                if ($same['id'] && ! $same['image'] && $item['image']) {
                    $this->replaceWith($same['id'], $item);
                    $known[array_search($same, $known, true)]['image'] = true;
                    $upgraded++;
                }

                continue;
            }

            $story['id'] = $this->store($item);
            $known[] = $story;
            $created++;
        }

        return ['fetched' => count($items), 'relevant' => count($relevant), 'created' => $created, 'upgraded' => $upgraded, 'failed_sources' => $failed];
    }

    /**
     * @param  array{title: string, excerpt: string}  $item
     */
    public function isRelevant(array $item): bool
    {
        $title = $item['title'];

        // Tema lingkungan/SDA harus jadi pokok berita (ada di judul); penegakan hukumnya boleh disebut di ringkasan.
        return $this->mentions($title, config('news.environment_keywords', []))
            && $this->mentions($title.' '.$item['excerpt'], config('news.enforcement_keywords', []))
            && ! $this->mentions($title, config('news.exclude_keywords', []));
    }

    /**
     * Hanya berita berbahasa Indonesia dari media Indonesia. Dilewati: judul/ringkasan berhuruf
     * non-Latin (Arab, Cina, dst.) atau berbahasa Inggris, dan media bernama huruf non-Latin
     * (situs luar yang menyalin berita Indonesia).
     *
     * @param  array{title: string, excerpt: string, source: string}  $item
     */
    public function isIndonesian(array $item): bool
    {
        $text = $item['title'].' '.$item['excerpt'];

        foreach ([$text, $item['source']] as $part) {
            $letters = preg_match_all('/\p{L}/u', $part);
            if ($letters && preg_match_all('/\p{Latin}/u', $part) / $letters < 0.9) {
                return false;
            }
        }

        preg_match_all('/\p{L}+/u', mb_strtolower($text), $words);

        return count(array_intersect($words[0], self::ENGLISH_WORDS)) <= count(array_intersect($words[0], self::INDONESIAN_WORDS));
    }

    /**
     * @return array{0: list<array>, 1: list<string>}
     */
    private function fetchAll(): array
    {
        $sources = [];
        $engines = config('news.search_engines', ['bing', 'google']);
        foreach (config('news.search_queries', []) as $i => $query) {
            if (in_array('bing', $engines, true)) {
                $sources["bing:{$i}"] = ['type' => 'bing', 'name' => 'Bing News', 'url' => $this->bingUrl($query, sortByDate: true)];
            }
            if (in_array('google', $engines, true)) {
                // "when:7d" = hanya 7 hari terakhir.
                $sources["google:{$i}"] = ['type' => 'google', 'name' => 'Google News', 'url' => 'https://news.google.com/rss/search?'.http_build_query([
                    'q' => $query.' when:7d', 'hl' => 'id', 'gl' => 'ID', 'ceid' => 'ID:id',
                ])];
            }
        }
        foreach (config('news.rss_feeds', []) as $name => $url) {
            $sources["rss:{$name}"] = ['type' => 'rss', 'name' => $name, 'url' => $url];
        }

        $responses = Http::pool(fn (Pool $pool) => array_map(
            fn ($key) => $pool->as($key)->timeout(15)->withUserAgent($this->userAgent())->get($sources[$key]['url']),
            array_keys($sources),
        ));

        $items = [];
        $failed = [];
        foreach ($sources as $key => $source) {
            $response = $responses[$key] ?? null;
            $xml = $response instanceof Response && $response->successful()
                ? @simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET)
                : false;

            if (! $xml || ! isset($xml->channel)) {
                $failed[] = $source['name'];
                Log::warning("news:pull gagal membaca {$source['url']}");

                continue;
            }

            foreach ($xml->channel->item as $node) {
                if ($item = $this->parseItem($node, $source)) {
                    $items[] = $item;
                }
            }
        }

        return [$items, array_values(array_unique($failed))];
    }

    private function parseItem(SimpleXMLElement $node, array $source): ?array
    {
        $title = trim(html_entity_decode((string) $node->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $link = trim((string) $node->link);

        if ($title === '' || ! str_starts_with($link, 'http')) {
            return null;
        }

        if ($source['type'] === 'google') {
            // Judul Google News selalu diakhiri " - Nama Media" (kadang berupa domain, mis. "- kumparan.com");
            // deskripsinya hanya mengulang judul.
            $media = trim(trim((string) $node->source) ?: Str::afterLast($title, ' - '), " -–|");
            $title = str_contains($title, ' - ') ? Str::beforeLast($title, ' - ') : $title;
            $title = $this->stripMediaSuffix($title, $media);
            $excerpt = '';
            $image = null;
            $externalId = 'gnews:'.sha1(trim((string) $node->guid) ?: $link);
        } elseif ($source['type'] === 'bing') {
            // Tautan Bing berupa pengalihan (apiclick.aspx?…&url=<alamat asli>); nama media & gambar di <News:…>.
            parse_str((string) parse_url($link, PHP_URL_QUERY), $query);
            $link = is_string($query['url'] ?? null) && preg_match('~^https?://~i', $query['url']) ? $query['url'] : $link;
            $news = $node->children($node->getNamespaces(true)['News'] ?? 'News');
            $media = trim((string) $news->Source) ?: (string) parse_url($link, PHP_URL_HOST);
            $excerpt = Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $node->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 280);
            $image = preg_replace('~^http://~i', 'https://', trim((string) $news->Image)) ?: null;
            $image = $image && strlen($image) <= 255 ? $image : null;
            $externalId = 'bing:'.sha1($link);
        } else {
            $media = $source['name'];
            $excerpt = Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $node->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 280);
            $image = $this->imageOf($node);
            $externalId = 'rss:'.sha1(trim((string) $node->guid) ?: $link);
        }

        try {
            $publishedAt = Carbon::parse((string) $node->pubDate);
        } catch (\Throwable) {
            $publishedAt = now();
        }

        return [
            'external_id' => $externalId,
            'title' => Str::limit($title, 250, ''),
            'excerpt' => $excerpt,
            'link' => $link,
            'source' => Str::limit($media, 100, ''),
            'image' => $image,
            'published_at' => $publishedAt,
        ];
    }

    /**
     * Gambar berita dari RSS: <enclosure>, <media:content>/<media:thumbnail>, atau <img> pertama di deskripsi/konten.
     */
    private function imageOf(SimpleXMLElement $node): ?string
    {
        $candidates = [];

        if (isset($node->enclosure['url']) && str_starts_with((string) $node->enclosure['type'], 'image')) {
            $candidates[] = (string) $node->enclosure['url'];
        }

        $namespaces = $node->getNamespaces(true);
        if (isset($namespaces['media'])) {
            $media = $node->children($namespaces['media']);
            foreach ([$media->content, $media->thumbnail, $media->group->content ?? null] as $element) {
                if ($element && isset($element['url'])) {
                    $candidates[] = (string) $element['url'];
                }
            }
        }

        $html = (string) $node->description.(isset($namespaces['content']) ? (string) $node->children($namespaces['content'])->encoded : '');
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            $candidates[] = html_entity_decode($m[1]);
        }
        if (isset($node->enclosure['url'])) {
            $candidates[] = (string) $node->enclosure['url'];
        }

        foreach ($candidates as $url) {
            // Kolom image VARCHAR(255); hanya URL http(s) yang dipakai.
            if (preg_match('~^https?://~i', $url) && strlen($url) <= 255) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Buang akhiran nama media yang kadang sudah ada di judul aslinya, mis. "… Dibidik! - disway.id"
     * atau "… – Kalimantan Post".
     */
    private function stripMediaSuffix(string $title, string $media): string
    {
        $mediaKey = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', Str::before($media, '.')));

        while (preg_match('/^(.{20,}?)\s+[-–|]\s+([^-–|]{2,40})$/u', $title, $m)) {
            $suffix = trim($m[2]);
            $suffixKey = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', Str::before($suffix, '.')));
            $looksLikeDomain = (bool) preg_match('/^[\w-]+(\.[\w-]+)*\.[a-z]{2,}$/i', $suffix);

            if (! $looksLikeDomain && ($mediaKey === '' || ! str_contains($suffixKey, $mediaKey) && ! str_contains($mediaKey, $suffixKey))) {
                break;
            }

            $title = trim($m[1]);
        }

        return $title;
    }

    private function store(array $item): int
    {
        return DB::transaction(function () use ($item) {
            $category = $this->categories->guess(['title' => $item['title'], 'description' => $item['excerpt']]);

            $artikel = Artikel::create([
                'slug' => Str::limit(Str::slug($item['title']), 80, '').'-'.substr(sha1($item['external_id']), 0, 6),
                'type' => 'eksternal',
                'status' => 'active',
                'published_at' => $item['published_at'],
                'link' => $item['link'],
                'image' => $item['image'],
                'source_name' => $item['source'],
                'external_id' => $item['external_id'],
                'category_id' => $category['id'] ?? null,
            ]);

            // Berita berbahasa Indonesia; versi EN memakai teks yang sama agar tetap tampil di /en.
            foreach (['id', 'en'] as $locale) {
                ArtikelTranslation::create([
                    'artikel_id' => $artikel->id,
                    'locale' => $locale,
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => null,
                ]);
            }

            return $artikel->id;
        });
    }

    /**
     * Ganti berita Google News (tanpa gambar, tautan lewat Google) dengan versi media yang bergambar.
     * Seluruh isi kartu (judul, ringkasan, media, tautan, gambar) diganti agar tetap satu artikel yang sama.
     */
    public function replaceWith(int $artikelId, array $item): void
    {
        DB::transaction(function () use ($artikelId, $item) {
            Artikel::whereKey($artikelId)->update([
                'image' => $item['image'],
                'link' => $item['link'],
                'source_name' => $item['source'],
                'external_id' => $item['external_id'],
                'enriched_at' => now(),
            ]);

            ArtikelTranslation::where('artikel_id', $artikelId)->update(['title' => $item['title'], 'excerpt' => $item['excerpt']]);
        });
    }

    /**
     * Cari di Bing News (urut relevansi), mis. judul berita yang belum bergambar.
     *
     * @return list<array>
     */
    public function searchBing(string $query): array
    {
        try {
            $response = Http::connectTimeout(5)->timeout(10)->withUserAgent($this->userAgent())->get($this->bingUrl($query, sortByDate: false));
        } catch (ConnectionException) {
            return [];
        }

        $xml = $response->successful() ? @simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET) : false;

        $items = [];
        foreach ($xml && isset($xml->channel) ? $xml->channel->item : [] as $node) {
            if ($item = $this->parseItem($node, ['type' => 'bing', 'name' => 'Bing News'])) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Dua judul dianggap artikel yang sama bila katanya sangat mirip dan angkanya persis sama
     * (mis. "52 tersangka" ≠ "48 tersangka").
     */
    public function isSameArticle(string $a, string $b): bool
    {
        preg_match_all('/\d+/', $a, $numbersA);
        preg_match_all('/\d+/', $b, $numbersB);
        $numbersA = array_unique($numbersA[0]);
        $numbersB = array_unique($numbersB[0]);
        sort($numbersA);
        sort($numbersB);

        return $numbersA === $numbersB && $this->similarity($a, $b) >= self::SAME_ARTICLE_SIMILARITY;
    }

    /**
     * Kemiripan kata judul (Jaccard), 0–1.
     */
    public function similarity(string $a, string $b): float
    {
        $wordsA = $this->titleWords($a);
        $wordsB = $this->titleWords($b);
        $total = count(array_unique([...$wordsA, ...$wordsB]));

        return $total ? count(array_intersect($wordsA, $wordsB)) / $total : 0.0;
    }

    /**
     * Berita eksternal beberapa hari terakhir, agar peristiwa yang sama dari media lain
     * (atau dari Mongabay langsung dan lewat Google News) tidak tampil berulang.
     *
     * @return list<array{id: int, words: list<string>, date: \Illuminate\Support\Carbon, image: bool}>
     */
    private function recentStories(): array
    {
        return DB::table('artikel_translations')
            ->join('artikels', 'artikels.id', '=', 'artikel_translations.artikel_id')
            ->where('artikels.type', 'eksternal')
            ->where('artikel_translations.locale', 'id')
            ->where('artikels.published_at', '>=', now()->subDays((int) config('news.max_age_days', 14) + 3)->toDateString())
            ->get(['artikels.id', 'artikels.image', 'artikel_translations.title', 'artikels.published_at'])
            ->map(fn ($row) => ['id' => (int) $row->id, 'words' => $this->titleWords($row->title), 'date' => Carbon::parse($row->published_at), 'image' => filled($row->image)])
            ->all();
    }

    /**
     * Sama bila judulnya sangat mirip (Jaccard kata) dan terbit berdekatan (±3 hari).
     */
    private function sameStory(array $story, array $known): ?array
    {
        foreach ($known as $other) {
            if (abs($story['date']->diffInDays($other['date'])) > 3 || ! $story['words'] || ! $other['words']) {
                continue;
            }

            $shared = count(array_intersect($story['words'], $other['words']));
            $total = count(array_unique([...$story['words'], ...$other['words']]));

            if ($shared / $total >= self::SAME_STORY_SIMILARITY) {
                return $other;
            }
        }

        return null;
    }

    /**
     * Kata bermakna dari judul (huruf kecil, ≥3 huruf, tanpa kata umum).
     *
     * @return list<string>
     */
    private function titleWords(string $title): array
    {
        preg_match_all('/[\p{L}\p{N}]{3,}/u', mb_strtolower($title), $m);

        return array_values(array_unique(array_diff($m[0], self::STOPWORDS)));
    }

    private function bingUrl(string $query, bool $sortByDate): string
    {
        return 'https://www.bing.com/news/search?'.http_build_query(array_filter([
            'q' => $query, 'format' => 'rss', 'mkt' => 'id-ID', 'setlang' => 'id', 'qft' => $sortByDate ? 'sortbydate="1"' : null,
        ]));
    }

    private function userAgent(): string
    {
        return 'greendefender-news/1.0 (+'.url('/').')';
    }

    /** Kata utuh, tanpa membedakan huruf besar-kecil; kecuali singkatan berhuruf besar semua (mis. "PETI"), yang harus persis. */
    private function mentions(string $text, array $keywords): bool
    {
        $pattern = fn (array $words) => implode('|', array_map(fn ($k) => preg_quote($k, '/'), $words));
        $acronyms = array_filter($keywords, fn ($k) => mb_strtoupper($k) === $k && preg_match('/\p{Lu}/u', $k));
        $words = array_diff($keywords, $acronyms);

        return ($words && preg_match('/\b('.$pattern($words).')\b/iu', $text))
            || ($acronyms && preg_match('/\b('.$pattern($acronyms).')\b/u', $text));
    }
}
