<?php

namespace App\Livewire\Cases;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Services\CaseTaskGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class CaseModal extends Component
{
    public $show = false;

    public $caseId = null;

    public $report_id = null;

    // Fields
    public $category_id;

    public $status_id;

    public $event_date;

    public $latitude;

    public $longitude;

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

    protected $listeners = [
        'open-case-modal' => 'openCreate',
        'open-edit-case-modal' => 'openEdit',
    ];

    protected $rules = [
        'category_id' => 'required',
        'status_id' => 'required',
        'event_date' => 'required|date',
        'title_id' => 'required|string|max:255',
    ];
    
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

    /* ============================================================
     * OPEN CREATE CASE
     * ============================================================*/
    public function openCreate()
    {
        $this->resetForm();
        $this->show = true;
    }

    /* ============================================================
     * OPEN EDIT CASE
     * ============================================================*/
    public function openEdit($caseId)
    {
        $this->resetForm();
        $this->caseId = $caseId;

        $case = DB::table('cases')->where('id', $caseId)->first();
        if (! $case) {
            return;
        }

        $this->category_id = $case->category_id;
        $this->status_id = $case->status_id;
        $this->event_date = $case->event_date;
        $this->latitude = $case->latitude;
        $this->longitude = $case->longitude;
        $this->is_public = $case->is_public;

        $trans = DB::table('case_translations')
            ->where('case_id', $caseId)
            ->get()
            ->keyBy('locale');

        $idTrans = $trans['id'] ?? null;
        $enTrans = $trans['en'] ?? null;

        $this->title_id = $idTrans->title ?? '';
        $this->summary_id = $idTrans->summary ?? '';
        $this->desc_id = $idTrans->description ?? '';

        $this->title_en = $enTrans->title ?? '';
        $this->summary_en = $enTrans->summary ?? '';
        $this->desc_en = $enTrans->description ?? '';

        $this->show = true;
    }

    /* ============================================================
     * CONVERT REPORT → CASE
     * ============================================================*/
    public function mount($report_id = null)
    {
        if ($report_id) {
            $this->report_id = $report_id;

            $report = DB::table('reports')->where('id', $report_id)->first();

            $trans = DB::table('report_translations')
                ->where('report_id', $report_id)
                ->get()
                ->keyBy('locale');

            $idTrans = $trans['id'] ?? null;
            $enTrans = $trans['en'] ?? null;

            $descId = $idTrans->description ?? '';
            $descEn = $enTrans->description ?? '';

            $this->title_id = $descId
                ? Str::limit(strip_tags($descId), 60)
                : "Laporan #{$report->report_code}";

            $this->summary_id = $descId ? Str::limit(strip_tags($descId), 160) : null;
            $this->desc_id = $descId;

            $this->title_en = $descEn ? Str::limit(strip_tags($descEn), 60) : '';
            $this->summary_en = $descEn ? Str::limit(strip_tags($descEn), 160) : '';
            $this->desc_en = $descEn;
        }
    }

    /* ============================================================
     * SAVE CASE
     * ============================================================*/
    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {

            // ===== EDIT CASE =====
            if ($this->caseId) {

                DB::table('cases')
                    ->where('id', $this->caseId)
                    ->update([
                        'category_id' => $this->category_id,
                        'status_id' => $this->status_id,
                        'event_date' => $this->event_date,
                        'latitude' => $this->latitude,
                        'longitude' => $this->longitude,
                        'is_public' => $this->is_public,
                        'updated_at' => now(),
                    ]);

                $caseId = $this->caseId;

                $this->saveTranslations($caseId);

                DB::commit();
                $this->dispatch('close-case-modal');

                return;
            }

            // ===== CREATE CASE =====
            $caseNumber = 'CASE-'.str_pad(DB::table('cases')->count() + 1, 6, '0', STR_PAD_LEFT);

            $caseId = DB::table('cases')->insertGetId([
                'case_number' => $caseNumber,
                'category_id' => $this->category_id,
                'status_id' => $this->status_id,
                'event_date' => $this->event_date,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'is_public' => $this->is_public,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Save translation
            $this->saveTranslations($caseId);

            // Generate default tasks
            // 🚀 AUTO TASK GENERATOR
            try {
                $gen = \App\Services\CaseTaskGenerator::generate($caseId, $this->category_id);
                if ($gen > 0) {
                    DB::table('case_timelines')->insert([
                        'case_id' => $caseId,
                        'actor_id' => auth()->id(),
                        'notes' => "Auto-generated {$gen} task(s) when creating case.",
                        'created_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Case task generation failed: '.$e->getMessage());
            }

            // Timeline
            DB::table('case_timelines')->insert([
                'case_id' => $caseId,
                'notes' => 'Case created by '.auth()->user()->name,
                'created_at' => now(),
            ]);

            DB::commit();
            $this->dispatch('close-case-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        return view('livewire.cases.case-modal', [
            'categories' => Category::with('translations')->get(),
            'statuses' => DB::table('statuses')->get(),
        ]);
    }
}
