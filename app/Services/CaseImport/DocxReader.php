<?php

namespace App\Services\CaseImport;

use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use ZipArchive;

/**
 * Membaca isi .docx menjadi daftar paragraf sederhana.
 *
 * Setiap paragraf berisi teks polos, HTML inline (tebal, miring, tautan), jenis daftar bila paragraf itu
 * bagian dari list bernomor/berbutir Word, dan letak selnya bila paragraf ada di dalam tabel.
 * Isi dropdown Google Docs (content control w:sdt) ikut terbaca sebagai teks biasa.
 */
class DocxReader
{
    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private const R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @return list<array{text: string, html: string, style: ?string, list: ?string, cell: ?array{row: string, col: int}}>
     */
    public function read(string $binary): array
    {
        $parts = $this->unzip($binary);

        $document = $this->load($parts['word/document.xml'] ?? null)
            ?? throw new InvalidArgumentException('Berkas bukan dokumen Word (.docx) yang valid.');

        $listTypes = $this->listTypes($this->load($parts['word/numbering.xml'] ?? null));
        $links = $this->hyperlinks($parts['word/_rels/document.xml.rels'] ?? null);

        $xpath = $this->xpath($document);
        $paragraphs = [];

        foreach ($xpath->query('//w:body//w:p') as $p) {
            $segments = $this->segments($xpath, $p, $links);
            $text = trim(implode('', array_column($segments, 'text')));

            if ($text === '') {
                continue;
            }

            $numId = $xpath->evaluate('string(w:pPr/w:numPr/w:numId/@w:val)', $p);
            $level = $xpath->evaluate('string(w:pPr/w:numPr/w:ilvl/@w:val)', $p) ?: '0';
            $cell = $xpath->query('ancestor::w:tc[1]', $p)->item(0);

            $paragraphs[] = [
                'text' => $text,
                'html' => trim($this->render($segments)),
                'style' => $xpath->evaluate('string(w:pPr/w:pStyle/@w:val)', $p) ?: null,
                'list' => ($numId !== '' && $numId !== '0') ? ($listTypes[$numId][$level] ?? 'ol') : null,
                // Sel tabel: baris (path node, unik per baris) dan nomor kolomnya, agar tabel bisa dibaca per baris.
                'cell' => $cell ? [
                    'row' => $cell->parentNode->getNodePath(),
                    'col' => (int) $xpath->evaluate('count(preceding-sibling::w:tc)', $cell),
                ] : null,
            ];
        }

        return $paragraphs;
    }

    /**
     * @return array<string, string>
     */
    private function unzip(string $binary): array
    {
        $path = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($path, $binary);

        try {
            $zip = new ZipArchive;
            if ($zip->open($path) !== true) {
                throw new InvalidArgumentException('Berkas bukan dokumen Word (.docx) yang valid.');
            }

            $parts = [];
            foreach (['word/document.xml', 'word/numbering.xml', 'word/_rels/document.xml.rels'] as $name) {
                $content = $zip->getFromName($name);
                if ($content !== false) {
                    $parts[$name] = $content;
                }
            }
            $zip->close();

            return $parts;
        } finally {
            @unlink($path);
        }
    }

    private function load(?string $xml): ?DOMDocument
    {
        if ($xml === null) {
            return null;
        }

        $dom = new DOMDocument;

        return $dom->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT) ? $dom : null;
    }

    private function xpath(DOMDocument $dom): DOMXPath
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::W);
        $xpath->registerNamespace('r', self::R);

        return $xpath;
    }

    /**
     * numId → level → 'ol' | 'ul'
     *
     * @return array<string, array<string, string>>
     */
    private function listTypes(?DOMDocument $numbering): array
    {
        if (! $numbering) {
            return [];
        }

        $xpath = $this->xpath($numbering);
        $abstract = [];

        foreach ($xpath->query('//w:abstractNum') as $node) {
            $levels = [];
            foreach ($xpath->query('w:lvl', $node) as $lvl) {
                $format = $xpath->evaluate('string(w:numFmt/@w:val)', $lvl);
                $levels[$lvl->getAttributeNS(self::W, 'ilvl')] = $format === 'bullet' ? 'ul' : 'ol';
            }
            $abstract[$node->getAttributeNS(self::W, 'abstractNumId')] = $levels;
        }

        $types = [];
        foreach ($xpath->query('//w:num') as $num) {
            $abstractId = $xpath->evaluate('string(w:abstractNumId/@w:val)', $num);
            $types[$num->getAttributeNS(self::W, 'numId')] = $abstract[$abstractId] ?? [];
        }

        return $types;
    }

    /**
     * rId → URL tautan eksternal
     *
     * @return array<string, string>
     */
    private function hyperlinks(?string $rels): array
    {
        $dom = $this->load($rels);
        if (! $dom) {
            return [];
        }

        $links = [];
        foreach ($dom->getElementsByTagName('Relationship') as $rel) {
            if (str_ends_with($rel->getAttribute('Type'), '/hyperlink')) {
                $links[$rel->getAttribute('Id')] = $rel->getAttribute('Target');
            }
        }

        return $links;
    }

    /**
     * @return list<array{text: string, bold: bool, italic: bool, href: ?string}>
     */
    private function segments(DOMXPath $xpath, DOMElement $p, array $links): array
    {
        $segments = [];

        foreach ($xpath->query('w:r | w:hyperlink/w:r | w:ins/w:r | w:smartTag/w:r | w:sdt/w:sdtContent/w:r | w:sdt/w:sdtContent/w:hyperlink/w:r', $p) as $run) {
            $href = null;
            if ($run->parentNode instanceof DOMElement && $run->parentNode->localName === 'hyperlink') {
                $href = $links[$run->parentNode->getAttributeNS(self::R, 'id')] ?? null;
            }

            $text = '';
            foreach ($run->childNodes as $child) {
                $text .= match ($child->localName) {
                    't' => $child->textContent,
                    'tab' => ' ',
                    'br', 'cr' => "\n",
                    default => '',
                };
            }

            if ($text === '') {
                continue;
            }

            $segments[] = [
                'text' => $text,
                'bold' => $this->isOn($xpath, $run, 'b'),
                'italic' => $this->isOn($xpath, $run, 'i'),
                'href' => $href,
            ];
        }

        return $segments;
    }

    private function isOn(DOMXPath $xpath, DOMElement $run, string $property): bool
    {
        $node = $xpath->query("w:rPr/w:{$property}", $run)->item(0);

        return $node instanceof DOMElement
            && ! in_array($node->getAttributeNS(self::W, 'val'), ['0', 'false', 'none'], true);
    }

    /**
     * Gabungkan segmen berformat sama lalu ubah menjadi HTML; URL polos dijadikan tautan.
     *
     * @param  list<array{text: string, bold: bool, italic: bool, href: ?string}>  $segments
     */
    private function render(array $segments): string
    {
        $merged = [];
        foreach ($segments as $segment) {
            $last = array_key_last($merged);
            if ($last !== null
                && $merged[$last]['bold'] === $segment['bold']
                && $merged[$last]['italic'] === $segment['italic']
                && $merged[$last]['href'] === $segment['href']) {
                $merged[$last]['text'] .= $segment['text'];
            } else {
                $merged[] = $segment;
            }
        }

        $html = '';
        foreach ($merged as $segment) {
            $part = $segment['href']
                ? '<a href="'.e($segment['href']).'" target="_blank" rel="noopener">'.e($segment['text']).'</a>'
                : self::linkify(e($segment['text']));
            $part = nl2br($part, false);

            if ($segment['italic']) {
                $part = "<em>{$part}</em>";
            }
            if ($segment['bold']) {
                $part = "<strong>{$part}</strong>";
            }

            $html .= $part;
        }

        return $html;
    }

    /**
     * Ubah URL polos pada teks yang sudah di-escape menjadi tautan.
     */
    public static function linkify(string $escaped): string
    {
        return preg_replace_callback('~https?://[^\s<>"]+~i', function ($match) {
            $url = rtrim($match[0], '.,;:!?)');
            $trailing = substr($match[0], strlen($url));

            return '<a href="'.$url.'" target="_blank" rel="noopener">'.$url.'</a>'.$trailing;
        }, $escaped);
    }
}
