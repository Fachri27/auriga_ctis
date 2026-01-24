<?php

namespace App\Services;

use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SimpleCaseService
 *
 * A lightweight abstraction for public/CMS UIs that hides advanced workflow
 * constructs (process → task → requirements) from non-technical users.
 *
 * This service intentionally maps a small set of user-facing "steps" to
 * existing legal status keys stored in `statuses`.
 *
 * It preserves the advanced workflow code but keeps the public UI simple.
 */
class SimpleCaseService
{
    /**
     * Mapping between simplified steps and existing status keys.
     * Add or tweak mappings as the legal status set changes.
     */
    private const STEP_MAP = [
        'created' => 'open',
        'verified' => 'open',
        'under_investigation' => 'investigation',
        'follow_up' => 'investigation',
        'completed' => 'closed',
    ];

    /**
     * Map a simplified step to a status key.
     * Throws an exception if the mapping does not exist.
     */
    public function mapStepToStatusKey(string $step): string
    {
        if (! isset(self::STEP_MAP[$step])) {
            throw new \InvalidArgumentException("Step '{$step}' is not mapped to any status key.");
        }

        return self::STEP_MAP[$step];
    }

    /**
     * Change the case status according to a simplified step.
     * Uses CaseStatusService internally to preserve validation/authorization.
     */
    public function changeStep(int $caseId, string $step, ?string $notes = null): bool
    {
        $statusKey = $this->mapStepToStatusKey($step);

        // Use existing legal status service which contains validation and logging
        return app(CaseStatusService::class)->changeStatus($caseId, $statusKey, $notes);
    }

    /**
     * Add a simple timeline note. This is the preferred way to record events
     * from the simplified UI (title optional, notes required).
     */
    public function addTimelineNote(int $caseId, string $notes, ?int $actorId = null): void
    {
        try {
            DB::table('case_timelines')->insert([
                'case_id' => $caseId,
                'actor_id' => $actorId,
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to add simplified timeline note: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper: return a human-friendly simplified status label for a case.
     * Falls back to status name if mapping is not obvious.
     */
    public function getSimpleStatusLabel(CaseModel $case): string
    {
        $statusKey = $case->status?->key;

        if (! $statusKey) {
            return 'Unknown';
        }

        // Reverse map to nice label if possible
        $flip = array_flip(self::STEP_MAP);

        if (isset($flip[$statusKey])) {
            // Convert snake_case key to normal words
            return Str::title(str_replace('_', ' ', $flip[$statusKey]));
        }

        return $case->status?->name ?? 'Unknown';
    }
}
