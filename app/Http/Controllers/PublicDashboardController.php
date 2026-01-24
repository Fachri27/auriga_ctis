<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;

class PublicDashboardController extends Controller
{
    /**
     * Display the public case dashboard.
     *
     * Query only public cases with valid coordinates,
     * eager load relationships, and pass to blade view.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $filter = $request->get('filter', null); // accepted: active|published|closed|null

        // Base query: only public cases with coordinates
        $query = CaseModel::with(['category', 'status'])
            ->where('is_public', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Apply filter
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
        } elseif ($filter === 'published') {
            // already covered by base query (is_public = true), no extra where needed
        }

        // Order by event date desc for map relevance
        $cases = $query->orderByDesc('event_date')->get();

        // Summary numbers (derived from DB for accuracy)
        $totalCases = CaseModel::where('is_public', true)->count();

        $activeCases = CaseModel::where('is_public', true)
            ->whereHas('status', function ($q) {
                $q->where('key', '<>', 'closed');
            })->count();

        $closedCases = CaseModel::where('is_public', true)
            ->whereHas('status', function ($q) {
                $q->where('key', 'closed');
            })->count();

        

        return view('public.dashboard', compact('cases', 'totalCases', 'activeCases', 'closedCases', 'filter'));
    }
}
