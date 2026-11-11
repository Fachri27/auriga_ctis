<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function showArtikel(Request $request, $locale)
    {
        // Filter jenis: analisis (artikel internal yang ditulis di CMS) atau berita (dari media lain).
        $jenis = in_array($request->query('jenis'), ['analisis', 'berita'], true) ? $request->query('jenis') : null;
        $q = $this->searchText($request->query('q'));
        $terms = $this->searchTerms($q);
        // Tidak ada kata yang bisa dicari (mis. hanya simbol atau satu huruf): anggap tanpa pencarian.
        $q = $terms ? $q : '';

        // Analisis Auriga dan berita media dalam satu daftar, terbaru dulu.
        $artikels = $this->query($locale)
            ->where(fn ($q) => $q
                ->when($jenis !== 'berita', fn ($q) => $q->orWhere('artikels.type', 'internal'))
                ->when($jenis !== 'analisis', fn ($q) => $q->orWhere(fn ($q) => $q
                    ->where('artikels.type', 'eksternal')
                    // Berita hanya yang bergambar; yang lain menyusul setelah gambarnya ditemukan (NewsEnricher).
                    ->when(config('news.require_image', true), fn ($q) => $q->whereNotNull('artikels.image')->where('artikels.image', '<>', '')))))
            // Pencarian: setiap kata harus ada di judul, ringkasan, nama media, atau kategori.
            ->when($terms, fn ($query) => $query->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->where(fn ($match) => $match
                        ->whereLike('artikel_translations.title', "%{$term}%")
                        ->orWhereLike('artikel_translations.excerpt', "%{$term}%")
                        ->orWhereLike('artikels.source_name', "%{$term}%")
                        ->orWhereLike('category_translations.name', "%{$term}%"));
                }
            }))
            ->orderByDesc('artikels.published_at')
            ->orderByDesc('artikels.id')
            ->paginate(20)
            ->appends(array_filter(['jenis' => $jenis, 'q' => $q]));

        // Infinite scroll: muatan berikutnya diminta lewat fetch, cukup kartunya.
        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('front.components.artikel-cards', compact('artikels'))->render(),
                'next' => $artikels->nextPageUrl(),
            ]);
        }

        return view('front.artikels', compact('artikels', 'jenis', 'locale', 'q'));
    }

    /** Teks pencarian dari URL: spasi dirapikan, maksimal 100 karakter. */
    private function searchText(mixed $value): string
    {
        return is_string($value) ? mb_substr(trim(preg_replace('/\s+/u', ' ', $value)), 0, 100) : '';
    }

    /**
     * Kata yang dicari (maksimal 6, minimal 2 huruf). Karakter pola LIKE (% _ \) dibuang agar
     * dicari apa adanya, bukan sebagai pola.
     *
     * @return list<string>
     */
    private function searchTerms(string $q): array
    {
        $words = preg_split('/\s+/u', trim(str_replace(['%', '_', '\\'], ' ', $q)), -1, PREG_SPLIT_NO_EMPTY);

        return array_slice(array_values(array_filter($words, fn ($word) => mb_strlen($word) >= 2)), 0, 6);
    }

    private function query(string $locale)
    {
        return DB::table('artikels')
            ->join('artikel_translations', 'artikel_translations.artikel_id', '=', 'artikels.id')
            ->leftJoin('categories', 'categories.id', '=', 'artikels.category_id')
            ->leftJoin('category_translations', function ($join) use ($locale) {
                $join->on('category_translations.category_id', '=', 'categories.id')->where('category_translations.locale', $locale);
            })
            ->where('artikels.status', 'active')
            ->where('artikel_translations.locale', $locale)
            ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content',
                DB::raw('COALESCE(category_translations.name, categories.slug) as category_name'));
    }
}
