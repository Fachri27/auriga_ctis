<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReplaceStorageUrl extends Command
{
    protected $signature = 'db:replace-storage-url
        {--from= : Old domain URL (e.g. https://ctis.environmentaldefender.id)}
        {--to= : New domain URL (e.g. https://stg.greendefender.id)}
        {--dry-run : Only count matches, do not update}';

    protected $description = 'Replace old storage URL in all content columns';

    public function handle(): int
    {
        $from = $this->option('from');
        $to = $this->option('to');

        if (!$from || !$to) {
            $this->error('Both --from and --to are required.');
            return Command::FAILURE;
        }

        $tables = [
            ['table' => 'about_page_translations', 'columns' => ['content', 'vision', 'mission']],
            ['table' => 'artikel_translations', 'columns' => ['content', 'excerpt']],
            ['table' => 'case_translations', 'columns' => ['description', 'summary', 'perkembangan', 'dugaan_permasalahan', 'pembelajaran']],
            ['table' => 'cases', 'columns' => ['instansi', 'status_narasi', 'sumber', 'publish_notes']],
            ['table' => 'case_actions', 'columns' => ['description']],
            ['table' => 'case_actors', 'columns' => ['description']],
            ['table' => 'case_discussions', 'columns' => ['message']],
            ['table' => 'case_geometries', 'columns' => ['case_description']],
            ['table' => 'case_timelines', 'columns' => ['notes']],
            ['table' => 'category_translations', 'columns' => ['description']],
            ['table' => 'report_translations', 'columns' => ['description']],
            ['table' => 'task_translations', 'columns' => ['description']],
        ];

        $totalAffected = 0;

        foreach ($tables as $t) {
            foreach ($t['columns'] as $column) {
                $count = DB::table($t['table'])
                    ->where($column, 'like', '%' . $from . '%')
                    ->count();

                if ($count > 0) {
                    $this->line("[{$t['table']}.{$column}] Found {$count} row(s) with old URL.");

                    if (!$this->option('dry-run')) {
                        $updated = DB::table($t['table'])
                            ->where($column, 'like', '%' . $from . '%')
                            ->update([$column => DB::raw("REPLACE({$column}, '{$from}', '{$to}')")]);
                        $totalAffected += $updated;
                        $this->info("  ✓ Updated {$updated} row(s).");
                    }
                }
            }
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No changes made.');
        } else {
            $this->info("Done. Total rows updated: {$totalAffected}");
        }

        return Command::SUCCESS;
    }
}
