<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\{CaseModel, Category};

class VerifiedCaseController extends Controller
{
    public function index()
    {
        $cases = CaseModel::with(['category', 'status', 'translations'])
            ->whereNotNull('published_at')
            ->where('is_public', true)
            ->orderByDesc('event_date')
            ->get();

        // Get all categories used by all public cases
        $allCategoryIds = $cases->pluck('category_ids')
            ->flatten()
            ->unique()
            ->filter()
            ->toArray();

        $categories = Category::with('translations')
            ->whereIn('id', $allCategoryIds)
            ->get();

        return view('front.verified-cases', compact('cases', 'categories'));
    }
}
