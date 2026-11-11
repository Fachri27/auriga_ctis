<?php

namespace App\Services\CaseImport;

use Illuminate\Support\Facades\DB;

/**
 * Menebak kategori kasus dari isi dokumen.
 *
 * Dokumen dinilai per tema (tambang, hutan, satwa, …) dari kata kunci yang muncul; judul
 * dan "Dugaan Permasalahan" berbobot lebih besar. Tema lalu dihubungkan ke kategori yang
 * ada di database lewat nama/slug/deskripsi kategori, jadi tetap bekerja walau daftar
 * kategori diubah dari CMS. Kategori payung ("Lingkungan Hidup") ikut semua tema lingkungan
 * dengan bobot setengah, sehingga kategori yang lebih spesifik didahulukan bila ada.
 */
class CategoryGuesser
{
    /**
     * link: pola pada nama/slug/deskripsi kategori yang menandai kategori itu bertema ini.
     * keywords: pola pada isi dokumen. Kata pendek diberi batas kata (\b) agar tidak cocok
     * di dalam kata lain, mis. "rusa" di "perusahaan" atau "penyu" di "penyuluhan".
     */
    private const THEMES = [
        'tambang' => [
            'link' => '/tambang|mining|mineral|batu ?bara|nikel|emas/iu',
            'keywords' => ['tambang', 'nikel', 'batu ?bara', '\bemas\b', 'timah', '\bpeti\b', '\biup\b', 'galian', 'bauksit', 'smelter'],
        ],
        'hutan' => [
            'link' => '/hutan|forest|kayu|logging|timber|deforest/iu',
            'keywords' => ['hutan', 'pembalakan', '\bkayu\b', 'logging', 'perambahan', 'deforestasi', 'merbau', '\bulin\b', 'meranti', 'penebangan'],
        ],
        'perkebunan' => [
            'link' => '/kebun|sawit|plantation|palm/iu',
            'keywords' => ['sawit', 'perkebunan', 'kebun', '\bhgu\b'],
        ],
        'kebakaran' => [
            'link' => '/bakar|fire|karhutla/iu',
            'keywords' => ['kebakaran', 'karhutla', 'terbakar', 'pembakaran', 'membakar', 'titik api'],
        ],
        'satwa' => [
            'link' => '/satwa|wildlife|fauna|hewan|binatang/iu',
            'keywords' => ['satwa', 'harimau', 'gajah', 'badak', 'orangutan', 'trenggiling', 'rangkong', '\bgading\b', '\bcula\b', '\bpenyu\b', 'dugong', '\brusa\b', 'perburuan', 'dilindungi'],
        ],
        'pencemaran' => [
            'link' => '/cemar|limbah|polusi|pollution|waste|sampah/iu',
            'keywords' => ['limbah', '\bb3\b', 'pencemaran', 'tercemar', 'mencemari', 'sampah', 'plastik', 'polusi', 'tumpahan minyak', 'ikan mati'],
        ],
        'pesisir' => [
            'link' => '/pesisir|laut|kelautan|marine|coast|perikanan|fisher|mangrove/iu',
            'keywords' => ['pesisir', 'mangrove', 'bakau', 'reklamasi', 'terumbu', 'perairan', '\blaut\b', 'nelayan', 'perikanan', 'pulau kecil', '\bikan\b', 'lobster', 'trawl'],
        ],
        'korupsi' => [
            'link' => '/korupsi|corrupt|suap|tipikor|bribery/iu',
            'keywords' => ['korupsi', 'suap', 'gratifikasi', 'pencucian uang', '\btppu\b', 'kerugian negara', '\bkpk\b', 'tipikor'],
        ],
    ];

    /** Kategori payung untuk semua tema lingkungan */
    private const UMBRELLA = '/lingkungan|environment/iu';

    private const NON_ENVIRONMENT_THEMES = ['korupsi'];

    /** Bobot per bagian dokumen */
    private const WEIGHTS = ['title' => 3, 'dugaan_permasalahan' => 2, 'description' => 1, 'perkembangan' => 0.5];

    private ?array $categories = null;

    /**
     * @param  array<string, ?string>  $parsed  hasil CaseDocumentParser
     * @return array{id: int, reason: string}|null
     */
    public function guess(array $parsed): ?array
    {
        $texts = [];
        foreach (self::WEIGHTS as $field => $weight) {
            $texts[$field] = html_entity_decode(strip_tags((string) ($parsed[$field] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Skor tiap tema + kata yang paling sering muncul (untuk alasan)
        $themeScores = [];
        $themeWords = [];
        foreach (self::THEMES as $theme => $config) {
            foreach ($config['keywords'] as $keyword) {
                foreach (self::WEIGHTS as $field => $weight) {
                    if ($texts[$field] !== '' && preg_match_all('/'.$keyword.'/iu', $texts[$field], $m)) {
                        $themeScores[$theme] = ($themeScores[$theme] ?? 0) + $weight * count($m[0]);
                        $word = mb_strtolower($m[0][0]);
                        $themeWords[$theme][$word] = ($themeWords[$theme][$word] ?? 0) + count($m[0]);
                    }
                }
            }
        }

        $best = null;
        foreach ($this->categories() as $id => $category) {
            $score = 0;
            $words = [];

            foreach ($category['themes'] as $theme => $factor) {
                $score += ($themeScores[$theme] ?? 0) * $factor;
                foreach ($themeWords[$theme] ?? [] as $word => $count) {
                    $words[$word] = ($words[$word] ?? 0) + $count;
                }
            }

            // Nama kategori disebut langsung di judul
            if ($category['name'] !== '' && mb_stripos($texts['title'], $category['name']) !== false) {
                $score += 5;
                $words[mb_strtolower($category['name'])] = ($words[mb_strtolower($category['name'])] ?? 0) + 1;
            }

            if ($score > 0 && ($best === null || $score > $best['score'])) {
                arsort($words);
                $best = ['id' => $id, 'score' => $score, 'reason' => implode(', ', array_slice(array_keys($words), 0, 3))];
            }
        }

        return $best ? ['id' => $best['id'], 'reason' => $best['reason']] : null;
    }

    /**
     * Kategori aktif beserta tema yang terhubung (faktor 1 = spesifik, 0.5 = payung).
     *
     * @return array<int, array{name: string, themes: array<string, float>}>
     */
    private function categories(): array
    {
        if ($this->categories !== null) {
            return $this->categories;
        }

        $rows = DB::table('categories')
            ->leftJoin('category_translations', function ($join) {
                $join->on('category_translations.category_id', '=', 'categories.id')->where('category_translations.locale', 'id');
            })
            ->where('categories.is_active', true)
            ->select('categories.id', 'categories.slug', 'category_translations.name', 'category_translations.description')
            ->get();

        $this->categories = [];
        foreach ($rows as $row) {
            // Deskripsi bawaan seeder ("Deskripsi ID untuk …") tidak informatif, jadi nama & slug didahulukan.
            $profile = implode(' ', [$row->name, $row->slug, $row->description]);
            $themes = [];

            foreach (self::THEMES as $theme => $config) {
                if (preg_match($config['link'], $profile)) {
                    $themes[$theme] = 1.0;
                }
            }

            if (preg_match(self::UMBRELLA, $profile)) {
                foreach (array_diff(array_keys(self::THEMES), self::NON_ENVIRONMENT_THEMES) as $theme) {
                    $themes[$theme] ??= 0.5;
                }
            }

            $this->categories[(int) $row->id] = ['name' => (string) $row->name, 'themes' => $themes];
        }

        return $this->categories;
    }
}
