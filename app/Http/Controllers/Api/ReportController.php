<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $reports = Report::where('created_by', $user->id)->paginate(20);

        return response()->json(['success' => true, 'message' => 'My reports', 'data' => $reports]);
    }

    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

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

        $report->update($data);

        return response()->json(['success' => true, 'message' => 'Report updated', 'data' => $report]);
    }
}
