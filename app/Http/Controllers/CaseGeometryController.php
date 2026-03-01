<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseGeometryController extends Controller
{
    public function index(Request $request)
    {
        $sector   = $request->query('sector');
        $status   = $request->query('status');
        $locale   = $request->query('locale', app()->getLocale()); // ← ambil locale dari request

        $q = DB::table('case_geometries as g')
            ->join('cases as c', 'c.id', '=', 'g.case_id')
            ->where('c.is_public', true)
            // Locale aktif
            ->leftJoin('case_translations as ct_locale', function ($q) use ($locale) {
                $q->on('ct_locale.case_id', '=', 'c.id')
                    ->where('ct_locale.locale', $locale);
            })
            // Fallback id
            ->leftJoin('case_translations as ct_id', function ($q) {
                $q->on('ct_id.case_id', '=', 'c.id')
                    ->where('ct_id.locale', 'id');
            })
            // Fallback en
            ->leftJoin('case_translations as ct_en', function ($q) {
                $q->on('ct_en.case_id', '=', 'c.id')
                    ->where('ct_en.locale', 'en');
            })
            ->leftJoin('statuses as s', 's.id', '=', 'c.status_id')
            ->select(
                'g.id',
                'g.case_id',
                'g.category',
                'c.case_number',
                'c.event_date',
                'c.status_id',
                's.key as status_key',
                // Title: locale aktif → id → en → kolom geometries
                DB::raw('COALESCE(
                    NULLIF(ct_locale.title, ""),
                    NULLIF(ct_id.title, ""),
                    NULLIF(ct_en.title, ""),
                    g.title
                ) as title'),
                // Description: locale aktif → id → en → kolom geometries
                DB::raw('COALESCE(
                    NULLIF(ct_locale.description, ""),
                    NULLIF(ct_id.description, ""),
                    NULLIF(ct_en.description, ""),
                    g.case_description
                ) as case_description'),
                DB::raw('ST_AsGeoJSON(g.geom) as geojson')
            );

        if ($sector) {
            $q->where('g.category', $sector);
        }

        if ($status) {
            $q->where('s.key', $status);
        }

        if ($request->query('search')) {
            $s = '%' . $request->query('search') . '%';
            $q->where(function ($q2) use ($s) {
                $q2->where('g.title', 'like', $s)
                    ->orWhere('ct_locale.description', 'like', $s)
                    ->orWhere('ct_id.description', 'like', $s);
            });
        }

        $lat      = $request->query('lat');
        $lng      = $request->query('lng');
        $radiusKm = $request->query('radius');
        if ($lat && $lng && $radiusKm) {
            $radiusMeters = floatval($radiusKm) * 1000;
            $q->whereRaw(
                'ST_Distance_Sphere(g.geom, ST_GeomFromText(?, 4326)) <= ?',
                ["POINT({$lng} {$lat})", $radiusMeters]
            );
        }

        $rows = $q->get();

        $features = $rows->map(function ($r) {
            $geometry  = $r->geojson ? json_decode($r->geojson, true) : null;
            $eventDate = $r->event_date ? Carbon::parse($r->event_date)->toDateString() : null;

            return [
                'type'     => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'id'               => $r->id,
                    'case_id'          => $r->case_id,
                    'case_number'      => $r->case_number,
                    'title'            => $r->title,
                    'category'         => $r->category,
                    'event_date'       => $eventDate,
                    'case_description' => $r->case_description ?? null,
                    'status_key'       => $r->status_key ?? null,
                ],
            ];
        });

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
