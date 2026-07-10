<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\{CaseModel, Category};
use Illuminate\Http\Request;

class VerifiedCaseController extends Controller
{
    public function index(Request $request)
    {
        $cases = CaseModel::with(['category', 'status', 'translations'])
            ->whereNotNull('published_at')
            ->where('is_public', true)
            ->latest('published_at')
            ->paginate(9);

        // Get all categories used by visible cases on this page
        $allCategoryIds = collect($cases->items())
            ->pluck('category_ids')
            ->flatten()
            ->unique()
            ->filter()
            ->toArray();

        $categories = Category::with('translations')
            ->whereIn('id', $allCategoryIds)
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = '';
            foreach ($cases as $case) {
                $html .= view('front.verified-case-card', compact('case', 'categories'))->render();
            }
            return response()->json([
                'html' => $html,
                'nextPage' => $cases->hasMorePages() ? $cases->currentPage() + 1 : null,
                'hasMore' => $cases->hasMorePages(),
            ]);
        }

        return view('front.verified-cases', compact('cases', 'categories'));
    }
}
