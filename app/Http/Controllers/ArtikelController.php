<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function showArtikel($locale)
    {
        $artikels = DB::table('artikels')
            ->join('artikel_translations', 'artikel_translations.artikel_id', '=', 'artikels.id')
            ->where('artikels.status', 'active')
            ->where('artikel_translations.locale', $locale)
            ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content')
            ->get();

        return view('front.artikels', compact('artikels', 'locale'));
    }
}
