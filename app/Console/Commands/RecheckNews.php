<?php

namespace App\Console\Commands;

use App\Models\Artikel;
use App\Services\News\NewsAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecheckNews extends Command
{
    protected $signature = 'news:recheck {--hide : Sembunyikan (status nonaktif) berita yang tidak lolos, bukan hanya didaftar}';

    protected $description = 'Periksa ulang berita eksternal yang tayang dengan aturan tema terbaru (config/news.php)';

    public function handle(NewsAggregator $aggregator): int
    {
        $rows = DB::table('artikels')
            ->join('artikel_translations', fn ($join) => $join->on('artikel_translations.artikel_id', '=', 'artikels.id')->where('artikel_translations.locale', 'id'))
            ->where('artikels.type', 'eksternal')
            ->where('artikels.status', 'active')
            ->orderBy('artikels.id')
            ->get(['artikels.id', 'artikels.source_name', 'artikel_translations.title', 'artikel_translations.excerpt']);

        $offTopic = $rows->reject(fn ($row) => $aggregator->isRelevant(['title' => (string) $row->title, 'excerpt' => (string) $row->excerpt]));

        $this->info("Dicek {$rows->count()} berita yang tayang; {$offTopic->count()} tidak sesuai tema.");
        foreach ($offTopic as $row) {
            $this->line("  #{$row->id} {$row->title} ({$row->source_name})");
        }

        if ($offTopic->isEmpty()) {
            return self::SUCCESS;
        }

        if (! $this->option('hide')) {
            $this->comment('Jalankan dengan --hide untuk menyembunyikannya. Berita yang disembunyikan bisa ditampilkan lagi dari CMS.');

            return self::SUCCESS;
        }

        // Disembunyikan, bukan dihapus: tetap tercatat (external_id) sehingga tidak ditarik ulang, dan bisa diaktifkan lagi di CMS.
        Artikel::whereIn('id', $offTopic->pluck('id'))->update(['status' => 'inactive']);
        $this->info("{$offTopic->count()} berita disembunyikan.");

        return self::SUCCESS;
    }
}
