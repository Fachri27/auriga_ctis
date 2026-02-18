<?php

namespace App\Livewire\Reports;

use Illuminate\Support\Facades\{Cache, Http};
use App\Models\{Category, Report, ReportTranslation, Status};
use DB;
use Str;
use Livewire\{Component, WithFileUploads};

class ReportForm extends Component
{
    use WithFileUploads;

    public $id;
    public $nama_lengkap;

    public $nik;

    public $jenis_kelamin;

    public $tanggal_lahir;

    public $alamat;

    public $no_hp;

    public $email;

    public $pekerjaan;

    public $status_perkawinan;

    // public $contact;
    public $kewarganegaraan = 'Indonesia';

    public $locale;

    public $description;

    public $category_id;

    public $lat;

    public $lng;

    public $evidence_files = [];

    public $province_id;

    public $city_id;

    public $district_id;

    public $provinces = [];

    public $cities = [];

    public $districts = [];

    public $searchLocation = '';

    public $results = [];

    public $selectedId = null;

    public $selectedText = null;

    public $open = false;

    public $category_ids = [];
    public $type_pelapor;

    public function mount($locale)
    {
        $this->locale = in_array($locale, ['id', 'en']) ? $locale : 'id';
        // $this->loadProvinces();
        // $this->loadDistricts();
    }

    public function updatedSearchLocation($value)
    {
        if (strlen($this->searchLocation) < 3) {
            $this->results = [];
        }

        // reset lat lng kalo user hapus input
        if (strlen($value) < 3) {
            $this->resetLocation();

            return;
        }
        // dd($this->loadDistricts());
        $this->loadDistricts();
    }

    public function loadDistricts()
    {
        // $cacheKey = 'districts_'.md5($this->searchLocation);
        // $this->results = Cache::remember($cacheKey, 60, function () {
        //     try {
        //         $req = Http::get('https://aws.simontini.id/geoserver/proteus/wfs', [
        //             'service' => 'WFS',
        //             'version' => '2.0.0',
        //             'request' => 'GetFeature',
        //             'typeName' => 'proteus:POLITICAL_LEVEL_6_dissolved',
        //             'propertyName' => 'NAME,latitude,longtitude',
        //             'cql_filter' => "NAME ILIKE '%{$this->searchLocation}%'",
        //             'count' => 10,
        //             'outputFormat' => 'application/json',
        //         ]);

        //         $json = $req->json();

        //         return collect($json['features'] ?? [])
        //             ->map(function ($item) {
        //                 return [
        //                     'id' => $item['properties']['NAME'],
        //                     'text' => $this->formatName($item['properties']['NAME']),
        //                     'lat' => $item['properties']['latitude'],
        //                     'long' => $item['properties']['longtitude'],
        //                 ];
        //             })
        //             ->values()
        //             ->toArray();
        //     } catch (\Exception $e) {
        //         logger()->error('Error fetching districts', ['error' => $e->getMessage()]);

        //         return [];
        //     }
        // });
        // try {
        //     $req = Http::get('https://aws.simontini.id/geoserver/proteus/wfs', [
        //         'service' => 'WFS',
        //         'version' => '2.0.0',
        //         'request' => 'GetFeature',
        //         'typeName' => 'proteus:POLITICAL_LEVEL_6_dissolved',
        //         'propertyName' => 'NAME,latitude,longtitude',
        //         'cql_filter' => "NAME ILIKE '%{$this->searchLocation}%'",
        //         'count' => 10,
        //         // 'maxFeatures' => 10,
        //         'outputFormat' => 'application/json',
        //     ]);
        //     // logger()->info('GEOJSON STATUS', ['status' => $req->status(), 'body' => $req->body()]);
        //     $json = $req->json();

        //     // logger()->info('GEOJSON RESPONSE', ['response' => $json]);

        //     $this->results = collect($json['features'] ?? [])
        //         ->map(function ($item) {
        //             $name = $item['properties']['NAME'] ?? [];

        //             if (is_array($name)) {
        //                 $name = [$name];
        //             }

        //             $raw = trim($name, '[]');
        //             $parts = explode('][', $raw);
        //             if (is_numeric(end($parts))) {
        //                 array_pop($parts);
        //             }
        //             $name = collect($parts)
        //                 ->take(3)
        //                 ->implode(' - ');

        //             return [
        //                 'id' => $item['properties']['NAME'],
        //                 'text' => $name,
        //                 'lat' => $item['properties']['latitude'],
        //                 'long' => $item['properties']['longtitude'],
        //                 // 'geometry' => $item['geometry'],
        //             ];
        //         })
        //         ->values()
        //         ->toArray();

        //     // logger()->info('MAPPED RESULTS', ['results' => $this->results]);
        // } catch (\Exception $e) {
        //     logger()->error('Error fetching districts', ['error' => $e->getMessage()]);
        //     $this->results = $e;
        // }

        // AMBIL LANGSUNG DARI DATABASE
        try {
            $req = DB::connection('pgsql')->table('proteus.POLITICAL_LEVEL_6_dissolved')
                ->select('NAME', 'latitude', 'longtitude')
                ->where('NAME', 'ILIKE', ["%{$this->searchLocation}%"])
                ->limit(10)
                ->get();

            $this->results = $req->map(fn ($item) => [
                'id' => $item->NAME,
                'text' => $this->formatName($item->NAME),
                'lat' => $item->latitude,
                'long' => $item->longtitude,
            ])->toArray();
        } catch (\Exception $e) {
            logger()->error('Error fetching districts', ['error' => $e->getMessage()]);
            $this->results = [];
        }

        // logger()->info('RESULT COUNT', ['count' => count($this->results)]);
    }

    public function select($id, $text, $lat, $lng)
    {
        $this->selectedId = $id;
        $this->selectedText = $text;
        $this->searchLocation = $text;
        $this->lat = $lat;
        $this->lng = $lng;

        $this->results = [];
        $this->open = false;

        $geometry = $this->loadGeometry($id);

        $this->dispatch('location-updated', lat: $this->lat, lng: $this->lng, geometry: $geometry);
    }

    private function formatName($name)
    {
        if (is_array($name)) {
            $name = $name[0] ?? '';
        }

        $raw = trim($name, '[]');
        $parts = explode('][', $raw);

        if (is_numeric(end($parts))) {
            array_pop($parts);
        }

        return collect($parts)->take(3)->implode(' - ');
    }

    private function loadGeometry($name)
    {
        try {
            $req = Http::timeout(10)->get(
                'https://aws.simontini.id/geoserver/proteus/wfs',
                [
                    'service' => 'WFS',
                    'version' => '2.0.0',
                    'request' => 'GetFeature',
                    'typeName' => 'proteus:POLITICAL_LEVEL_6_dissolved',

                    // 🎯 EXACT MATCH = CEPAT
                    'cql_filter' => "NAME = '{$name}'",
                    'outputFormat' => 'application/json',
                ]
            );

            return $req->json()['features'][0]['geometry'] ?? null;

        } catch (\Throwable $e) {
            logger()->error('GEO POLYGON ERROR', ['e' => $e->getMessage()]);

            return null;
        }
    }

    public function resetLocation()
    {
        $this->lat = null;
        $this->lng = null;
        $this->selectedId = null;
        $this->selectedText = null;
        $this->results = [];
        $this->open = false;

        // 🔥 event KHUSUS reset
        $this->dispatch('location-reset');
    }

    public function save()
    {
        $this->validate([
            'description' => 'required|min:10',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'evidence_files.*' => 'nullable|file|max:5120',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'type_pelapor' => 'required',
        ]);

        // ambil status open
        $statusOpen = Status::where('key', 'open')->first();
        $report = Report::create([
            'report_code' => 'RPT-'.strtoupper(Str::random(5)),
            'nama_lengkap' => $this->nama_lengkap,
            'nik' => $this->nik,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tanggal_lahir' => $this->tanggal_lahir,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'email' => $this->email,
            'pekerjaan' => $this->pekerjaan,
            'status_perkawinan' => null,
            'kewarganegaraan' => $this->kewarganegaraan,
            'description' => $this->description,
            'status_id' => $statusOpen ? $statusOpen->id : null,
            'category_ids' => $this->category_ids,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'evidence' => null,
            'created_by' => auth()->id(),
            'type_pelapor' => $this->type_pelapor,
        ]);

        // \dd($report);

        ReportTranslation::updateOrCreate([
            'report_id' => $report->id,
            'locale' => $this->locale,
            'description' => $this->description,
        ]);
        

        $paths = [];
        foreach ($this->evidence_files as $file) {
            $paths[] = $file->store('reports/'.$report->id, 'public');
        }

        if (! empty($paths)) {
            $report->update([
                'evidence' => $paths,
            ]);
        }
        

        session()->flash('success', __('Your report has been submitted successfully!'));

        // Clear form
        $this->reset('nama_lengkap', 'description', 'lat', 'lng', 'evidence_files', 'nik', 'jenis_kelamin', 'tanggal_lahir', 'alamat', 'no_hp', 'email', 'pekerjaan', 'status_perkawinan', 'city_id', 'district_id', 'province_id', 'category_id', 'results');
    }

    public function render()
    {
        return view('livewire.reports.report-form', [
            'categories' => Category::with('translations')->get(),
        ])->layout('layouts.main');
    }
}
