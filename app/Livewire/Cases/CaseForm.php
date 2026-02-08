<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use App\Models\{CaseModel, CaseTranslation, Category};
use App\Services\CaseTaskGenerator;
use Livewire\{Component, WithFileUploads};




class CaseForm extends Component
{
    use WithFileUploads;
    public $case;
    public $caseId = null;

    public $report_id = null;

    // Fields
    public $category_id;

    public $status_id;

    public $event_date;


    public $is_public = false;

    // Localization
    public $title_id;

    public $summary_id;

    public $desc_id;

    public $title_en;

    public $summary_en;

    public $desc_en;

    public $searchLocation = '';

    public $results = [];

    public $selectedId = null;

    public $selectedText = null;

    public $open = false;

    public $lat;
    public $lng;
    public $locale;
    public $description_id;
    public $bukti = [];
    public $korban;
    public $pekerjaan;
    public $jenis_kelamin;
    public $jumlah_korban;
    public $konflik;

    protected $rules = [
        'category_id' => 'required',
        'status_id' => 'required',
        'event_date' => 'required|date',
        'title_id' => 'required|string|max:255',
    ];

    public function mount($caseId = null) 
    {
        if($caseId) {
            $this->case = CaseModel::with('translations')->findOrfail($caseId);
            $idTranslation = $this->case->translations->firstWhere('locale', 'id');
            $enTranslation = $this->case->translations->firstWhere('locale', 'en');

            $this->fill([
                'title_id' => $idTranslation->title ?? '',
                'title_en' => $enTranslation->title ??'',
                'summary_id' => $idTranslation->summary ?? '',
                'summary_en' => $enTranslation->summary ??'',
                'desc_id' => $idTranslation->description ??'',
                'desc_en' => $enTranslation->description ?? '',
                'category_id' => $this->case->category_id,
                'status_id' => $this->case->status_id,
                'event_date' => $this->case->event_date,
                'lat' => $this->case->latitude,
                'lng' => $this->case->longitude,
                'is_public' => $this->case->is_public,
                'bukti' => $this->case->bukti,
                'korban' => $this->case->korban,
                'pekerjaan' => $this->case->pekerjaan,
                'jenis_kelamin' => $this->case->jenis_kelamin,
                'jumlah_korban' => $this->case->jumlah_korban,
                'konflik' => $this->case->konflik,
            ]);

            // dd($this->case);

        }
    }
    
    // location
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
        $this->validate();

        $case = $this->case ?? new CaseModel;
        $caseNumber = 'CASE-'.strtoupper(Str::random(5));

        $data = [
            'case_number' => $caseNumber,
            'category_id' => $this->category_id,
            'report_id' => $this->report_id,
            'status_id' => $this->status_id,
            'event_date' => $this->event_date,
            'verified_by' => auth()->id(),
            'latitude' => $this->lat,
            'longitude' => $this->lng,
            'is_public' => $this->is_public,
            'bukti' => null,
            'korban' => $this->korban,
            'pekerjaan' => $this->pekerjaan,
            'jenis_kelamin' => $this->jenis_kelamin,
            'jumlah_korban' => $this->jumlah_korban,
            'konflik' => $this->konflik,
        ];

        $case->fill($data)->save();
        $case->refresh();

        foreach (['id', 'en'] as $locale) {
            CaseTranslation::updateOrCreate(
                ['case_id' => $case->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->title_id : $this->title_en,
                    'summary' => $locale === 'id' ? $this->summary_id : $this->summary_en,
                    'description' => $locale === 'id' ? $this->desc_id : $this->desc_en,
                ]
            );
        }

        // 3.1️⃣ Auto-generate tasks for this category (if templates exist)
        $generated = 0;
        try {
            $generated = CaseTaskGenerator::generate($case->id, $case->category_id);
        } catch (\Throwable $e) {
            // don't block case creation; log and continue
            \Log::error('Case task generation failed: '.$e->getMessage());
        }

        $paths = [];
        foreach ($this->bukti ?? [] as $file) {
            $paths[] = $file->store('cases/', 'public');
        }

        if (! empty($paths)) {
            $case->update([
                'bukti' => $paths ?? [],
            ]);
        }

        // dd($data);

        // dd($case);
        // dd($data);

        session()->flash('success', 'Kasus berhasil disimpan.');

        return redirect()->route('case.index');
    }

    /* ============================================================
     * SAVE TRANSLATIONS
     * ============================================================*/
    private function saveTranslations($caseId)
    {
        DB::table('case_translations')->updateOrInsert(
            ['case_id' => $caseId, 'locale' => 'id'],
            [
                'title' => $this->title_id,
                'summary' => $this->summary_id,
                'description' => $this->desc_id,
            ]
        );

        DB::table('case_translations')->updateOrInsert(
            ['case_id' => $caseId, 'locale' => 'en'],
            [
                'title' => $this->title_en,
                'summary' => $this->summary_en,
                'description' => $this->desc_en,
            ]
        );
    }


    /* ============================================================
     * RESET FORM
     * ============================================================*/
    private function resetForm()
    {
        $this->resetValidation();

        $this->reset([
            'caseId',
            'category_id',
            'status_id',
            'event_date',
            'latitude',
            'longitude',
            'is_public',
            'title_id',
            'summary_id',
            'desc_id',
            'title_en',
            'summary_en',
            'desc_en',
        ]);
    }


    public function render()
    {
        return view('livewire.cases.case-form', [
            'categories' => Category::with('translations')->get(),
            'statuses' => DB::table('statuses')->get(),
        ])->layout('layouts.internal');
    }
}
