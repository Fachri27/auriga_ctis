<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with statistics, charts, and case data.
     *
     * Fetches:
     * - Total cases count
     * - New reports (cases created today)
     * - Published cases count
     * - Unpublished cases count
     * - Cases grouped by status
     * - Cases grouped by category
     * - Latest 10 cases
     * - Cases with location data (for map)
     */
    public function index()
    {
        // Statistics: Total cases
        $totalCases = CaseModel::count();

        // Statistics: New reports today (cases created today)
        $newReportsToday = CaseModel::whereDate('created_at', today())->count();

        // Statistics: Published cases
        $publishedCases = CaseModel::where('is_public', true)->count();

        // Statistics: Unpublished cases
        $unpublishedCases = CaseModel::where('is_public', false)->count();

        // Group cases by status
        // Use join to get status information with counts
        $casesByStatus = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->select('cases.status_id', 'statuses.key as status_key', 'statuses.name as status_name', DB::raw('count(*) as count'))
            ->groupBy('cases.status_id', 'statuses.key', 'statuses.name')
            ->get()
            ->map(function ($item) {
                return [
                    'status_id' => $item->status_id,
                    'status_key' => $item->status_key ?? 'unknown',
                    'status_name' => $item->status_name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        // Group cases by category
        // Use join to get category information with counts
        $casesByCategory = DB::table('cases')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('category_translations', function ($join) {
                $join->on('category_translations.category_id', '=', 'categories.id')
                    ->where('category_translations.locale', '=', 'id');
            })
            ->select(
                'cases.category_id',
                'categories.slug',
                'category_translations.name as category_name',
                DB::raw('count(*) as count')
            )
            ->groupBy('cases.category_id', 'categories.slug', 'category_translations.name')
            ->get()
            ->map(function ($item) {
                $categoryName = $item->category_name ?? $item->slug ?? 'Uncategorized';

                return [
                    'category_id' => $item->category_id,
                    'category_name' => $categoryName,
                    'count' => $item->count,
                ];
            });

        // Reports over time (last 30 days)
        // Get daily case counts for the last 30 days
        $reportsOverTime = CaseModel::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });

        // Latest 10 cases with relationships
        $latestCases = CaseModel::with(['status:id,key,name', 'category.translations'])
            ->latest('created_at')
            ->take(10)
            ->get();

        // Cases with location data for map
        // Get all cases (published + unpublished) with valid coordinates
        $casesWithLocation = CaseModel::with(['status:id,key,name', 'category.translations'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($case) {
                // Determine marker color based on status
                // Red → new/unverified
                // Yellow → investigation
                // Green → published
                $statusKey = $case->status?->key ?? 'unknown';
                $color = 'gray'; // default

                if (in_array($statusKey, ['new', 'unverified', 'pending'])) {
                    $color = 'red';
                } elseif (in_array($statusKey, ['investigation', 'in_progress', 'under_review'])) {
                    $color = 'yellow';
                } elseif ($case->is_public) {
                    $color = 'green';
                }

                $categoryName = $case->category?->translation('id')?->name
                    ?? $case->category?->slug
                    ?? 'Uncategorized';

                return [
                    'id' => $case->id,
                    'case_number' => $case->case_number,
                    'latitude' => $case->latitude,
                    'longitude' => $case->longitude,
                    'status_key' => $statusKey,
                    'status_name' => $case->status?->name ?? 'Unknown',
                    'category_name' => $categoryName,
                    'is_public' => $case->is_public,
                    'color' => $color,
                ];
            });

        return view('dashboard', compact(
            'totalCases',
            'newReportsToday',
            'publishedCases',
            'unpublishedCases',
            'casesByStatus',
            'casesByCategory',
            'reportsOverTime',
            'latestCases',
            'casesWithLocation'
        ));
    }
}
