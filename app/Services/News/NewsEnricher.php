<?php

namespace App\Services\News;

use App\Models\{Artikel, ArtikelTranslation};
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\{DB, Http, Log};
use Illuminate\Support\{Sleep, Str};

/**
 * Melengkapi berita eksternal yang belum bergambar:
 * - berita dari Google News dicari dulu di Bing News berdasarkan judulnya (tautan langsung &
 *   gambar, tanpa bergantung pada Google); bila tidak ketemu, tautan Google diurai ke berita aslinya;
 * - gambar & ringkasan diambil dari meta pratinjau halaman berita (og:image, og:description) —
 *   yang sama dipakai media sosial saat tautan dibagikan.
 *
 * Situs yang menolak bot dibiarkan; beritanya tetap tampil dengan gambar cadangan.
 */
class NewsEnricher
{
    public function __construct(private NewsAggregator $news) {}

    /** Permintaan serentak per gelombang, agar Google & situs media tidak terbebani. */
    private const CHUNK = 5;

    private const MAX_BYTES = 1024 * 1024;

    /** Tautan Google yang gagal diurai (biasanya karena Google sedang membatasi) dicoba lagi setelah sekian jam. */
    private const RETRY_HOURS = 6;

    private const GOOGLE_LINK = '~^https://news\.google\.com/(?:rss/)?articles/([\w-]+)~';

    /**
     * @param  float|null  $seconds  batas waktu (permintaan web); sisanya dilanjutkan penarikan berikutnya
     * @return array{checked: int, images: int, throttled: bool}
     */
    public function enrich(int $limit = 40, ?float $seconds = null): array
    {
        $deadline = $seconds ? microtime(true) + $seconds : null;
        $checked = 0;
        $images = 0;

        $artikels = Artikel::where('type', 'eksternal')
            ->where(fn ($q) => $q->whereNull('image')->orWhere('image', ''))
            ->whereNotNull('link')
            ->where(fn ($q) => $q->whereNull('enriched_at')->orWhere(fn ($q) => $q
                ->where('link', 'like', 'https://news.google.com/%')
                ->where('enriched_at', '<', now()->subHours(self::RETRY_HOURS))
                ->where('published_at', '>=', now()->subDays((int) config('news.max_age_days', 14)))))
            // Tautan langsung didahulukan agar tidak ikut tertahan saat Google sedang membatasi.
            ->orderByRaw("link like 'https://news.google.com/%'")
            ->orderByRaw('enriched_at is not null')
            ->orderByDesc('published_at')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'link']);

        $googleBlocked = false;
        $lookups = 0;
        $searched = [];

        // Satu pencarian Bing per berita, berjeda agar Bing tidak membatasi.
        $bing = function (int $id) use (&$lookups, &$searched) {
            $searched[$id] = true;
            if ($lookups++ > 0) {
                Sleep::for(1)->second();
            }

            return $this->findOnBing($id);
        };

        foreach ($artikels->chunk(self::CHUNK)->values() as $i => $chunk) {
            if ($deadline && microtime(true) > $deadline) {
                break;
            }

            // Jeda antar gelombang agar Google/Bing tidak membatasi permintaan.
            if ($i > 0) {
                Sleep::for(1)->second();
            }

            $links = $chunk->pluck('link', 'id')->all();

            foreach ($links as $id => $link) {
                if (! preg_match(self::GOOGLE_LINK, $link) || ($deadline && microtime(true) > $deadline)) {
                    continue;
                }

                if ($found = $bing($id)) {
                    unset($links[$id]);
                    $checked++;
                    $images += $found === 'replaced' ? 1 : 0;
                }
            }

            if ($googleBlocked) {
                $failed = array_keys(array_filter($links, fn ($link) => preg_match(self::GOOGLE_LINK, $link)));
                $resolved = array_diff_key($links, array_flip($failed));
            } else {
                [$resolved, $failed] = $this->resolveGoogleLinks($links);
            }

            foreach ($this->previews($resolved) as $id => $preview) {
                $this->save($id, $resolved[$id], $preview);
                $checked++;

                // Situs menolak bot atau tanpa gambar: artikel yang sama dicari di Bing (bergambar).
                if ($preview['image'] || (! isset($searched[$id]) && ! ($deadline && microtime(true) > $deadline) && $bing($id) === 'replaced')) {
                    $images++;
                }
            }

            if ($failed) {
                // Tidak ketemu di Bing dan Google menjawab tanpa alamat berita (biasanya sedang membatasi):
                // Google tidak dihubungi lagi pada penarikan ini, dan berita ini dicoba lagi beberapa jam lagi.
                // Sementara itu berita tampil dengan gambar cadangan.
                $googleBlocked = true;
                Artikel::whereKey($failed)->update(['enriched_at' => now()]);
            }
        }

        return ['checked' => $checked, 'images' => $images, 'throttled' => $googleBlocked];
    }

    /**
     * Cari artikel yang sama di Bing News berdasarkan judul, diutamakan dari media yang sama.
     * Bila ketemu, kartu berita diganti dengan versi Bing (tautan langsung & gambar).
     *
     * @return 'replaced'|'duplicate'|null
     */
    private function findOnBing(int $id): ?string
    {
        $artikel = Artikel::with('translation')->find($id);
        $title = $artikel?->translation->firstWhere('locale', 'id')?->title;
        if (! $title) {
            return null;
        }

        $source = Str::lower(preg_replace('/[^\p{L}\p{N}]+/u', '', Str::before((string) $artikel->source_name, '.')));
        $match = collect($this->news->searchBing($title))
            ->filter(fn ($item) => $item['image'] && $this->news->isIndonesian($item) && $this->news->isSameArticle($title, $item['title']))
            ->sortByDesc(fn ($item) => (str_contains(Str::lower(preg_replace('/[^\p{L}\p{N}]+/u', '', $item['source'])), $source) ? 10 : 0)
                + $this->news->similarity($title, $item['title']))
            ->first();

        if (! $match) {
            return null;
        }

        // Artikel itu sudah tampil sebagai kartu lain: versi Google ini hanya duplikat.
        if (Artikel::whereKeyNot($id)->where(fn ($q) => $q->where('external_id', $match['external_id'])->orWhere('link', $match['link']))->exists()) {
            DB::transaction(function () use ($artikel) {
                $artikel->translation()->delete();
                $artikel->delete();
            });

            return 'duplicate';
        }

        $this->news->replaceWith($id, $match);

        return 'replaced';
    }

    /**
     * Urai tautan news.google.com ke alamat berita aslinya. Tautan lain dikembalikan apa adanya.
     *
     * @param  array<int, string>  $links
     * @return array{0: array<int, string>, 1: list<int>} tautan yang siap dibuka, dan ID yang gagal diurai
     */
    private function resolveGoogleLinks(array $links): array
    {
        $google = [];
        foreach ($links as $id => $link) {
            if (preg_match(self::GOOGLE_LINK, $link, $m)) {
                $google[$id] = $m[1];
            }
        }

        if ($google === []) {
            return [$links, []];
        }

        // 1) Halaman artikel Google News memuat tanda tangan & waktu untuk meminta alamat aslinya.
        $pages = Http::pool(fn (Pool $pool) => array_map(
            fn ($id) => $pool->as((string) $id)->connectTimeout(5)->timeout(10)->withUserAgent($this->userAgent())
                ->get("https://news.google.com/articles/{$google[$id]}"),
            array_keys($google),
        ));

        $params = [];
        $failed = [];
        foreach ($google as $id => $articleId) {
            $page = $pages[(string) $id] ?? null;
            $html = $page instanceof Response && $page->successful() ? $this->html($page) : '';

            if (preg_match('/data-n-a-sg="([^"]+)"/', $html, $sg) && preg_match('/data-n-a-ts="(\d+)"/', $html, $ts)) {
                $params[$id] = [$articleId, (int) $ts[1], $sg[1]];
            } else {
                $failed[] = $id;
                unset($links[$id]);
            }
        }

        // 2) Minta alamat asli lewat endpoint yang sama dengan yang dipakai halaman Google News.
        $answers = Http::pool(fn (Pool $pool) => array_map(
            fn ($id) => $pool->as((string) $id)->connectTimeout(5)->timeout(10)->withUserAgent($this->userAgent())->asForm()
                ->post('https://news.google.com/_/DotsSplashUi/data/batchexecute', ['f.req' => $this->decodeRequest(...$params[$id])]),
            array_keys($params),
        ));

        foreach (array_keys($params) as $id) {
            $answer = $answers[(string) $id] ?? null;
            $url = $answer instanceof Response && $answer->successful() ? $this->decodedUrl($answer->body()) : null;

            if ($url) {
                $links[$id] = $url;
            } else {
                $failed[] = $id;
                unset($links[$id]);
            }
        }

        return [$links, $failed];
    }

    private function decodeRequest(string $articleId, int $timestamp, string $signature): string
    {
        $inner = json_encode(['garturlreq', [['X', 'X', ['X', 'X'], null, null, 1, 1, 'US:en', null, 1, null, null, null, null, null, 0, 1],
            'X', 'X', 1, [1, 1, 1], 1, 1, null, 0, 0, null, 0], $articleId, $timestamp, $signature]);

        return json_encode([[['Fbv4je', $inner, null, 'generic']]]);
    }

    /**
     * Jawaban: )]}'\n\n[["wrb.fr","Fbv4je","[\"garturlres\",\"https://…\",1]",…]]
     */
    private function decodedUrl(string $body): ?string
    {
        $rows = json_decode(trim(Str::after($body, "\n")), true);

        foreach (is_array($rows) ? $rows : [] as $row) {
            if (($row[0] ?? null) === 'wrb.fr' && is_string($row[2] ?? null)) {
                $url = json_decode($row[2], true)[1] ?? null;

                return is_string($url) && preg_match('~^https?://~i', $url) ? $url : null;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $links
     * @return array<int, array{image: ?string, description: string}>
     */
    private function previews(array $links): array
    {
        if ($links === []) {
            return [];
        }

        $pages = Http::pool(fn (Pool $pool) => array_map(
            fn ($id) => $pool->as((string) $id)->connectTimeout(5)->timeout(10)->withUserAgent($this->userAgent())->get($links[$id]),
            array_keys($links),
        ));

        $previews = [];
        foreach ($links as $id => $url) {
            $page = $pages[(string) $id] ?? null;
            $html = $page instanceof Response && $page->successful() ? $this->html($page) : '';
            $meta = $this->meta($html);

            $previews[$id] = [
                'image' => $this->imageUrl($meta, $html, $url),
                'description' => $this->description($meta, $html),
            ];
        }

        return $previews;
    }

    /**
     * Hanya awal halaman HTML yang dibaca (meta pratinjau ada di <head>), agar halaman atau
     * berkas besar (PDF, video) tidak menghabiskan memori.
     */
    private function html(Response $response): string
    {
        $type = strtolower($response->header('Content-Type'));
        if ($type !== '' && ! str_contains($type, 'html')) {
            return '';
        }

        $body = $response->toPsrResponse()->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $html = '';
        while (! $body->eof() && strlen($html) < self::MAX_BYTES) {
            $html .= $body->read(self::MAX_BYTES - strlen($html));
        }

        return $html;
    }

    /**
     * Isi tag <meta> di <head> (property/name/itemprop => content), yang pertama yang dipakai.
     *
     * @return array<string, string>
     */
    private function meta(string $html): array
    {
        $head = Str::before($html, '</head>');
        preg_match_all('/<meta\b[^>]*>/i', $head, $tags);

        $meta = [];
        foreach ($tags[0] as $tag) {
            $attributes = $this->attributes($tag);

            $key = strtolower($attributes['property'] ?? $attributes['name'] ?? $attributes['itemprop'] ?? '');
            if ($key !== '' && isset($attributes['content']) && ! isset($meta[$key])) {
                $meta[$key] = trim(html_entity_decode($attributes['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        return $meta;
    }

    /**
     * @return array<string, string>
     */
    private function attributes(string $tag): array
    {
        preg_match_all('/([\w:-]+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/', $tag, $attrs, PREG_SET_ORDER);

        $attributes = [];
        foreach ($attrs as $attr) {
            $attributes[strtolower($attr[1])] = ($attr[2] ?? '') !== '' ? $attr[2] : ($attr[3] ?? '');
        }

        return $attributes;
    }

    /**
     * Gambar utama berita, berurutan: meta pratinjau (og:image, twitter:image, itemprop image),
     * <link rel="image_src">, data terstruktur JSON-LD, lalu gambar pertama setelah judul berita.
     */
    private function imageUrl(array $meta, string $html, string $pageUrl): ?string
    {
        $candidates = array_map(fn ($key) => $meta[$key] ?? '', ['og:image', 'og:image:secure_url', 'og:image:url', 'twitter:image', 'twitter:image:src', 'image']);

        // Sumber berikutnya hanya diperiksa bila sebelumnya tidak menghasilkan gambar.
        $sources = [fn () => $candidates, fn () => [$this->linkImage($html)], fn () => $this->jsonLdImages($html), fn () => [$this->heroImage($html)]];

        foreach ($sources as $source) {
            foreach ($source() as $candidate) {
                if ($url = $this->absoluteUrl((string) $candidate, $pageUrl)) {
                    return $url;
                }
            }
        }

        return null;
    }

    private function linkImage(string $html): ?string
    {
        preg_match_all('/<link\b[^>]*>/i', Str::before($html, '</head>'), $tags);

        foreach ($tags[0] as $tag) {
            $attributes = $this->attributes($tag);
            if (strtolower($attributes['rel'] ?? '') === 'image_src') {
                return $attributes['href'] ?? null;
            }
        }

        return null;
    }

    /**
     * Gambar dari JSON-LD (schema.org) berita/halaman, termasuk rujukan "@id" ala Yoast.
     *
     * @return list<string>
     */
    private function jsonLdImages(string $html): array
    {
        [$nodes, $byId] = $this->jsonLdNodes($html);

        $images = [];
        foreach ($nodes as $node) {
            // Hanya berita/halaman; gambar organisasi biasanya logo.
            if (! preg_match('/Article|BlogPosting|WebPage/', implode(' ', (array) ($node['@type'] ?? '')))) {
                continue;
            }

            foreach (['image', 'primaryImageOfPage', 'thumbnailUrl'] as $key) {
                if (isset($node[$key]) && $url = $this->jsonLdUrl($node[$key], $byId)) {
                    $images[] = $url;
                }
            }
        }

        return $images;
    }

    /**
     * Semua objek JSON-LD di halaman (termasuk isi "@graph"), dan indeksnya per "@id".
     *
     * @return array{0: list<array>, 1: array<string, array>}
     */
    private function jsonLdNodes(string $html): array
    {
        preg_match_all('~<script\b[^>]*application/ld\+json[^>]*>(.*?)</script>~is', $html, $blocks);

        $nodes = [];
        foreach ($blocks[1] as $json) {
            $data = json_decode(trim($json), true);
            foreach (is_array($data) ? (array_is_list($data) ? $data : [$data]) : [] as $node) {
                foreach (is_array($node['@graph'] ?? null) ? $node['@graph'] : [$node] as $item) {
                    if (is_array($item)) {
                        $nodes[] = $item;
                    }
                }
            }
        }

        $byId = [];
        foreach ($nodes as $node) {
            if (is_string($node['@id'] ?? null)) {
                $byId[$node['@id']] = $node;
            }
        }

        return [$nodes, $byId];
    }

    private function jsonLdUrl(mixed $value, array $byId, int $depth = 0): ?string
    {
        if (is_string($value) || $depth > 2) {
            return is_string($value) ? $value : null;
        }

        if (! is_array($value)) {
            return null;
        }

        if (array_is_list($value)) {
            return isset($value[0]) ? $this->jsonLdUrl($value[0], $byId, $depth + 1) : null;
        }

        $url = $value['url'] ?? $value['contentUrl'] ?? null;
        if (! $url && is_string($value['@id'] ?? null) && isset($byId[$value['@id']]) && $byId[$value['@id']] !== $value) {
            return $this->jsonLdUrl($byId[$value['@id']], $byId, $depth + 1);
        }

        return is_string($url) ? $url : null;
    }

    /**
     * Gambar pertama setelah judul berita (<h1>, atau <article> bila tanpa h1), termasuk gambar
     * lazy-load (data-src). Tanpa penanda itu tidak ditebak, agar logo situs tidak terambil.
     */
    private function heroImage(string $html): ?string
    {
        $start = stripos($html, '<h1');
        $start = $start === false ? stripos($html, '<article') : $start;
        if ($start === false) {
            return null;
        }

        preg_match_all('/<(?:img|amp-img)\b[^>]*>/i', substr($html, $start, 80_000), $tags);

        foreach ($tags[0] as $tag) {
            $attributes = $this->attributes($tag);
            $src = collect(['data-src', 'data-lazy-src', 'data-original', 'src'])
                ->map(fn ($key) => trim($attributes[$key] ?? ''))
                ->first(fn ($value) => $value !== '' && ! str_starts_with($value, 'data:'));

            $small = (int) ($attributes['width'] ?? 0) && (int) $attributes['width'] < 300
                || (int) ($attributes['height'] ?? 0) && (int) $attributes['height'] < 150;

            if ($src && ! $small && ! preg_match('~\.(svg|gif)(\?|$)|logo|icon|avatar|emoji|(^|[/_.-])(ads?|iklan|banner)([/_.-]|$)~i', $src)) {
                return $src;
            }
        }

        return null;
    }

    private function absoluteUrl(string $url, string $pageUrl): ?string
    {
        $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // Sebagian situs menggandakan domain, mis. "http://a.idhttps://b.id/foto.jpg": ambil alamat terakhir.
        if (preg_match_all('~https?://~i', $url, $m, PREG_OFFSET_CAPTURE) > 1) {
            $url = substr($url, end($m[0])[1]);
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        } elseif (str_starts_with($url, '/')) {
            $url = parse_url($pageUrl, PHP_URL_SCHEME).'://'.parse_url($pageUrl, PHP_URL_HOST).$url;
        }

        // Karakter non-ASCII/spasi di nama berkas (mis. tanda kutip lengkung) di-encode agar URL sah.
        $url = preg_replace_callback('/[^\x21-\x7E]/', fn ($m) => rawurlencode($m[0]), $url);

        // Kolom image VARCHAR(255).
        return preg_match('~^https?://~i', $url) && strlen($url) <= 255 && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * Ringkasan dari og:description, twitter:description, atau JSON-LD berita. Meta "description"
     * biasa sering berisi slogan situs, jadi hanya dipakai bila memuat kata-kata judul berita.
     */
    private function description(array $meta, string $html): string
    {
        $candidates = [$meta['og:description'] ?? '', $meta['twitter:description'] ?? ''];

        [$nodes] = $this->jsonLdNodes($html);
        foreach ($nodes as $node) {
            if (preg_match('/Article|BlogPosting/', implode(' ', (array) ($node['@type'] ?? ''))) && is_string($node['description'] ?? null)) {
                $candidates[] = $node['description'];
            }
        }

        $plain = $meta['description'] ?? '';
        preg_match('~<title[^>]*>(.*?)</title>~is', $html, $title);
        $pageTitle = $meta['og:title'] ?? $meta['twitter:title'] ?? html_entity_decode($title[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (count(array_intersect($this->keywords($plain), $this->keywords($pageTitle))) >= 2) {
            $candidates[] = $plain;
        }

        foreach ($candidates as $text) {
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
            if ($text !== '') {
                return Str::limit($text, 280);
            }
        }

        return '';
    }

    /**
     * Kata penting (≥4 huruf, tanpa kata tugas) untuk membandingkan ringkasan dengan judul.
     *
     * @return list<string>
     */
    private function keywords(string $text): array
    {
        preg_match_all('/\p{L}{4,}/u', mb_strtolower($text), $words);

        return array_values(array_unique(array_diff($words[0], ['yang', 'dengan', 'untuk', 'dalam', 'dari', 'pada', 'akan', 'oleh',
            'adalah', 'juga', 'tersebut', 'karena', 'setelah', 'para', 'atas', 'berita', 'terkini', 'hari', 'portal', 'media'])));
    }

    private function save(int $id, string $url, array $preview): void
    {
        DB::transaction(function () use ($id, $url, $preview) {
            // Tautan diganti ke berita aslinya, agar pembaca tidak lewat halaman pengalihan Google.
            Artikel::whereKey($id)->update(array_filter([
                'link' => $url,
                'image' => $preview['image'],
                'enriched_at' => now(),
            ]));

            if ($preview['description'] !== '') {
                ArtikelTranslation::where('artikel_id', $id)->where(fn ($q) => $q->whereNull('excerpt')->orWhere('excerpt', ''))
                    ->update(['excerpt' => $preview['description']]);
            }
        });

        if (! $preview['image']) {
            Log::info("news: tidak ada gambar pratinjau untuk artikel {$id} ({$url})");
        }
    }

    private function userAgent(): string
    {
        return 'greendefender-news/1.0 (+'.url('/').')';
    }
}
