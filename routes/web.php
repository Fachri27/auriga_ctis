<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/id');
});

Route::group([
    'prefix' => '{locale}',
    'middleware' => ['setlocale'],
    'where' => ['locale' => 'en|id'],
], function () {

    Route::get('/', function () {
        return view('front.dashboard-user');
    })->name('dashboard-user');

    Route::get('/about', function () {
        return view('front.about');
    })->name('about-user');
    
});
