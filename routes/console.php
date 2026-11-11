<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Console\ClosureCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('chart:sync')->everyFiveMinutes()->withoutOverlapping()->runInBackground();

// Berita penegakan hukum lingkungan & SDA untuk halaman Artikel (config/news.php)
Schedule::command('news:pull')->hourly()->withoutOverlapping()->runInBackground();
