<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseGeometryController extends Controller
{
    public function index(Request $request)
    {
        // return public case geometries as GeoJSON FeatureCollection
        $sector = $request->query('sector');
        $status = $request->query('status');

        $q = DB::table('case_geometries as g')
            ->join('cases as c', 'c.id', '=', 'g.case_id')
            ->where('c.is_public', true)
            ->leftJoin('case_translations as ct', function ($q) {
                $q->on('ct.case_id', '=', 'c.id')
                    ->where('ct.locale', 'id');
            })
            ->leftJoin('statuses as s', 's.id', '=', 'c.status_id')
            ->select(
                'g.id',
                'g.case_id',
                'g.title',
                'g.category',
                'c.case_number',
                'c.event_date',
                'c.status_id',
                's.key as status_key',
                DB::raw('ct.description as case_description'),
                DB::raw('ST_AsGeoJSON(g.geom) as geojson')
            );

        if ($sector) {
            $q->where('g.category', $sector);
        }

        if ($status) {
            $q->where('s.key', $status);
        }

        // search text in title or description
        if ($request->query('search')) {
            $s = '%'.$request->query('search').'%';
            $q->where(function ($q2) use ($s) {
                $q2->where('g.title', 'like', $s)
                    ->orWhere('ct.description', 'like', $s);
            });
        }

        // location filter: expect lat,lng and radius (km)
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radiusKm = $request->query('radius');
        if ($lat && $lng && $radiusKm) {
            $radiusMeters = floatval($radiusKm) * 1000;
            // Use ST_Distance_Sphere (MySQL) or ST_Distance (PostGIS) depending on DB
            // Try MySQL syntax first
            $q->whereRaw('ST_Distance_Sphere(g.geom, ST_GeomFromText(?, 4326)) <= ?', ["POINT({$lng} {$lat})", $radiusMeters]);
        }

        $rows = $q->get();

        $features = $rows->map(function ($r) {
            $geometry = $r->geojson ? json_decode($r->geojson, true) : null;
            $eventDate = $r->event_date ? Carbon::parse($r->event_date)->toDateString() : null;
            // get status key directly from joined column
            $statusKey = $r->status_key ?? null;

            return [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'id' => $r->id,
                    'case_id' => $r->case_id,
                    'case_number' => $r->case_number,
                    'title' => $r->title,
                    'category' => $r->category,
                    'event_date' => $eventDate,
                    'case_description' => $r->case_description ?? null,
                    'status_key' => $statusKey,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);

        // return view('front.dashboard-user', compact('q'));
    }
}
