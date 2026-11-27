<?php

namespace App\Services\CaseImport;

/**
 * Menebak kualifikasi kasus (pidana / perdata / administrasi, lihat CaseModel::KUALIFIKASI) dari isi kasus,
 * untuk kasus yang dokumennya belum punya bagian "Kualifikasi Kasus".
 *
 * Status kasus didahulukan: status yang hanya ada di satu jalur (mis. Penyidikan → pidana, Gugatan → perdata,
 * Sanksi → administrasi) langsung menentukan, dan Persidangan/Vonis tidak mungkin administrasi. Selebihnya
 * dinilai dari kata kunci; judul dan bagian Status/Perkembangan (jalur hukum yang sedang ditempuh) berbobot
 * lebih besar. Tanpa petunjuk sama sekali hasilnya pidana, jalur yang juga dipakai halaman publik bila kosong.
 */
class KualifikasiGuesser
{
    /** Kata pendek diberi batas kata (\b) agar tidak cocok di dalam kata lain. */
    private const KEYWORDS = [
        'pidana' => [
            'tersangka', 'terdakwa', 'terpidana', 'penyidik', 'penyidikan', 'ditangkap', 'penangkapan', 'dituntut',
            'tuntutan', 'jaksa', 'penuntut umum', 'penuntutan', 'divonis', 'pidana penjara', 'hukuman penjara', 'dipenjara',
            'ditahan', 'penahanan', 'kurungan', 'pidana denda', '\bkuhp\b', '\bp-?21\b', 'tangkap tangan', 'praperadilan',
        ],
        'perdata' => [
            'ganti rugi', 'ganti kerugian', 'pemulihan lingkungan', 'citizen lawsuit', 'class action',
            'perbuatan melawan hukum', 'tanggung jawab mutlak', 'strict liability',
        ],
        // Gugatan biasa: perdata, kecuali kasus yang digugat ke PTUN (keputusan/izin pemerintah) → administrasi.
        'gugatan' => ['gugatan', 'menggugat', 'digugat', 'tergugat', 'penggugat'],
        'administrasi' => [
            'sanksi administratif', 'sanksi administrasi', 'paksaan pemerintah', 'pencabutan izin', 'mencabut izin',
            'izin(?:nya)? dicabut', 'pembekuan izin', 'membekukan izin', 'teguran tertulis', 'denda administratif',
            'penyegelan', 'disegel', 'penghentian kegiatan', 'tata usaha negara', '\bptun\b', '\bpttun\b',
        ],
    ];

    /** Gugatan praperadilan termasuk proses pidana, bukan gugatan perdata. */
    private const PRETRIAL = '/gugatan\s+pra\s?peradilan/iu';

    /** Kasus yang gugatannya diajukan ke peradilan tata usaha negara */
    private const ADMINISTRATIVE_COURT = '/tata usaha negara|\bp?t?tun\b/iu';

    /** Bobot per bagian kasus */
    private const WEIGHTS = ['title' => 4, 'status' => 2, 'perkembangan' => 1.5, 'dugaan_permasalahan' => 1, 'description' => 1];

    /** Status (key tabel statuses) yang hanya ada di satu jalur */
    private const STATUS_TRACK = [
        'penyelidikan' => 'pidana', 'investigation' => 'pidana', 'penyidikan' => 'pidana', 'prosecution' => 'pidana',
        'gugatan' => 'perdata', 'pembuktian' => 'perdata',
        'sanksi' => 'administrasi',
    ];

    /** Status yang ada di jalur pidana & perdata, tidak di administrasi */
    private const COURT_STATUSES = ['trial', 'vonis', 'berkekuatan-hukum-tetap'];

    /**
     * @param  array<string, ?string>  $texts  title, status, perkembangan, dugaan_permasalahan, description (boleh HTML)
     * @param  string|null  $statusKey  key status kasus (tabel statuses)
     * @return array{kualifikasi: string, reason: string}
     */
    public function guess(array $texts, ?string $statusKey = null): array
    {
        if (isset(self::STATUS_TRACK[$statusKey])) {
            return ['kualifikasi' => self::STATUS_TRACK[$statusKey], 'reason' => "status {$statusKey}"];
        }

        $texts = array_map(
            fn ($field) => html_entity_decode(strip_tags((string) ($texts[$field] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            array_combine(array_keys(self::WEIGHTS), array_keys(self::WEIGHTS)),
        );
        // Gugatan dihitung administrasi bila kasusnya menyebut PTUN dan tidak ada tanda gugatan perdata (ganti rugi dsb.).
        $all = implode("\n", $texts);
        $lawsuitTrack = preg_match(self::ADMINISTRATIVE_COURT, $all) && ! preg_match('/'.implode('|', self::KEYWORDS['perdata']).'/iu', $all)
            ? 'administrasi' : 'perdata';

        $scores = ['pidana' => 0, 'perdata' => 0, 'administrasi' => 0];
        $words = [];
        foreach (self::WEIGHTS as $field => $weight) {
            $text = preg_replace(self::PRETRIAL, '', $texts[$field]);
            if ($text === '') {
                continue;
            }

            foreach (self::KEYWORDS as $group => $keywords) {
                $track = $group === 'gugatan' ? $lawsuitTrack : $group;
                foreach ($keywords as $keyword) {
                    $count = preg_match_all('/'.$keyword.'/iu', $text, $m);
                    if ($count) {
                        $scores[$track] += $weight * $count;
                        $word = mb_strtolower($m[0][0]);
                        $words[$track][$word] = ($words[$track][$word] ?? 0) + $count;
                    }
                }
            }
        }

        if (in_array($statusKey, self::COURT_STATUSES, true)) {
            unset($scores['administrasi']);
        }

        arsort($scores);
        $track = array_key_first($scores);
        if ($scores[$track] <= 0) {
            return ['kualifikasi' => 'pidana', 'reason' => 'tidak ada petunjuk'];
        }

        arsort($words[$track]);

        return ['kualifikasi' => $track, 'reason' => implode(', ', array_slice(array_keys($words[$track]), 0, 3))];
    }
}
