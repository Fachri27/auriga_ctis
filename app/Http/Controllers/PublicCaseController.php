<?php

namespace App\Http\Controllers;

use App\Models\{Artikel, CaseModel, Category};
use App\Services\ReverseGeocoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PublicCaseController extends Controller
{
    /**
     * Detail kasus di /id/kasus/{judul-kode} atau /en/cases/{title-code}. Setiap kasus punya satu alamat resmi per
     * bahasa; judul lama (setelah judul diubah), judul bahasa lain, atau segmen bahasa yang salah dialihkan permanen.
     */
    public function show(Request $request, string $locale, string $slug, ReverseGeocoder $geo)
    {
        $case = CaseModel::findPublicBySlug($slug) ?? abort(404);

        // Bandingkan path saja (bukan URL utuh) agar http/https di balik proxy tidak memicu pengalihan berulang.
        if ('/'.$request->path() !== $case->publicUrl($locale, absolute: false)) {
            return redirect()->to($case->publicUrl($locale), 301);
        }

        return $this->render($case, $locale, $geo);
    }

    /** Alamat lama /{locale}/detail-case/{nomor}: dialihkan permanen ke alamat baru. */
    public function legacy(string $locale, string $caseNumber)
    {
        $case = CaseModel::with('translations')
            ->where('case_number', $caseNumber)
            ->where('is_public', true)
            ->firstOrFail();

        return redirect()->to($case->publicUrl($locale), 301);
    }

    private function render(CaseModel $case, string $locale, ReverseGeocoder $geo)
    {
        app()->setLocale($locale);

        $case->loadMissing(['translations', 'status', 'timelines']);

        $categories = Category::with('translations')
            ->whereIn('id', $case->category_ids ?? [])
            ->get();

        $location = null;

        if ($case->latitude && $case->longitude) {
            $location = $geo->getLocation(
                (float) $case->latitude,
                (float) $case->longitude
            );
        }

        // $artikel = Artikel::with('translation')
        //     ->where('status', 'active')
        //     ->when($case->category_ids, function ($q) use ($case) {
        //         $q->whereIn('category_id', $case->category_ids);  // ← pakai whereIn karena array
        //     })
        //     // ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content', 'categories.slug as category_name')
        //     ->latest()
        //     ->limit(6)
        //     ->get();
        //  dd($artikel);

        // Artikel terkait: sekategori dengan kasus, terbaru dulu. Berita media hanya yang bergambar,
        // sama seperti halaman Artikel (config news.require_image).
        $artikels = DB::table('artikels')
            ->join('artikel_translations', 'artikel_translations.artikel_id', '=', 'artikels.id')
            ->leftJoin('categories', 'categories.id', '=', 'artikels.category_id')
            ->leftJoin('category_translations', function ($join) use ($locale) {
                $join->on('category_translations.category_id', '=', 'categories.id')->where('category_translations.locale', $locale);
            })
            ->where('artikels.status', 'active')
            ->when($case->category_ids, function ($q) use ($case) {
                $q->whereIn('artikels.category_id', $case->category_ids);  // ← pakai whereIn karena array
            })
            ->when(config('news.require_image', true), fn ($q) => $q->where(fn ($q) => $q
                ->where('artikels.type', 'internal')
                ->orWhere(fn ($q) => $q->whereNotNull('artikels.image')->where('artikels.image', '<>', ''))))
            ->where('artikel_translations.locale', $locale)
            ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content',
                DB::raw('COALESCE(category_translations.name, categories.slug) as category_name'))
            ->orderByDesc('artikels.published_at')
            ->orderByDesc('artikels.id')
            ->take(3)
            ->get();

        return view('public.case-detail', compact('case', 'location', 'categories', 'artikels'));  // ← tambah categories
    }
}