<?php

namespace App\Services\CaseImport;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mencocokkan teks "Lokasi:" dari dokumen ke wilayah administrasi di PostGIS
 * (tabel yang sama dengan pencarian lokasi di form kasus) untuk mendapatkan titik peta.
 *
 * Tabel memakai nama berbahasa Inggris untuk arah mata angin ("South Sulawesi",
 * "East Luwu", "West Mendo", "Padang City"), sedangkan dokumen berbahasa Indonesia,
 * jadi nama diterjemahkan dulu. Pencarian dimulai dari desa, lalu kecamatan,
 * kabupaten/kota, dan terakhir provinsi; titik untuk wilayah di atas desa adalah
 * rata-rata titik desanya. Daftar kecamatan/kabupaten/provinsi diambil sekali lalu
 * di-cache (lihat areas()).
 */
class LocationMatcher
{
    private const TABLE = 'proteus.POLITICAL_LEVEL_6_dissolved';

    private const PROVINCES = [
        'Kepulauan Bangka Belitung' => 'Bangka Belitung Islands',
        'Bangka Belitung' => 'Bangka Belitung Islands',
        'Nusa Tenggara Timur' => 'East Nusa Tenggara',
        'Nusa Tenggara Barat' => 'West Nusa Tenggara',
        'Papua Barat Daya' => 'Southwest Papua',
        'Papua Pegunungan' => 'Papua Pegunungan',
        'Kalimantan Tengah' => 'Central Kalimantan',
        'Kalimantan Timur' => 'East Kalimantan',
        'Kalimantan Selatan' => 'South Kalimantan',
        'Kalimantan Barat' => 'West Kalimantan',
        'Kalimantan Utara' => 'North Kalimantan',
        'Sulawesi Tenggara' => 'Southeast Sulawesi',
        'Sulawesi Tengah' => 'Central Sulawesi',
        'Sulawesi Selatan' => 'South Sulawesi',
        'Sulawesi Barat' => 'West Sulawesi',
        'Sulawesi Utara' => 'North Sulawesi',
        'Sumatera Selatan' => 'South Sumatra',
        'Sumatera Barat' => 'West Sumatra',
        'Sumatera Utara' => 'North Sumatra',
        'Kepulauan Riau' => 'Riau Islands',
        'DI Yogyakarta' => 'D I Yogyakarta',
        'Yogyakarta' => 'D I Yogyakarta',
        'DKI Jakarta' => 'DKI Jakarta',
        'Jakarta' => 'DKI Jakarta',
        'Jawa Tengah' => 'Central Java',
        'Jawa Timur' => 'East Java',
        'Jawa Barat' => 'West Java',
        'Papua Tengah' => 'Central Papua',
        'Papua Selatan' => 'South Papua',
        'Papua Barat' => 'West Papua',
        'Maluku Utara' => 'North Maluku',
        'Gorontalo' => 'Gorontalo',
        'Bengkulu' => 'Bengkulu',
        'Lampung' => 'Lampung',
        'Banten' => 'Banten',
        'Maluku' => 'Maluku',
        'Papua' => 'Papua',
        'Jambi' => 'Jambi',
        'Riau' => 'Riau',
        'Aceh' => 'Aceh',
        'Bali' => 'Bali',
        // Singkatan yang lazim dipakai di dokumen
        'Kalbar' => 'West Kalimantan', 'Kalteng' => 'Central Kalimantan', 'Kaltim' => 'East Kalimantan',
        'Kalsel' => 'South Kalimantan', 'Kaltara' => 'North Kalimantan',
        'Sulsel' => 'South Sulawesi', 'Sulteng' => 'Central Sulawesi', 'Sultra' => 'Southeast Sulawesi',
        'Sulut' => 'North Sulawesi', 'Sulbar' => 'West Sulawesi',
        'Sumut' => 'North Sumatra', 'Sumbar' => 'West Sumatra', 'Sumsel' => 'South Sumatra',
        'Jabar' => 'West Java', 'Jateng' => 'Central Java', 'Jatim' => 'East Java',
        'NTB' => 'West Nusa Tenggara', 'NTT' => 'East Nusa Tenggara', 'DIY' => 'D I Yogyakarta',
        'Babel' => 'Bangka Belitung Islands', 'Kepri' => 'Riau Islands', 'Malut' => 'North Maluku',
    ];

    private const DIRECTIONS = [
        'Barat Daya' => 'Southwest', 'Barat Laut' => 'Northwest', 'Timur Laut' => 'Northeast',
        'Tenggara' => 'Southeast', 'Timur' => 'East', 'Barat' => 'West',
        'Selatan' => 'South', 'Utara' => 'North', 'Tengah' => 'Central',
    ];

    /**
     * Awalan bentang alam yang dibuang sebelum mencoba nama sebagai wilayah.
     * "Selat"/"Laut" sengaja tidak dibuang: "Selat Malaka" bukan Kabupaten Malaka.
     */
    private const LANDMARK_PREFIX = '/^(teluk|pulau|perairan|pelabuhan|hulu|hutan lindung|hutan|taman nasional|daerah aliran sungai|das|sungai|kali|gunung|kawasan|provinsi)\s+/iu';

    private ?array $areas = null;

    /**
     * @return array{label: string, lat: float, lng: float, precision: string}|null
     */
    public function match(?string $location): ?array
    {
        if ($location === null || trim($location) === '') {
            return null;
        }

        try {
            // "Laut Aru; Tual, Maluku": bagian pertama yang bisa dipetakan yang dipakai.
            foreach ($this->parts($location) as $part) {
                if ($hit = $this->search($this->parse($part))) {
                    return $hit;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('LocationMatcher: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Pisahkan beberapa lokasi: "A; B" atau rute "A – B".
     *
     * @return list<string>
     */
    public function parts(string $location): array
    {
        $parts = preg_split('/\s*;\s*|\s+[–—-]\s+|(?<=\p{L})–(?=\p{L})/u', $location);

        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * Uraikan satu lokasi menjadi kandidat nama per tingkat wilayah (beserta varian ejaan tabel).
     *
     * @return array{villages: list<string>, districts: list<string>, regencies: list<string>, plain: list<string>, province: ?string}
     */
    public function parse(string $location): array
    {
        $segments = array_values(array_filter(array_map('trim', preg_split('/[,()\/]/u', $location))));
        $parsed = ['villages' => [], 'districts' => [], 'regencies' => [], 'plain' => [], 'province' => null];

        // "Klangenan dan Desa Penguragan" → ["Klangenan", "Desa Penguragan"]
        $pieces = array_merge(...array_map(fn ($s) => preg_split('/\s+dan\s+|^dan\s+/iu', $s), $segments));

        foreach (array_filter(array_map('trim', $pieces)) as $piece) {
            // Awalan wilayah boleh muncul di tengah: "Hutan Lindung Desa Lariang", "Kawasan hutan Kabupaten Katingan".
            if (preg_match('/\b(?:desa|kelurahan|kampung|gampong|nagari)\s+(.+)$/iu', $piece, $m)) {
                $parsed['villages'] = $parsed['villages'] ?: $this->variants(trim($m[1]));
            } elseif (preg_match('/\b(?:kecamatan|distrik)\s+(.+)$/iu', $piece, $m)) {
                $parsed['districts'] = $parsed['districts'] ?: $this->variants(trim($m[1]));
            } elseif (preg_match('/\b(kabupaten|kota)\s+(.+)$/iu', $piece, $m)) {
                $parsed['regencies'] = array_merge($parsed['regencies'], $this->variants(trim($m[2]), regency: true, city: strtolower($m[1]) === 'kota'));
            } elseif ($province = $this->province($piece)) {
                $parsed['province'] ??= $province;
            } else {
                // "perairan Pulau Rupat" → "Rupat"
                do {
                    $before = $piece;
                    $piece = trim(preg_replace(self::LANDMARK_PREFIX, '', $piece));
                } while ($piece !== $before);

                if (mb_strlen($piece) >= 3) {
                    $parsed['plain'][] = $piece;
                }
            }
        }

        return $parsed;
    }

    private function search(array $p): ?array
    {
        $regencies = $p['regencies'];
        $plainRegencies = $p['plain'] ? array_merge(...array_map(fn ($s) => $this->variants($s, regency: true), $p['plain'])) : [];
        $province = $p['province'];

        // 1. Desa (bila nama kabupaten tidak cocok, cukup dibatasi kecamatan/provinsi)
        if ($p['villages']) {
            $villages = $this->villages($p['villages']);
            $regencyFilters = array_filter([$regencies ?: $plainRegencies, ($p['districts'] || $province) ? [] : null], 'is_array');

            foreach ($regencyFilters as $names) {
                foreach ($villages as $v) {
                    if ((! $p['districts'] || $this->inList($v->LEVEL_5, $p['districts']))
                        && (! $names || $this->inList($v->LEVEL_4, $names))
                        && ($province === null || $v->LEVEL_3 === $province)) {
                        return $this->result([$v->LEVEL_6, $v->LEVEL_5, $v->LEVEL_4, $v->LEVEL_3], $v->latitude, $v->longtitude, 'desa');
                    }
                }
            }
        }

        // 2. Kecamatan & kabupaten/kota yang disebut eksplisit ("Kecamatan X", "Kabupaten Y")
        $constraint = $regencies ?: $plainRegencies;
        if ($p['districts']) {
            $hit = $this->average('district', $p['districts'], $constraint, $province, 'kecamatan')
                ?? ($constraint ? $this->average('district', $p['districts'], [], $province, 'kecamatan') : null);
            if ($hit) {
                return $hit;
            }
        }

        if ($regencies) {
            // "Alang-Alang Lebar, Kota Palembang": nama polos dicari dulu sebagai kecamatan di kota tsb.
            foreach ($p['plain'] as $name) {
                if ($hit = $this->average('district', $this->variants($name), $regencies, $province, 'kecamatan')) {
                    return $hit;
                }
            }

            // Provinsi di dokumen bisa memakai nama lama (mis. Merauke kini di Papua Selatan).
            $hit = $this->average('regency', $regencies, [], $province, 'kabupaten')
                ?? ($province ? $this->average('regency', $regencies, [], null, 'kabupaten') : null);
            if ($hit) {
                return $hit;
            }
        }

        // 3. Nama tanpa awalan ("Pandeglang", "Samarinda Utara"): coba sebagai kabupaten/kota, lalu kecamatan
        foreach ($p['plain'] as $name) {
            $hit = $this->average('regency', $this->variants($name, regency: true), [], $province, 'kabupaten')
                ?? $this->average('district', $this->variants($name), [], $province, 'kecamatan');
            if ($hit) {
                return $hit;
            }
        }

        // 4. Provinsi
        if ($province) {
            return $this->average('province', [$province], [], null, 'provinsi');
        }

        return null;
    }

    /**
     * Titik rata-rata (berbobot jumlah desa) wilayah bernama salah satu $names. Nama dicoba
     * berurutan (mis. "Gorontalo City" sebelum "Gorontalo"); bila satu nama dipakai beberapa
     * wilayah, dipilih yang desanya paling banyak.
     */
    private function average(string $level, array $names, array $regencies, ?string $province, string $precision): ?array
    {
        $levels = ['district', 'regency', 'province'];
        $group = array_slice($levels, array_search($level, $levels));
        $regencyKeys = array_map(fn ($n) => $this->key($n), $regencies);

        foreach (array_unique(array_map(fn ($n) => $this->key($n), $names)) as $nameKey) {
            $matches = [];

            foreach ($this->areas() as $area) {
                if ($area["{$level}_key"] !== $nameKey
                    || ($regencyKeys && ! in_array($area['regency_key'], $regencyKeys, true))
                    || ($province !== null && $area['province'] !== $province)) {
                    continue;
                }

                $key = implode('|', array_map(fn ($l) => $area[$l], $group));
                $matches[$key] ??= ['parts' => array_map(fn ($l) => $area[$l], $group), 'lat' => 0.0, 'lng' => 0.0, 'n' => 0];
                $matches[$key]['lat'] += $area['lat'] * $area['n'];
                $matches[$key]['lng'] += $area['lng'] * $area['n'];
                $matches[$key]['n'] += $area['n'];
            }

            if ($matches) {
                usort($matches, fn ($a, $b) => $b['n'] <=> $a['n']);
                $best = $matches[0];

                return $this->result($best['parts'], $best['lat'] / $best['n'], $best['lng'] / $best['n'], $precision);
            }
        }

        return null;
    }

    /**
     * Ringkasan per kecamatan (±7.300 baris) disimpan di cache, sehingga pencocokan
     * kecamatan/kabupaten/provinsi tidak perlu memindai tabel desa berulang kali.
     *
     * @return list<array<string, mixed>>
     */
    private function areas(): array
    {
        return $this->areas ??= Cache::remember('location-matcher:areas:v2', now()->addDay(), function () {
            return DB::connection('pgsql')->table(self::TABLE)
                ->selectRaw('"LEVEL_3", "LEVEL_4", "LEVEL_5", AVG(latitude) AS lat, AVG(longtitude) AS lng, COUNT(*) AS n')
                ->groupBy('LEVEL_3', 'LEVEL_4', 'LEVEL_5')
                ->get()
                ->map(fn ($r) => [
                    'province' => (string) $r->LEVEL_3,
                    'regency' => (string) $r->LEVEL_4,
                    'district' => (string) $r->LEVEL_5,
                    'province_key' => $this->key((string) $r->LEVEL_3),
                    'regency_key' => $this->key((string) $r->LEVEL_4),
                    'district_key' => $this->key((string) $r->LEVEL_5),
                    'lat' => (float) $r->lat,
                    'lng' => (float) $r->lng,
                    'n' => (int) $r->n,
                ])
                ->all();
        });
    }

    /**
     * Desa bernama salah satu $names. Memakai index trigram kolom NAME ("[Desa][Kecamatan][Kabupaten]…");
     * spasi/tanda hubung boleh berbeda ("Warung Banten" = "Warungbanten").
     */
    private function villages(array $names): array
    {
        $alternatives = array_map(function ($name) {
            $words = preg_split('/[\s-]+/u', trim($name));

            return implode('[ -]?', array_map(fn ($w) => preg_replace('/[\\\\.^$|?*+()\[\]{}]/', '\\\\$0', $w), $words));
        }, $names);

        return DB::connection('pgsql')->table(self::TABLE)
            ->whereRaw('"NAME" ~* ?', ['^\[('.implode('|', $alternatives).')\]'])
            ->select('LEVEL_6', 'LEVEL_5', 'LEVEL_4', 'LEVEL_3', 'latitude', 'longtitude')
            ->limit(50)
            ->get()
            ->all();
    }

    /**
     * Nama dalam bentuk dokumen dan bentuk tabel: "Mendo Barat" → ["Mendo Barat", "West Mendo"],
     * "Seram Bagian Timur" → [..., "East Seram"]; untuk kabupaten ditambah "X City" (didahulukan bila kota).
     *
     * @return list<string>
     */
    private function variants(string $name, bool $regency = false, bool $city = false): array
    {
        $names = [$name];
        $base = preg_replace('/\s+bagian(?=\s)/iu', '', $name);
        $names[] = $base;

        foreach (self::DIRECTIONS as $id => $en) {
            if (preg_match('/^(.+)\s+'.$id.'$/iu', $base, $m)) {
                $names[] = "{$en} {$m[1]}";
                break;
            }
        }

        $names = array_values(array_unique($names));
        if (! $regency) {
            return $names;
        }

        $cities = array_map(fn ($n) => "{$n} City", $names);

        return $city ? [...$cities, ...$names] : [...$names, ...$cities];
    }

    private function province(string $segment): ?string
    {
        $segment = trim(preg_replace('/^provinsi\s+/iu', '', $segment));

        foreach (self::PROVINCES as $id => $en) {
            if (strcasecmp($segment, $id) === 0) {
                return $en;
            }
        }

        return null;
    }

    /**
     * Kunci pembanding: huruf kecil tanpa spasi/tanda baca ("Palangka Raya" = "Palangkaraya").
     */
    private function key(string $name): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($name));
    }

    private function inList(?string $value, array $names): bool
    {
        return $value !== null && in_array($this->key($value), array_map(fn ($n) => $this->key($n), $names), true);
    }

    private function result(array $parts, $lat, $lng, string $precision): array
    {
        return [
            'label' => implode(', ', array_filter($parts)),
            'lat' => round((float) $lat, 7),
            'lng' => round((float) $lng, 7),
            'precision' => $precision,
        ];
    }
}
