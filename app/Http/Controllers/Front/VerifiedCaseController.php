<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;

class VerifiedCaseController extends Controller
{
    public function index()
    {
        $cases = CaseModel::with(['category', 'status'])
            ->whereNotNull('published_at')
            ->where('is_public', true)
            ->orderByDesc('event_date')
            ->get();
        return view('front.verified-cases', compact('cases'));
    }
}
