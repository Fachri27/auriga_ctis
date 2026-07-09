<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function exportCsv()
    {
        $cases = CaseModel::with(['status', 'category.translations'])->orderBy('created_at', 'desc')->get();

        $filename = 'cases-export-' . now()->format('Y-m-d-Hi') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['No', 'Case Number', 'Category', 'Status', 'Event Date', 'Created At', 'Is Public'];

        $callback = function () use ($cases, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($cases as $i => $c) {
                $catName = $c->category?->translation('id')?->name ?? $c->category?->slug ?? 'Uncategorized';
                fputcsv($file, [
                    $i + 1,
                    $c->case_number,
                    $catName,
                    $c->status?->name ?? 'Unknown',
                    $c->event_date ?? 'N/A',
                    $c->created_at?->format('Y-m-d H:i') ?? 'N/A',
                    $c->is_public ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel()
    {
        $cases = CaseModel::with(['status', 'category.translations'])->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Case Number', 'Category', 'Status', 'Event Date', 'Created At', 'Is Public'];
        foreach (range(0, count($headers) - 1) as $col) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $headers[$col]);
        }
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        foreach ($cases as $i => $c) {
            $row = $i + 2;
            $catName = $c->category?->translation('id')?->name ?? $c->category?->slug ?? 'Uncategorized';
            $sheet->setCellValueByColumnAndRow(1, $row, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $c->case_number);
            $sheet->setCellValueByColumnAndRow(3, $row, $catName);
            $sheet->setCellValueByColumnAndRow(4, $row, $c->status?->name ?? 'Unknown');
            $sheet->setCellValueByColumnAndRow(5, $row, $c->event_date ?? 'N/A');
            $sheet->setCellValueByColumnAndRow(6, $row, $c->created_at?->format('Y-m-d H:i') ?? 'N/A');
            $sheet->setCellValueByColumnAndRow(7, $row, $c->is_public ? 'Yes' : 'No');
        }

        foreach (range(1, 7) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $filename = 'cases-export-' . now()->format('Y-m-d-Hi') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
