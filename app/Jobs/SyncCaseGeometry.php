<?php

namespace App\Jobs;

use App\Models\CaseModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCaseGeometry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $caseId;

    public function __construct(int $caseId)
    {
        $this->caseId = $caseId;
    }

    public function handle(): void
    {
        $case = CaseModel::find($this->caseId);

        if (! $case) {
            return;
        }

        if (! $case->is_public) {
            // ensure geometry is not public
            DB::table('case_geometries')->where('case_id', $this->caseId)->update(['is_public' => 0, 'updated_at' => now()]);
            return;
        }

        if (is_null($case->latitude) || is_null($case->longitude)) {
            Log::info("SyncCaseGeometry: case {$this->caseId} has no coordinates");
            return;
        }

        $lat = (float) $case->latitude;
        $lon = (float) $case->longitude;

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            Log::warning("SyncCaseGeometry: invalid coords for case {$this->caseId}");
            return;
        }

        $wkt = sprintf('POINT(%.15f %.15f)', $lat, $lon);

        $exists = DB::table('case_geometries')->where('case_id', $this->caseId)->exists();

        if ($exists) {
            DB::statement(
                'UPDATE case_geometries SET geom = ST_GeomFromText(?, 4326), title = ?, category = ?, status = ?, is_public = ?, updated_at = ? WHERE case_id = ?',
                [
                    $wkt,
                    $case->title ?? ('Case ' . $case->case_number),
                    $case->category?->slug ?? null,
                    'published',
                    1,
                    now(),
                    $this->caseId,
                ]
            );
        } else {
            DB::statement(
                'INSERT INTO case_geometries (case_id, geom, title, category, status, is_public, created_at, updated_at) VALUES (?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, ?)',
                [
                    $this->caseId,
                    $wkt,
                    $case->title ?? ('Case ' . $case->case_number),
                    $case->category?->slug ?? null,
                    'published',
                    1,
                    now(),
                    now(),
                ]
            );
        }

        // set map_published_at/by on case if not set
        if (! $case->map_published_at) {
            $case->update(['map_published_at' => now(), 'map_published_by' => $case->published_by ?? auth()?->id()]);
        }
    }
}
