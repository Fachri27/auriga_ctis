<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Permintaan Livewire (/livewire/update) ikut memakai bahasa halaman asalnya (/id atau /en),
        // agar pesan dari komponen di halaman publik tidak jatuh ke APP_LOCALE.
        Livewire::addPersistentMiddleware([SetLocale::class]);

        if (!app()->runningInConsole()) {
            $baseUrl = request()->getSchemeAndHttpHost();
            config(['filesystems.disks.public.url' => $baseUrl . '/storage']);
        }
    }
}
