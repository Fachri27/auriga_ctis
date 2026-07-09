<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;

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

        $cases = $query->orderByDesc('event_date')->get()->map(function ($c) use ($locale, $allCategoryNames) {
            $translation = $c->translations->where('locale', $locale)->first()
                ?? $c->translations->first();

            $categoryIds = is_array($c->category_ids) ? $c->category_ids : [];
            $categoryNames = collect($categoryIds)
                ->map(fn($id) => $allCategoryNames->get($id))
                ->filter()
                ->implode(', ');

            return [
                'case_number' => $c->case_number,
                'title' => $translation?->title ?? $c->case_number,
                'category' => $categoryNames ? ['name' => $categoryNames] : null,
                'status' => $c->status ? ['name' => $c->status->name, 'key' => $c->status->key] : null,
                'province' => $c->province?->name,
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

        return view('public.dashboard', compact('cases', 'totalCases', 'activeCases', 'closedCases', 'provinceCount', 'filter'));
    }
}
