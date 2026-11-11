<?php

namespace App\Services;

use App\Exceptions\TranslationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Klien DeepL API (https://developers.deepl.com/docs).
 *
 * - Teks yang mengandung HTML (hasil TinyMCE) dikirim dengan tag_handling=html
 *   supaya struktur tag tetap utuh; teks biasa (judul, nama) dikirim apa adanya.
 * - DeepL membatasi 50 teks dan 128 KiB per request, jadi input dipecah otomatis.
 */
class DeepLTranslator
{
    private const MAX_TEXTS_PER_REQUEST = 50;

    // Sedikit di bawah batas 128 KiB untuk memberi ruang parameter lain di body.
    private const MAX_BYTES_PER_REQUEST = 120 * 1024;

    private const RETRYABLE_STATUSES = [429, 500, 503, 504, 529];

    public function isConfigured(): bool
    {
        return filled(config('services.deepl.key'));
    }

    /**
     * Apakah nilai ini punya teks yang bisa diterjemahkan (bukan kosong / tag HTML saja).
     */
    public static function hasText(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $text) !== '';
    }

    /**
     * Terjemahkan sekumpulan teks. Key array dipertahankan; nilai tanpa teks
     * dikembalikan apa adanya.
     *
     * @param  array<array-key, string|null>  $texts
     * @return array<array-key, string|null>
     *
     * @throws TranslationException
     */
    public function translate(array $texts, string $sourceLang = 'ID', ?string $targetLang = null): array
    {
        $targetLang ??= config('services.deepl.target_lang', 'EN-US');

        $html = [];
        $plain = [];

        foreach ($texts as $key => $text) {
            if (! self::hasText($text)) {
                continue;
            }

            if (preg_match('/<\/?[a-z][^>]*>/i', $text)) {
                $html[$key] = $text;
            } else {
                $plain[$key] = $text;
            }
        }

        $result = $texts;

        foreach ([[$html, true], [$plain, false]] as [$group, $isHtml]) {
            foreach ($this->chunk($group) as $chunk) {
                $translated = $this->request(array_values($chunk), $sourceLang, $targetLang, $isHtml);

                foreach (array_keys($chunk) as $i => $key) {
                    $result[$key] = $translated[$i];
                }
            }
        }

        return $result;
    }

    /**
     * Pemakaian karakter pada periode tagihan berjalan, atau null bila gagal diambil.
     *
     * @return array{character_count: int, character_limit: int}|null
     */
    public function usage(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get('/v2/usage');
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed() || ! is_numeric($response->json('character_limit'))) {
            return null;
        }

        return [
            'character_count' => (int) $response->json('character_count'),
            'character_limit' => (int) $response->json('character_limit'),
        ];
    }

    /**
     * @param  list<string>  $texts
     * @return list<string>
     */
    private function request(array $texts, string $sourceLang, string $targetLang, bool $html): array
    {
        $payload = [
            'text' => $texts,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
        ];

        if ($html) {
            $payload['tag_handling'] = 'html';
        }

        try {
            $response = $this->client()
                ->retry(
                    [1000, 3000],
                    0,
                    fn ($e) => $e instanceof RequestException
                        && in_array($e->response->status(), self::RETRYABLE_STATUSES, true),
                    throw: false,
                )
                ->post('/v2/translate', $payload);
        } catch (ConnectionException $e) {
            throw new TranslationException('Tidak bisa terhubung ke DeepL: '.$e->getMessage(), 0, $e);
        }

        if ($response->failed()) {
            throw TranslationException::fromStatus($response->status(), $response->json('message'));
        }

        $translations = array_column($response->json('translations') ?? [], 'text');

        if (count($translations) !== count($texts)) {
            throw new TranslationException('Jawaban DeepL tidak lengkap. Coba lagi.');
        }

        return $translations;
    }

    /**
     * Pecah teks menjadi beberapa request sesuai batas jumlah & ukuran DeepL.
     *
     * @param  array<array-key, string>  $texts
     * @return list<array<array-key, string>>
     */
    private function chunk(array $texts): array
    {
        $chunks = [];
        $current = [];
        $bytes = 0;

        foreach ($texts as $key => $text) {
            $size = strlen($text);

            if ($current && (count($current) >= self::MAX_TEXTS_PER_REQUEST || $bytes + $size > self::MAX_BYTES_PER_REQUEST)) {
                $chunks[] = $current;
                $current = [];
                $bytes = 0;
            }

            $current[$key] = $text;
            $bytes += $size;
        }

        if ($current) {
            $chunks[] = $current;
        }

        return $chunks;
    }

    private function client(): PendingRequest
    {
        $key = (string) config('services.deepl.key');
        $baseUrl = str_ends_with($key, ':fx') ? 'https://api-free.deepl.com' : 'https://api.deepl.com';

        return Http::baseUrl($baseUrl)
            ->withHeaders(['Authorization' => 'DeepL-Auth-Key '.$key])
            ->acceptJson()
            ->asJson()
            ->timeout(60);
    }
}
