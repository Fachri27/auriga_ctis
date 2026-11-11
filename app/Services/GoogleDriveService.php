<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Akses baca Google Drive API v3 untuk impor dokumen kasus.
 *
 * Autentikasi (salah satu):
 * - GOOGLE_DRIVE_API_KEY: untuk folder yang dibagikan "Siapa saja yang memiliki link".
 * - Service account (GOOGLE_SERVICE_ACCOUNT_JSON): folder dibagikan ke email service account.
 */
class GoogleDriveService
{
    public const GOOGLE_DOC = 'application/vnd.google-apps.document';

    public const DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    // Kode exception untuk pembatasan sementara oleh Google (halaman "Sorry…" / rate limit): cukup tunggu lalu ulangi.
    public const THROTTLED = 429;

    public function isConfigured(): bool
    {
        return filled(config('services.google_drive.api_key'))
            || is_file((string) config('services.google_drive.credentials'));
    }

    /**
     * Ambil ID folder dari link Drive (…/folders/<id>, …?id=<id>) atau ID mentah.
     */
    public static function folderId(string $input): ?string
    {
        $input = trim($input);

        foreach (['~/folders/([A-Za-z0-9_-]{10,})~', '~[?&]id=([A-Za-z0-9_-]{10,})~', '~^([A-Za-z0-9_-]{10,})$~'] as $pattern) {
            if (preg_match($pattern, $input, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * Google Docs dan file .docx di dalam folder, diurutkan natural berdasarkan nama ("2. …" sebelum "10. …").
     *
     * @return list<array{id: string, name: string, mimeType: string}>
     */
    public function listDocuments(string $folderId): array
    {
        $files = [];
        $pageToken = null;

        do {
            $response = $this->send(fn (PendingRequest $http) => $http->get('/files', array_filter([
                'q' => sprintf("'%s' in parents and trashed = false and (mimeType = '%s' or mimeType = '%s')", $folderId, self::GOOGLE_DOC, self::DOCX),
                'fields' => 'nextPageToken, files(id, name, mimeType)',
                'pageSize' => 200,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
                'pageToken' => $pageToken,
            ])));

            array_push($files, ...($response->json('files') ?? []));
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        usort($files, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        return $files;
    }

    /**
     * Isi dokumen dalam format .docx (Google Docs diekspor, file .docx diunduh apa adanya).
     */
    public function downloadDocx(string $fileId, string $mimeType): string
    {
        return $this->send(fn (PendingRequest $http) => $http->get(...$this->downloadRequest($fileId, $mimeType)))->body();
    }

    /**
     * Unduh beberapa dokumen sekaligus secara paralel.
     *
     * @param  list<array{id: string, mimeType: string}>  $files
     * @return array<string, string|RuntimeException> isi .docx, atau error per file (key = ID file)
     */
    public function downloadMany(array $files): array
    {
        $this->ensureConfigured();

        $responses = Http::pool(fn (Pool $pool) => array_map(
            fn ($file) => $this->authorize($pool->as($file['id']))->get(...$this->downloadRequest($file['id'], $file['mimeType'])),
            $files,
        ));

        $results = [];
        foreach ($files as $file) {
            $response = $responses[$file['id']] ?? null;

            $results[$file['id']] = match (true) {
                $response instanceof Response && $response->successful() => $response->body(),
                $response instanceof Response => $this->failure($response),
                default => new RuntimeException('Tidak bisa terhubung ke Google Drive'.($response instanceof \Throwable ? ': '.$response->getMessage() : '.')),
            };
        }

        return $results;
    }

    /**
     * Google Docs diekspor ke .docx; file .docx diunduh apa adanya.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private function downloadRequest(string $fileId, string $mimeType): array
    {
        return $mimeType === self::GOOGLE_DOC
            ? ["/files/{$fileId}/export", ['mimeType' => self::DOCX]]
            : ["/files/{$fileId}", ['alt' => 'media', 'supportsAllDrives' => 'true']];
    }

    private function send(callable $request): Response
    {
        $this->ensureConfigured();

        try {
            $response = $request($this->client());
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa terhubung ke Google Drive: '.$e->getMessage(), 0, $e);
        }

        if ($response->successful()) {
            return $response;
        }

        throw $this->failure($response);
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Drive belum dikonfigurasi. Isi GOOGLE_DRIVE_API_KEY atau pasang file service account.');
        }
    }

    private function failure(Response $response): RuntimeException
    {
        // Alasan spesifik dari Google (error.details[].reason) memberi petunjuk perbaikan yang lebih tepat.
        $reasons = array_filter(array_column($response->json('error.details') ?? [], 'reason'));

        $reasons = [...$reasons, ...array_filter(array_column($response->json('error.errors') ?? [], 'reason'))];

        $message = match (true) {
            // Halaman HTML "Sorry…": Google sementara memblokir unduhan otomatis dari IP ini.
            $response->status() === 403 && str_contains((string) $response->header('Content-Type'), 'text/html') => 'Google sementara memblokir unduhan otomatis dari jaringan ini karena terlalu banyak permintaan. Tunggu beberapa menit lalu klik "Coba lagi yang gagal", atau gunakan service account.',
            (bool) array_intersect(['rateLimitExceeded', 'userRateLimitExceeded', 'RATE_LIMIT_EXCEEDED', 'downloadQuotaExceeded'], $reasons) => 'Google Drive membatasi permintaan. Tunggu beberapa menit lalu klik "Coba lagi yang gagal".',
            in_array('API_KEY_SERVICE_BLOCKED', $reasons, true) => 'API key ini dibatasi dan belum mengizinkan Google Drive API. Tambahkan "Google Drive API" di API restrictions key tersebut (Google Cloud Console → APIs & Services → Credentials).',
            in_array('SERVICE_DISABLED', $reasons, true) => 'Google Drive API belum diaktifkan di project Google Cloud. Aktifkan di APIs & Services → Library → Google Drive API.',
            in_array('API_KEY_INVALID', $reasons, true) => 'GOOGLE_DRIVE_API_KEY tidak valid. Periksa kembali key di .env.',
            default => null,
        } ?? match ($response->status()) {
            400 => 'Permintaan ke Google Drive tidak valid. Periksa kembali link folder.',
            401, 403 => 'Akses Google Drive ditolak. Pastikan Drive API aktif dan folder dibagikan ("Siapa saja yang memiliki link" atau ke service account).',
            404 => 'Folder atau dokumen tidak ditemukan. Periksa link dan izin berbaginya.',
            429 => 'Google Drive membatasi permintaan. Coba lagi beberapa saat lagi.',
            default => 'Google Drive error ('.$response->status().'): '.($response->json('error.message') ?? 'tidak diketahui'),
        };

        $throttled = $response->status() === 429
            || ($response->status() === 403 && str_contains((string) $response->header('Content-Type'), 'text/html'))
            || array_intersect(['rateLimitExceeded', 'userRateLimitExceeded', 'RATE_LIMIT_EXCEEDED', 'downloadQuotaExceeded'], $reasons);

        return new RuntimeException($message, $throttled ? self::THROTTLED : $response->status());
    }

    private function client(): PendingRequest
    {
        return $this->authorize(Http::createPendingRequest())
            ->retry(2, 1000, fn ($e) => $e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503], true), throw: false);
    }

    private function authorize(PendingRequest $http): PendingRequest
    {
        // Timeout pendek agar satu respons yang menggantung tidak melewati max_execution_time PHP (30 detik).
        $http->baseUrl('https://www.googleapis.com/drive/v3')->connectTimeout(5)->timeout(15);

        if ($key = config('services.google_drive.api_key')) {
            return $http->withQueryParameters(['key' => $key]);
        }

        return $http->withToken($this->serviceAccountToken());
    }

    private function serviceAccountToken(): string
    {
        return Cache::remember('google_drive_token', now()->addMinutes(50), function () {
            $client = new GoogleClient;
            $client->setAuthConfig((string) config('services.google_drive.credentials'));
            $client->setScopes(['https://www.googleapis.com/auth/drive.readonly']);

            $token = $client->fetchAccessTokenWithAssertion();

            return $token['access_token'] ?? throw new RuntimeException('Gagal mendapatkan token service account Google: '.($token['error_description'] ?? 'tidak diketahui'));
        });
    }
}
