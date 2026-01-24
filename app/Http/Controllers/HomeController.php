<?php

namespace App\Http\Controllers;

// use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $cases = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', 'id');
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.*',
                'statuses.key',
                'statuses.name',
                'reports.evidence',
                'categories.slug',
            )
            // ->latest()
            ->get();

        $case = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', 'id');
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.*',
                'statuses.key',
                'statuses.name',
                'reports.evidence',
                'categories.slug',
            )
            // ->latest()
            ->first();

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
            ->where('cases.is_public', true)
            ->get()
            ->map(function ($item) {
                $categoryName = $item->category_name ?? $item->slug ?? 'Uncategorized';

                return [
                    'category_id' => $item->category_id,
                    'category_name' => $categoryName,
                    'count' => $item->count,
                ];
            });
        
            $categories = $casesByCategory->pluck('category_name');
            $caseCounts = $casesByCategory->pluck('count');
            

        return view('front.dashboard-user', compact('cases', 'case', 'casesByCategory', 'categories', 'caseCounts'));
    }

    public function getCases($caseNumber)
    {
        // list for sidebar/cards
        $cases = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', 'id');
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.*',
                'statuses.key',
                'statuses.name',
                'reports.evidence',
                'categories.slug',
            )
            ->get();

        // specific case to show (must be public)
        $case = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', 'id');
            })
            ->where('cases.is_public', true)
            ->where('cases.case_number', $caseNumber)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.*',
                'statuses.key',
                'statuses.name',
                'reports.evidence',
                'categories.slug',
            )
            ->first();

        if (! $case) {
            abort(404, 'Case not found');
        }

        return view('front.dashboard-user', compact('cases', 'case'));
    }
}
