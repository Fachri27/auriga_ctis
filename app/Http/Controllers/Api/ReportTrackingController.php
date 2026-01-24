<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;

class ReportTrackingController extends Controller
{
    public function show($report_code)
    {
        $report = Report::where('report_code', $report_code)->first();

        if (! $report) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }

        // Try to find a case linked to this report and collect timeline
        $case = \App\Models\CaseModel::where('report_id', $report->id)->first();

        $timeline = [];
        if ($case) {
            $timeline = \Illuminate\Support\Facades\DB::table('case_timelines')
                ->where('case_id', $case->id)
                ->orderBy('created_at', 'asc')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Report found',
            'data' => [
                'report' => $report,
                'status' => $report->status,
                'category' => $report->category,
                'timeline' => $timeline,
            ],
        ]);
    }
}
