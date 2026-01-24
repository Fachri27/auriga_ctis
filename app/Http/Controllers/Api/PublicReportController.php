<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string',
            'email' => 'nullable|email',
            'pekerjaan' => 'nullable|string',
            'kewarganegaraan' => 'nullable|string',
            'status_perkawinan' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'evidence' => 'nullable|array',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // generate unique report_code
        do {
            $code = strtoupper('R-'.Str::random(8));
        } while (Report::where('report_code', $code)->exists());

        $report = Report::create(array_merge($data, [
            'report_code' => $code,
            'created_by' => $request->user()?->id ?? null,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Report submitted',
            'data' => ['report_code' => $report->report_code, 'report' => $report],
        ], 201);
    }
}
