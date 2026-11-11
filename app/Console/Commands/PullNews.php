<?php

namespace App\Console\Commands;

use App\Services\News\{NewsAggregator, NewsEnricher};
use Illuminate\Console\Command;

class PullNews extends Command
{
    protected $signature = 'news:pull {--enrich=40 : Jumlah berita tanpa gambar yang dilengkapi dari halaman aslinya}';

    protected $description = 'Tarik berita penegakan hukum lingkungan & SDA (Google News, RSS media) menjadi artikel eksternal';

    public function handle(NewsAggregator $aggregator, NewsEnricher $enricher): int
    {
        $result = $aggregator->pull();

        $this->info("Dibaca {$result['fetched']} berita, {$result['relevant']} relevan, {$result['created']} berita baru ditambahkan, {$result['upgraded']} diperbarui dengan gambar.");

        if ($result['failed_sources']) {
            $this->warn('Gagal membaca: '.implode(', ', $result['failed_sources']));
        }

        $enriched = $enricher->enrich((int) $this->option('enrich'));
        $this->info("Dicek {$enriched['checked']} berita tanpa gambar, {$enriched['images']} mendapat gambar dari halaman aslinya.");

        if ($enriched['throttled']) {
            $this->warn('Google sedang membatasi permintaan; sisanya dilanjutkan pada penarikan berikutnya.');
        }

        return self::SUCCESS;
    }
}
