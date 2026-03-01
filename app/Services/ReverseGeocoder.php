<?php

namespace App\Services;

use Illuminate\Support\Facades\{Cache, Http, Log};

class ReverseGeocoder
{
    protected string $endPoint = 'https://aws.simontini.id/geoserver/proteus/wfs';

   public function getLocation(float $lat, float $lng): array
    {
        $cacheKey = 'geocode_' . round($lat, 4) . '_' . round($lng, 4);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($lat, $lng) {
            $nominatim = $this->fromNominatim($lat, $lng);
            $point     = "POINT($lat $lng)";

            return [
                'province' => $nominatim['province'],
                'district' => $this->lookup('POLITICAL_LEVEL_5_dissolved', $point)
                            ?? $this->lookup('POLITICAL_LEVEL_5_dissolved', $point)
                            ?? $nominatim['district'], // fallback ke Nominatim kalau GeoServer kosong
                'village'  => $this->lookup('POLITICAL_LEVEL_6_dissolved', $point)
                        ?? $this->lookup('POLITICAL_LEVEL_5_dissolved', $point)
                        ?? $nominatim['village'], // fallback ke Nominatim kalau GeoServer kosong
            ];
        });
    }

    protected function fromNominatim(float $lat, float $lng): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'CTIS-Auriga/1.0 (contact@auriga.or.id)',
            ])->timeout(8)->get('https://nominatim.openstreetmap.org/reverse', [
                'format'         => 'json',
                'lat'            => $lat,
                'lon'            => $lng,
                'zoom'           => 12,
                'addressdetails' => 1,
            ]);

            if (! $response->ok()) {
                return ['province' => null, 'district' => null, 'village' => null];
            }

            $address = $response->json('address') ?? [];

            return [
                'province' => $address['state']
                            ?? $address['province']
                            ?? $address['region']
                            ?? null,
                'district' => $address['city']
                            ?? $address['county']
                            ?? $address['municipality']
                            ?? null,
                'village'  => $address['village']
                            ?? $address['suburb']
                            ?? $address['neighbourhood']
                            ?? null,
            ];

        } catch (\Exception $e) {
            Log::warning('Nominatim error: ' . $e->getMessage());
            return ['province' => null, 'district' => null, 'village' => null];
        }
    }

    protected function lookup(string $layer, string $point): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'CTIS-Auriga/1.0 (contact@auriga.or.id)',
            ])->timeout(5)->get('https://aws.simontini.id/geoserver/proteus/wfs', [
                'service'      => 'WFS',
                'version'      => '2.0.0',
                'request'      => 'GetFeature',
                'typeName'     => "proteus:$layer",
                'outputFormat' => 'application/json',
                'count'        => 1,
                'cql_filter'   => "INTERSECTS(geom, $point)",
            ]);

            if (! $response->ok()) {
                return null;
            }

            $rawName = $response->json('features.0.properties.NAME');

            return $this->extractPrimaryName($rawName);

        } catch (\Exception $e) {
            Log::warning("GeoServer error [$layer]: " . $e->getMessage());
            return null;
        }
    }

    protected function extractPrimaryName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        preg_match('/\[(.*?)\]/', $name, $matches);

        return $matches[1] ?? trim($name);
    }
}