<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\{Auth, DB, Http, Log};
use Illuminate\Support\Str;

use App\Models\{CaseModel, CaseTranslation, Category};
use App\Services\{CaseCoverImage, CaseStages, CaseWriter};
use App\Livewire\Concerns\AutoTranslates;
use Livewire\{Component, WithFileUploads};

class CaseForm extends Component
{
    use WithFileUploads;
    use AutoTranslates;
    public $case;
    public $caseId = null;

    public $report_id = null;

    // Fields
    public $category_id;

    public $status_id;

    public $kualifikasi;

    public $event_date;

    // Lokasi sesuai teks dokumen (ditampilkan di halaman kasus); titik peta disimpan terpisah di lat/lng.
    public $lokasi;

    public $is_public = false;

    // Localization
    public $title_id;

    public $summary_id = null;

    public $desc_id;

    public $title_en;

    public $summary_en = null;

    public $desc_en;

    public $searchLocation = "";

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
    public $category_ids = [];
    public $existingBukti = [];

    // Foto sampul (lihat App\Services\CaseCoverImage)
    public $cover;
    public $existingCover;
    public $removeCover = false;
    public $cover_caption_id;
    public $cover_caption_en;
    public $cover_credit;

    public $perkembangan_id;
    public $perkembangan_en;
    public $pembelajaran_id;
    public $pembelajaran_en;
    public $dugaan_permasalahan_id;
    public $dugaan_permasalahan_en;

    // Keterangan custom per tahapan kasus: key tahapan => teks (lihat App\Services\CaseStages)
    public $tahapan_id = [];
    public $tahapan_en = [];
    public $pelapor;
    public $terlapor;
    public $sumber_id;
    public $status;
    public $instansi;

    protected $rules = [
        // 'category_id' => 'required',
        "status_id" => "required",
        "kualifikasi" => "nullable|in:pidana,perdata,administrasi",
        "event_date" => "required|date",
        "lokasi" => "nullable|string|max:500",
        "category_ids" => "required|array|min:1",
        "category_ids.*" => "integer|exists:categories,id",
        "bukti.*" => "file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,mp4,mov,avi,zip,rar",
        "cover" => "nullable|image|mimes:jpg,jpeg,png,webp|max:8192|dimensions:min_width=800,min_height=450",
        "cover_caption_id" => "nullable|string|max:255",
        "cover_caption_en" => "nullable|string|max:255",
        "cover_credit" => "nullable|string|max:150",
        "tahapan_id.*" => "nullable|string",
        "tahapan_en.*" => "nullable|string",
    ];

    protected $messages = [
        "cover.image" => "Foto sampul harus berupa gambar JPG, PNG, atau WebP.",
        "cover.mimes" => "Foto sampul harus berupa gambar JPG, PNG, atau WebP.",
        "cover.max" => "Ukuran foto sampul maksimal 8 MB.",
        "cover.dimensions" => "Foto sampul minimal 800 × 450 piksel agar tetap tajam.",
    ];

    public function mount($caseId = null)
    {
        if ($caseId) {
            $this->case = CaseModel::with("translations")->findOrFail($caseId);
            $idTranslation = $this->case->translations->firstWhere(
                "locale",
                "id",
            );
            $enTranslation = $this->case->translations->firstWhere(
                "locale",
                "en",
            );

            $this->fill([
                "title_id" => $idTranslation->title ?? "",
                "title_en" => $enTranslation->title ?? "",
                "summary_id" => $idTranslation->summary ?? "",
                "summary_en" => $enTranslation->summary ?? "",
                "desc_id" => $idTranslation->description ?? "",
                "desc_en" => $enTranslation->description ?? "",
                "category_ids" => $this->case->category_ids,
                "status_id" => $this->case->status_id,
                "kualifikasi" => $this->case->kualifikasi,
                "event_date" => $this->case->event_date,
                "lat" => $this->case->latitude,
                "lng" => $this->case->longitude,
                "lokasi" => $this->case->lokasi,
                "is_public" => $this->case->is_public,
                // 'bukti' => $this->case->bukti,
                "korban" => $this->case->korban,
                "pekerjaan" => $this->case->pekerjaan,
                "jenis_kelamin" => $this->case->jenis_kelamin,
                "jumlah_korban" => $this->case->jumlah_korban,
                "konflik" => $this->case->konflik,
                "perkembangan_id" => $idTranslation->perkembangan ?? "",
                "perkembangan_en" => $enTranslation->perkembangan ?? "",
                "pembelajaran_id" => $idTranslation->pembelajaran ?? "",
                "pembelajaran_en" => $enTranslation->pembelajaran ?? "",
                "dugaan_permasalahan_id" => $idTranslation->dugaan_permasalahan ?? "",
                "dugaan_permasalahan_en" => $enTranslation->dugaan_permasalahan ?? "",
                "tahapan_id" => $idTranslation->keterangan_tahapan ?? [],
                "tahapan_en" => $enTranslation->keterangan_tahapan ?? [],
                "pelapor" => $this->case->pelapor,
                "terlapor" => $this->case->terlapor,
                "sumber_id" => $this->case->sumber,
                "instansi" => $this->case->instansi,
                "status" => $this->case->status_narasi,
                "cover_caption_id" => $idTranslation->cover_caption ?? "",
                "cover_caption_en" => $enTranslation->cover_caption ?? "",
                "cover_credit" => $this->case->cover_credit,
            ]);

            // dd($this->case);

            $this->existingBukti = $this->case->bukti ?? [];
            $this->bukti = [];
            $this->existingCover = $this->case->cover_image;
        }
    }

    public function updatedCover(): void
    {
        $this->removeCover = false;
        $this->validateOnly("cover");
    }

    public function removeCoverImage(): void
    {
        $this->cover = null;
        $this->removeCover = true;
        $this->resetValidation("cover");
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
            $req = DB::connection("pgsql")
                ->table("proteus.POLITICAL_LEVEL_6_dissolved")
                ->select("NAME", "latitude", "longtitude")
                ->where("NAME", "ILIKE", ["%{$this->searchLocation}%"])
                ->limit(20)
                ->get();

            $this->results = $req
                ->map(
                    fn($item) => [
                        "id" => $item->NAME,
                        "text" => $this->formatName($item->NAME),
                        "lat" => $item->latitude,
                        "long" => $item->longtitude,
                    ],
                )
                ->toArray();
        } catch (\Exception $e) {
            logger()->error("Error fetching districts", [
                "error" => $e->getMessage(),
            ]);
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

        $this->dispatch(
            "location-updated",
            lat: $this->lat,
            lng: $this->lng,
            geometry: $geometry,
        );
    }

    private function formatName($name)
    {
        if (is_array($name)) {
            $name = $name[0] ?? "";
        }

        $raw = trim($name, "[]");
        $parts = explode("][", $raw);

        if (is_numeric(end($parts))) {
            array_pop($parts);
        }

        return collect($parts)->take(3)->implode(" - ");
    }

    private function loadGeometry($name)
    {
        try {
            $req = Http::timeout(10)->get(
                "https://aws.simontini.id/geoserver/proteus/wfs",
                [
                    "service" => "WFS",
                    "version" => "2.0.0",
                    "request" => "GetFeature",
                    "typeName" => "proteus:POLITICAL_LEVEL_6_dissolved",

                    // 🎯 EXACT MATCH = CEPAT
                    "cql_filter" => "NAME = '{$name}'",
                    "outputFormat" => "application/json",
                ],
            );

            return $req->json()["features"][0]["geometry"] ?? null;
        } catch (\Throwable $e) {
            logger()->error("GEO POLYGON ERROR", ["e" => $e->getMessage()]);

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
        $this->dispatch("location-reset");
    }

    public function save()
    {
        $this->dispatch(
            "case-save-debug",
            step: "save_called",
            payload: [
                "status_id" => $this->status_id,
                "event_date" => $this->event_date,
                "category_ids" => $this->category_ids,
                "title_id" => $this->title_id,
            ],
        );

        $this->validate();

        try {
            $data = [
                "category_ids" => $this->category_ids,
                "report_id" => $this->report_id,
                "status_id" => $this->status_id,
                "kualifikasi" => $this->kualifikasi ?: null,
                "event_date" => $this->event_date,
                "verified_by" => Auth::id(),
                "latitude" => $this->lat,
                "longitude" => $this->lng,
                "lokasi" => trim((string) $this->lokasi) ?: null,
                "is_public" => $this->is_public,
                "bukti" => null,
                "korban" => $this->korban,
                "pekerjaan" => $this->pekerjaan,
                "jenis_kelamin" => $this->jenis_kelamin,
                "jumlah_korban" => $this->jumlah_korban,
                "konflik" => $this->konflik,
                "pelapor" => $this->pelapor,
                "terlapor" => $this->terlapor,
                "sumber" => $this->sumber_id,
                "status_narasi" => $this->status,
                "instansi" => $this->instansi,
                "cover_credit" => trim((string) $this->cover_credit) ?: null,
            ];

            // Nomor kasus dibuat sekali saat kasus baru; edit tidak mengubahnya.
            $case = app(CaseWriter::class)->save($this->case, $data, [
                "id" => [
                    "title" => $this->title_id,
                    "cover_caption" => trim((string) $this->cover_caption_id) ?: null,
                    "description" => $this->desc_id,
                    "perkembangan" => $this->perkembangan_id,
                    "pembelajaran" => $this->pembelajaran_id,
                    "dugaan_permasalahan" => $this->dugaan_permasalahan_id,
                    "keterangan_tahapan" => $this->stageNotes($this->tahapan_id),
                ],
                "en" => [
                    "title" => $this->title_en ?? "",
                    "cover_caption" => trim((string) $this->cover_caption_en) ?: null,
                    "description" => $this->desc_en ?? "",
                    "perkembangan" => $this->perkembangan_en ?? "",
                    "pembelajaran" => $this->pembelajaran_en ?? "",
                    "dugaan_permasalahan" => $this->dugaan_permasalahan_en ?? "",
                    "keterangan_tahapan" => $this->stageNotes($this->tahapan_en),
                ],
            ]);

            // Foto sampul: foto baru menggantikan yang lama (berkas lama dihapus); tombol hapus mengosongkannya.
            $covers = app(CaseCoverImage::class);
            if ($this->cover instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $oldCover = $case->cover_image;
                $case->update(["cover_image" => $covers->store($this->cover)]);
                $covers->delete($oldCover);
            } elseif ($this->removeCover && $case->cover_image) {
                $covers->delete($case->cover_image);
                $case->update(["cover_image" => null]);
            }

            $paths = [];

            foreach ($this->bukti ?? [] as $file) {
                if (
                    $file instanceof
                    \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                ) {
                    $paths[] = $file->store("cases/", "public");
                }
            }

            // Ambil bukti lama
            $existing = $this->existingBukti ?? [];

            // Gabungkan kalau ada file baru
            if (!empty($paths)) {
                $existing = array_merge($existing, $paths);
            }

            // Update
            $case->update([
                "bukti" => $existing,
            ]);

            // ✉️ Kirim notifikasi ke subscriber ketika case baru dibuat & sudah publish:
            //    1. case_id IS NULL = berlangganan semua kasus baru
            //    2. case_id = case ini = mengikuti kasus spesifik ini
            if (!$this->caseId && ($case->is_public ?? false)) {
                // Hanya untuk case baru yang sudah publish, bukan edit
                try {
                    $emails = \App\Models\CaseSubscription::whereNull('case_id')
                        ->orWhere('case_id', $case->id)
                        ->pluck('email');

                    foreach ($emails as $email) {
                        \Mail::to($email)->queue(new \App\Mail\NewCaseMail(
                            (object) [
                                "id" => $case->id,
                                "case_number" => $case->case_number,
                                "title" => $this->title_id ?: "Case " . $case->case_number,
                                "description" => $this->desc_id ?? "",
                            ],
                        ));
                    }
                } catch (\Throwable $e) {
                    Log::error(
                        "Failed to send case creation notification: " .
                            $e->getMessage(),
                    );
                }
            }

            // dd($data);

            // dd($case);
            // dd($data);

            $this->dispatch(
                "case-save-debug",
                step: "save_success",
                payload: [
                    "case_id" => $case->id,
                    "case_number" => $case->case_number,
                ],
            );

            session()->flash("success", "Kasus berhasil disimpan.");

            return redirect()->route("case.index");
        } catch (\Throwable $e) {
            $this->dispatch(
                "case-save-debug",
                step: "save_error",
                payload: [
                    "message" => $e->getMessage(),
                ],
            );

            Log::error("Error saving case: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);

            $this->dispatch("notify-error", message: "Gagal menyimpan: " . $e->getMessage());

            session()->flash(
                "error",
                "Gagal menyimpan kasus: " . $e->getMessage(),
            );
        }
    }

    /* ============================================================
     * SAVE TRANSLATIONS
     * ============================================================*/
    private function saveTranslations($caseId)
    {
        DB::table("case_translations")->updateOrInsert(
            ["case_id" => $caseId, "locale" => "id"],
            [
                "title" => $this->title_id,
                "summary" => Str::of($this->desc_id)->stripTags()->limit(300, "..."),
                "description" => $this->desc_id,
            ],
        );

        DB::table("case_translations")->updateOrInsert(
            ["case_id" => $caseId, "locale" => "en"],
            [
                "title" => $this->title_en,
                "summary" => Str::of($this->desc_en)->stripTags()->limit(300, "..."),
                "description" => $this->desc_en,
            ],
        );
    }

    /* ============================================================
     * RESET FORM
     * ============================================================*/
    private function resetForm()
    {
        $this->resetValidation();

        $this->reset([
            "caseId",
            "category_id",
            "status_id",
            "kualifikasi",
            "event_date",
            "latitude",
            "longitude",
            "lokasi",
            "is_public",
            "title_id",
            "summary_id",
            "desc_id",
            "title_en",
            "summary_en",
            "desc_en",
            "cover",
            "existingCover",
            "removeCover",
            "cover_caption_id",
            "cover_caption_en",
            "cover_credit",
            "tahapan_id",
            "tahapan_en",
        ]);
    }

    /**
     * Keterangan tahapan yang diisi saja; null bila semuanya kosong (halaman publik memakai keterangan standar).
     *
     * @return array<string, string>|null
     */
    private function stageNotes(?array $notes): ?array
    {
        $notes = array_filter(array_map(fn ($note) => trim((string) $note), $notes ?? []), fn ($note) => $note !== "");

        return $notes ?: null;
    }

    protected function translatableFields(): array
    {
        return [
            "title_id" => "title_en",
            "desc_id" => "desc_en",
            "perkembangan_id" => "perkembangan_en",
            "pembelajaran_id" => "pembelajaran_en",
            "dugaan_permasalahan_id" => "dugaan_permasalahan_en",
            "cover_caption_id" => "cover_caption_en",
        ] + collect($this->tahapan_id ?? [])
            ->filter(fn ($note) => filled($note))
            ->mapWithKeys(fn ($note, $key) => ["tahapan_id.{$key}" => "tahapan_en.{$key}"])
            ->all();
    }

    public function render()
    {
        return view("livewire.cases.case-form", [
            "categories" => Category::with("translations")->get(),
            "statuses" => DB::table("statuses")->get(),
            // Tahapan mengikuti kualifikasi yang dipilih; keterangan standar jadi placeholder isian.
            "stagesId" => CaseStages::for($this->kualifikasi, null, [], "id")["steps"],
            "stagesEn" => CaseStages::for($this->kualifikasi, null, [], "en")["steps"],
        ])->layout("layouts.internal");
    }
}
