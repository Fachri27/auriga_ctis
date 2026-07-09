<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    private ?Sheets $sheets = null;

    public function __construct()
    {
        $apiKey = config('services.google_sheets.api_key');

        if ($apiKey) {
            return;
        }

        $this->authenticate();
    }

    private function authenticate(): void
    {
        try {
            $client = new GoogleClient();
            $client->setApplicationName('CTIS Google Sheets');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(config('services.google_sheets.credentials'));
            $client->setAccessType('offline');

            $this->sheets = new Sheets($client);
        } catch (\Throwable $e) {
            Log::error('Google Sheets auth failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getData(?string $range = null): array
    {
        $spreadsheetId = config('services.google_sheets.spreadsheet_id');
        $range = $range ?? config('services.google_sheets.range');
        $apiKey = config('services.google_sheets.api_key');

        if ($apiKey) {
            return $this->fetchWithApiKey($spreadsheetId, $range, $apiKey);
        }

        if (!$this->sheets) {
            throw new \RuntimeException('Google Sheets client not authenticated');
        }

        try {
            $response = $this->sheets->spreadsheets_values->get($spreadsheetId, $range);
            return $response->getValues() ?? [];
        } catch (\Throwable $e) {
            Log::error('Google Sheets fetch failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function fetchWithApiKey(string $spreadsheetId, string $range, string $apiKey): array
    {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}?key={$apiKey}";

        $response = Http::get($url);

        if ($response->failed()) {
            Log::error('Google Sheets API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Google Sheets API request failed: ' . $response->body());
        }

        return $response->json('values', []);
    }

    public function getDataAsJson(?string $range = null): array
    {
        $rows = $this->getData($range);

        if (empty($rows)) {
            return [];
        }

        $headers = array_shift($rows);
        $result = [];

        foreach ($rows as $row) {
            $item = [];
            foreach ($headers as $i => $header) {
                $item[$header] = $row[$i] ?? null;
            }
            $result[] = $item;
        }

        return $result;
    }

    public function getSheetNames(): array
    {
        $spreadsheetId = config('services.google_sheets.spreadsheet_id');
        $apiKey = config('services.google_sheets.api_key');

        if ($apiKey) {
            return $this->getSheetNamesWithApiKey($spreadsheetId, $apiKey);
        }

        if (!$this->sheets) {
            return [];
        }

        try {
            $spreadsheet = $this->sheets->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheet->getSheets();
            return array_map(fn($s) => $s->getProperties()->getTitle(), $sheets);
        } catch (\Throwable $e) {
            Log::error('Failed to get sheet names: ' . $e->getMessage());
            return [];
        }
    }

    private function getSheetNamesWithApiKey(string $spreadsheetId, string $apiKey): array
    {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}?key={$apiKey}";

        $response = Http::get($url);

        if ($response->failed()) {
            Log::error('Google Sheets API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        $sheets = $response->json('sheets', []);

        return array_map(fn($s) => $s['properties']['title'], $sheets);
    }
}
