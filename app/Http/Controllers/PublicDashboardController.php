<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use App\Models\CaseModel;
use App\Services\ReverseGeocoder;

class PublicDashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $filter = $request->get('filter', null);
        $locale = app()->getLocale();

        $query = CaseModel::with(['category', 'status', 'translations', 'province'])
            ->where('is_public', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($filter === 'closed') {
            $query->whereHas('status', function ($q) {
                $q->where('key', 'closed');
            });
        } elseif ($filter === 'active') {
            $query->where(function ($q) {
                $q->whereHas('status', function ($s) {
                    $s->where('key', '<>', 'closed');
                })->orWhereNull('status_id');
            });
        }

        $allCategoryNames = \App\Models\CategoryTranslation::where('locale', $locale)
            ->pluck('name', 'category_id');

        if ($allCategoryNames->isEmpty()) {
            $allCategoryNames = \App\Models\CategoryTranslation::pluck('name', 'category_id');
        }

        // Geocoder untuk menurunkan kabupaten/kota & desa dari koordinat agar
        // search-by-lokasi jalan (per koordinat ter-cache 30 hari di ReverseGeocoder).
        $geo = app(ReverseGeocoder::class);

        $cases = $query->orderByDesc('event_date')->get()->map(function ($c) use ($locale, $allCategoryNames, $geo) {
            $translation = $c->translations->where('locale', $locale)->first()
                ?? $c->translations->first();

            $categoryIds = is_array($c->category_ids) ? $c->category_ids : [];
            $categoryNames = collect($categoryIds)
                ->map(fn($id) => $allCategoryNames->get($id))
                ->filter()
                ->implode(', ');

            $loc = $geo->getLocation((float) $c->latitude, (float) $c->longitude);

            return [
                'case_number' => $c->case_number,
                'title' => $translation?->title ?? $c->case_number,
                'category' => $categoryNames ? ['name' => $categoryNames] : null,
                'status' => $c->status ? ['name' => $c->status->name, 'key' => $c->status->key] : null,
                'province' => $c->province?->name,
                'regency' => $loc['district'] ?? null,
                'village' => $loc['village'] ?? null,
                'latitude' => $c->latitude,
                'longitude' => $c->longitude,
                'event_date' => $c->event_date,
                'detail_url' => route('public.verify.case', ['locale' => $locale, 'caseNumber' => $c->case_number]),
            ];
        })->values();

        $totalCases = CaseModel::where('is_public', true)->count();
        $activeCases = CaseModel::where('is_public', true)
            ->whereHas('status', function ($q) {
                $q->where('key', '<>', 'closed');
            })->count();
        $closedCases = CaseModel::where('is_public', true)
            ->whereHas('status', function ($q) {
                $q->where('key', 'closed');
            })->count();
        $provinceCount = $cases->pluck('province')->filter()->unique()->count();

        // Kabupaten/kota terdampak: diturunkan dari koordinat tiap case publik via
        // ReverseGeocoder. Dibungkus cache bersama homepage (home_regency_covered)
        // agar angkanya konsisten di kedua halaman & tidak me-loop tiap load.
        $regencyCount = Cache::remember('home_regency_covered', now()->addHour(), function () {
            $geo = app(ReverseGeocoder::class);
            $regencies = [];

            foreach (CaseModel::where('is_public', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['latitude', 'longitude']) as $c) {
                $loc = $geo->getLocation((float) $c->latitude, (float) $c->longitude);
                $kab = $loc['district'] ?? null;
                if ($kab !== null && $kab !== '') {
                    $regencies[$kab] = true;
                }
            }

            return count($regencies);
        });

        // Urutan status mengikuti lifecycle bar di case-detail (status bar).
        $barOrder = [
            'open', 'verified', 'published', 'penyelidikan', 'investigation',
            'penyidikan', 'prosecution', 'trial', 'vonis',
            'berkekuatan-hukum-tetap', 'Berkekuatan hukum tetap',
            'executed', 'completed', 'closed', 'rejected', 'converted',
        ];

        $statusOptions = $cases->pluck('status')
            ->filter()
            ->unique(fn ($s) => $s['key'] ?? ($s['name'] ?? ''))
            ->sortBy(fn ($s) => ($idx = array_search($s['key'] ?? '', $barOrder)) === false ? 999 : $idx)
            ->values();

        return view('public.dashboard', compact('cases', 'totalCases', 'activeCases', 'closedCases', 'regencyCount', 'statusOptions', 'filter'));
    }
}
