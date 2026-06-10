<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    private ?Sheets $sheets = null;

    public function __construct()
    {
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
        if (!$this->sheets) {
            throw new \RuntimeException('Google Sheets client not authenticated');
        }

        $spreadsheetId = config('services.google_sheets.spreadsheet_id');
        $range = $range ?? config('services.google_sheets.range');

        try {
            $response = $this->sheets->spreadsheets_values->get($spreadsheetId, $range);
            return $response->getValues() ?? [];
        } catch (\Throwable $e) {
            Log::error('Google Sheets fetch failed: ' . $e->getMessage());
            throw $e;
        }
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
}
