<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class GeoServer extends Component
{
    public $searchLocation = '';

    public $results = [];

    public $selectedId = null;

    public $selectedText = null;

    public $open = false;
    public $lat = null;
    public $lng = null;
    public $locale;

    public function updatedSearchLocation()
    {
        $this->loadDistricts();
    }

    public function loadDistricts()
    {
        logger()->info('SEARCH LOCATION', ['search' => $this->searchLocation]);
        if (strlen($this->searchLocation) < 3) {
            $this->results = [];

            return;
        }

        $req = Http::get('https://aws.simontini.id/geoserver/proteus/wfs', [
            'service' => 'WFS',
            'version' => '2.0.0',
            'request' => 'GetFeature',
            'typeName' => 'proteus:POLITICAL_LEVEL_6_dissolved',
            'propertyName' => 'LEVEL_2,LEVEL_3,LEVEL_4,LEVEL_6,GEOCODE',
            'cql_filter' => "strToLowerCase(LEVEL_6) LIKE '%".strtolower($this->searchLocation)."%'",
            'maxFeatures' => 10,
            'outputFormat' => 'application/json',
        ]);

        $json = $req->json();

        $this->results = collect($json['features'] ?? [])
            ->map(fn ($item) => [
                'id' => $item['properties']['GEOCODE'],
                'text' => sprintf(
                    '%s, %s, %s, %s',
                    $item['properties']['LEVEL_6'],
                    $item['properties']['LEVEL_4'],
                    $item['properties']['LEVEL_3'],
                    $item['properties']['LEVEL_2'],
                ),
            ])
            ->take(10)
            ->values()
            ->toArray();

        $geo = Http::withHeaders([
            'User-Agent' => 'MyLaravelApp/1.0',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'format' => 'json',
            'q' => $this->results[0]['text'] ?? $this->searchLocation,
        ])->json();

        if (! empty($geo)) {
            $this->lat = $geo[0]['lat'];
            $this->lng = $geo[0]['lon'];

            $this->dispatch('location-updated', lat: $this->lat, lng: $this->lng);
        }

        logger()->info('RESULT COUNT', ['count' => count($this->results)]);
    }

    public function select($id, $text)
    {
        $this->selectedId = $id;
        $this->selectedText = $text;
        $this->searchLocation = $text;
        $this->results = [];
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.geo-server');
    }
}
