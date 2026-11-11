<?php

namespace App\Services\CaseImport;

use Illuminate\Support\Str;

/**
 * Memetakan paragraf dokumen kasus (template Green Defender) ke field kasus.
 *
 * Bentuk dokumen yang dikenali:
 *   Judul (paragraf pertama)
 *   Label: nilai            ← Pengungkap/Pelapor/Penggugat, Terlapor, Penyidik, Lokasi, Tahun/Periode, Status
 *   Kronologi               ← judul bagian, diikuti paragraf isinya
 *   Perkembangan / Dugaan Permasalahan / Lesson Learning / Status / Sumber
 */
class CaseDocumentParser
{
    /** Judul bagian → field kasus */
    private const SECTIONS = [
        'kronologi' => 'description',
        'perkembangan' => 'perkembangan',
        'dugaan permasalahan' => 'dugaan_permasalahan',
        'lesson learning' => 'pembelajaran',
        'lesson learned' => 'pembelajaran',
        'pembelajaran' => 'pembelajaran',
        'status' => 'status',
        'sumber' => 'sumber',
        'referensi' => 'sumber',
    ];

    /** Label "X: nilai" di kepala dokumen → field */
    private const LABELS = [
        'pengungkap' => 'pelapor',
        'pengungkap awal' => 'pelapor',
        'pelapor' => 'pelapor',
        'penggugat' => 'pelapor',
        'terlapor' => 'terlapor',
        'tergugat' => 'terlapor',
        'penyidik' => 'instansi',
        'penegak hukum' => 'instansi',
        'penegak hukum/pengelola' => 'instansi',
        'lokasi' => 'lokasi',
        'tahun' => 'tahun',
        'periode' => 'tahun',
        'status' => 'status',
    ];

    private const MONTHS = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
        'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    /**
     * @param  list<array{text: string, html: string, style: ?string, list: ?string}>  $paragraphs
     * @return array{
     *     title: string, pelapor: ?string, terlapor: ?string, instansi: ?string, lokasi: ?string, tahun: ?string,
     *     description: ?string, perkembangan: ?string, dugaan_permasalahan: ?string, pembelajaran: ?string,
     *     status: ?string, sumber: ?string, event_date: ?string, sections: list<string>, warnings: list<string>
     * }
     */
    public function parse(array $paragraphs): array
    {
        $warnings = [];
        $title = trim($paragraphs[0]['text'] ?? '');
        $labels = [];
        $sections = [];
        $current = null;

        foreach (array_slice($paragraphs, 1) as $paragraph) {
            $heading = $this->heading($paragraph['text']);
            if ($heading !== null) {
                $current = $heading;
                $sections[$current] ??= [];

                continue;
            }

            // "Status: ..." bisa muncul di kepala dokumen maupun di akhir bagian lain.
            if (preg_match('/^([^:]{2,40}):\s*(.+)$/u', $paragraph['text'], $m)) {
                $field = self::LABELS[Str::lower(trim($m[1]))] ?? null;

                if ($field !== null && ($current === null || $field === 'status')) {
                    $labels[$field][] = trim($m[2]);

                    continue;
                }

                if ($current === null) {
                    $warnings[] = "Label \"{$m[1]}\" tidak dikenali dan dilewati.";

                    continue;
                }
            }

            if ($current === null) {
                $warnings[] = 'Paragraf sebelum bagian "Kronologi" dilewati: '.Str::limit($paragraph['text'], 60);

                continue;
            }

            $sections[$current][] = $paragraph;
        }

        $html = array_map(fn (array $blocks) => $this->toHtml($blocks), $sections);
        $text = array_map(fn (array $blocks) => implode("\n", array_column($blocks, 'text')), $sections);

        // Status dari label "Status:" digabung dengan bagian "Status" (bila ada keduanya).
        $statusHtml = trim(implode('', array_map(fn ($s) => '<p>'.e($s).'</p>', $labels['status'] ?? [])).($html['status'] ?? ''));

        $result = [
            'title' => Str::limit($title, 255, ''),
            'pelapor' => $this->joined($labels['pelapor'] ?? [], 'Pelapor', $warnings),
            'terlapor' => $this->joined($labels['terlapor'] ?? [], 'Terlapor', $warnings),
            'instansi' => isset($labels['instansi']) ? '<p>'.e(implode('; ', $labels['instansi'])).'</p>' : null,
            'lokasi' => isset($labels['lokasi']) ? implode('; ', $labels['lokasi']) : null,
            'tahun' => isset($labels['tahun']) ? implode('; ', $labels['tahun']) : null,
            'description' => $html['description'] ?? null,
            'perkembangan' => $html['perkembangan'] ?? null,
            'dugaan_permasalahan' => $html['dugaan_permasalahan'] ?? null,
            'pembelajaran' => $html['pembelajaran'] ?? null,
            'status' => $statusHtml !== '' ? $statusHtml : null,
            'sumber' => $html['sumber'] ?? null,
            'sections' => array_keys(array_filter($html)),
            'warnings' => $warnings,
        ];

        if ($title === '') {
            $result['warnings'][] = 'Judul kosong.';
        }
        if (empty($result['description'])) {
            $result['warnings'][] = 'Bagian "Kronologi" tidak ditemukan.';
        }

        $result['event_date'] = $this->eventDate($result['tahun'], $text['description'] ?? '');
        if ($result['event_date'] === null) {
            $result['warnings'][] = 'Tanggal kejadian tidak ditemukan; isi manual.';
        }

        return $result;
    }

    /**
     * Tanggal kejadian: label Tahun/Periode lebih diutamakan; tanggal lengkap pertama di
     * Kronologi dipakai bila tahunnya sama (atau bila tidak ada label tahun).
     */
    public function eventDate(?string $tahun, string $chronology): ?string
    {
        $year = $tahun !== null && preg_match('/\b(19|20)\d{2}\b/', $tahun, $m) ? (int) $m[0] : null;
        $months = implode('|', array_keys(self::MONTHS));

        preg_match_all("/\b(\d{1,2})\s+({$months})\s+((?:19|20)\d{2})\b/iu", $chronology, $dates, PREG_SET_ORDER);
        foreach ($dates as $d) {
            if ($year === null || (int) $d[3] === $year) {
                return sprintf('%04d-%02d-%02d', $d[3], self::MONTHS[Str::lower($d[2])], $d[1]);
            }
        }

        if ($year !== null) {
            return sprintf('%04d-01-01', $year);
        }

        if (preg_match("/\b({$months})\s+((?:19|20)\d{2})\b/iu", $chronology, $m)) {
            return sprintf('%04d-%02d-01', $m[2], self::MONTHS[Str::lower($m[1])]);
        }

        if (preg_match('/\b(19|20)\d{2}\b/', $chronology, $m)) {
            return sprintf('%04d-01-01', $m[0]);
        }

        return null;
    }

    private function heading(string $text): ?string
    {
        $key = Str::lower(trim(rtrim(trim($text), ':')));

        return self::SECTIONS[$key] ?? null;
    }

    /**
     * Kolom pelapor/terlapor bertipe VARCHAR(255).
     */
    private function joined(array $values, string $label, array &$warnings): ?string
    {
        if ($values === []) {
            return null;
        }

        $value = implode('; ', $values);
        if (mb_strlen($value) > 255) {
            $warnings[] = "{$label} lebih dari 255 karakter dan dipotong.";
            $value = Str::limit($value, 252);
        }

        return $value;
    }

    /**
     * @param  list<array{text: string, html: string, style: ?string, list: ?string}>  $blocks
     */
    private function toHtml(array $blocks): ?string
    {
        $html = '';
        $openList = null;

        foreach ($blocks as $block) {
            $list = $block['list'];
            $content = $block['html'];

            // Penomoran yang diketik manual ("1.  Sumber ...") dijadikan list bernomor.
            if ($list === null && preg_match('/^\d{1,2}[.)]\s+(.+)$/su', $block['text'], $m)) {
                $list = 'ol';
                $content = DocxReader::linkify(e($m[1]));
            }

            if ($list !== $openList) {
                $html .= $openList ? "</{$openList}>" : '';
                $html .= $list ? "<{$list}>" : '';
                $openList = $list;
            }

            $html .= $list ? "<li>{$content}</li>" : "<p>{$content}</p>";
        }

        $html .= $openList ? "</{$openList}>" : '';

        return $html !== '' ? $html : null;
    }
}
