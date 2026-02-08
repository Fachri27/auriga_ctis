<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReverseGeocoder
{
    protected string $endPoint = 'https://aws.simontini.id/geoserver/proteus/wfs';

    public function getLocation(float $lat, float $lng): array
    {
        // $response = Http::withHeaders([
        //     'User-Agent' => 'CTIS-Auriga/1.0 (contact@auriga.or.id)'
        // ])->get('https://nominatim.openstreetmap.org/reverse', [
        //     'format' => 'json',
        //     'lat' => $lat,
        //     'lon' => $lng,
        //     'zoom' => 10,
        //     'addressdetails' => 1,
        // ]);

        // if (! $response->ok()) {
        //     return [];
        // }

        // $address = $response->json('address');

        // return [
        //     'province' => $address['state'] ?? null,
        //     'district' => $address['city']
        //         ?? $address['county']
        //         ?? $address['municipality']
        //         ?? null,
        //     'village' => $address['village']
        //         ?? $address['suburb']
        //         ?? null,
        // ];

        $point = "POINT($lat $lng)";

        return [
            'province' => $this->lookup('POLITICAL_LEVEL_3_dissolved', $point),
            'district' => $this->lookup('POLITICAL_LEVEL_4_dissolved', $point),
            // 'subdistrict' => $this->lookup('POLITICAL_LEVEL_5_dissolved', $point),
            // 'village' => $this->lookup('POLITICAL_LEVEL_6_dissolved', $point),
        ];

    }

    protected function lookup(string $layer, string $point): string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'CTIS-Auriga/1.0 (contact@auriga.or.id)',
            ])->timeout(10)->get($this->endPoint, [
                'service' => 'WFS',
                'version' => '2.0.0',
                'request' => 'GetFeature',
                'typeName' => "proteus:$layer",
                'outputFormat' => 'application/json',
                'count' => 1,
                'cql_filter' => "CONTAINS(geom, $point)",
            ]);

            if(!$response->ok()) {
                return null;
            }

            $rawName = $response->json('features.0.properties.NAME');

            return $this->extractPrimaryName($rawName);

        }catch (\Exception $e) {
            return null;
        }
    }

    protected function extractPrimaryName(?string $name): string 
    {
        if ($name) {
            return '';
        }

        preg_match('/\[(.*?)\]/', $name, $matches);

        return $matches[1] ?? trim($name);
    }
}
