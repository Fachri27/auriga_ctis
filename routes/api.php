<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicCaseController;
use App\Http\Controllers\Api\PublicReportController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReportTrackingController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Public report submission (guest or authenticated)
Route::post('reports', [PublicReportController::class, 'store']);

// Tracking for guests
Route::get('public/reports/{code}', [ReportTrackingController::class, 'show']);

// Protected routes for authenticated users (sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // User's own reports
    Route::get('user/reports', [ReportController::class, 'index']);
    Route::put('reports/{report}', [ReportController::class, 'update']);
});

// Fallback JSON for API
Route::fallback(function () {
    return response()->json(['success' => false, 'message' => 'Not Found'], 404);
});

// Route::get('/public/cases/{case_number}',
//     [PublicCaseController::class, 'show']
// );
