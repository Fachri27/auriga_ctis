<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;

class PublicCaseController extends Controller
{
    public function show($locale, $caseNumber)
    {
        app()->setLocale($locale);

        $case = CaseModel::with([
                'translations',
                'category.translations',
                'status',
                'geometries',
                'timelines.process.translations'
            ])
            ->where('case_number', $caseNumber)
            ->where('is_public', true)
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Case not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformCase($case)
        ]);
    }

    protected function transformCase($case): array
    {
        $translation = $case->translations
            ->firstWhere('locale', app()->getLocale())
            ?? $case->translations->first();

        return [
            'case_number' => $case->case_number,
            'title'       => $translation?->title,
            'summary'     => $translation?->summary,
            'description' => $translation?->description,

            'status' => [
                'key'  => $case->status?->key,
                'name' => $case->status?->name,
            ],

            'category' => [
                'slug' => $case->category?->slug,
                'name' => optional(
                    $case->category?->translations
                        ->firstWhere('locale', app()->getLocale())
                )->name,
            ],

            'location' => [
                'latitude'  => $case->latitude,
                'longitude' => $case->longitude,
            ],

            'event_date'   => $case->event_date,
            'published_at'=> $case->published_at,

            'geometries' => $case->geometries->map(fn ($g) => [
                'id'       => $g->id,
                'type'     => str_starts_with($g->geom, 'POINT') ? 'Point' : 'Unknown',
                'geojson'  => $this->wktToGeoJson($g->geom),
                'title'    => $g->title,
                'category' => $g->category,
            ]),

            'timeline' => $case->timelines->map(fn ($t) => [
                'process' => optional(
                    $t->process?->translations
                        ->firstWhere('locale', app()->getLocale())
                )->name,
                'notes' => $t->notes,
                'started_at' => $t->started_at,
                'finished_at'=> $t->finished_at,
            ]),
        ];
    }

    protected function wktToGeoJson(string $wkt): array
    {
        if (str_starts_with($wkt, 'POINT')) {
            preg_match('/POINT\(([-\d\.]+)\s+([-\d\.]+)\)/', $wkt, $m);

            return [
                'type' => 'Point',
                'coordinates' => [
                    (float) $m[1],
                    (float) $m[2],
                ]
            ];
        }

        return [];
    }
}
