<?php

namespace App\Services;

use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing legal case status transitions.
 *
 * This service ensures that:
 * - Case status changes are only triggered by explicit legal events
 * - All status transitions are authorized and logged
 * - Task completion does NOT affect legal case status
 */
class CaseStatusService
{
    /**
     * Valid legal case status keys (excluding task/workflow statuses)
     */
    private const LEGAL_STATUSES = [
        'open',
        'investigation',
        'prosecution',
        'trial',
        'executed',
        'closed',
        'rejected',
    ];

    /**
     * Valid status transitions map
     * Key = from status, Value = array of allowed to statuses
     */
    private const VALID_TRANSITIONS = [
        'open' => ['investigation', 'rejected'],
        'investigation' => ['prosecution', 'closed', 'rejected'],
        'prosecution' => ['trial', 'closed'],
        'trial' => ['executed', 'closed'],
        'executed' => ['closed'],
        'closed' => [], // Final status, no transitions
        'rejected' => [], // Final status, no transitions
    ];

    /**
     * Status grouping for UI (virtual layer, no DB change)
     */
    private const STATUS_GROUPS = [
        'working' => ['investigation'],
        'decision' => ['prosecution', 'trial'],
        'final' => ['executed', 'closed'],
    ];

    /**
     * Check if a transition from one status to another is valid.
     *
     * @param  string|null  $fromStatusKey  Current status key (null for new cases)
     * @param  string  $toStatusKey  Target status key
     * @return bool True if transition is valid
     */
    public function canTransition(?string $fromStatusKey, string $toStatusKey): bool
    {
        // Validate toStatus is a legal status
        if (! in_array($toStatusKey, self::LEGAL_STATUSES)) {
            return false;
        }

        // If fromStatus is null (new case), allow setting initial statuses
        if ($fromStatusKey === null) {
            return in_array($toStatusKey, ['open', 'investigation']);
        }

        // Validate fromStatus is a legal status
        if (! in_array($fromStatusKey, self::LEGAL_STATUSES)) {
            return false;
        }

        // Check if transition is allowed
        $allowedTransitions = self::VALID_TRANSITIONS[$fromStatusKey] ?? [];

        return in_array($toStatusKey, $allowedTransitions);
    }

    /**
     * Get status group for a given status key.
     *
     * @param  string  $statusKey
     * @return string|null Group name or null if not grouped
     */
    public function getStatusGroup(string $statusKey): ?string
    {
        foreach (self::STATUS_GROUPS as $group => $statuses) {
            if (in_array($statusKey, $statuses)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Get all valid next statuses for a given current status.
     *
     * @param  string|null  $currentStatusKey
     * @return array Array of valid next status keys
     */
    public function getValidNextStatuses(?string $currentStatusKey): array
    {
        if ($currentStatusKey === null) {
            return ['open', 'investigation'];
        }

        return self::VALID_TRANSITIONS[$currentStatusKey] ?? [];
    }

    /**
     * Change case status to a new legal status.
     *
     * This method should ONLY be called for explicit legal events,
     * never for task completion or publishing.
     *
     * @param  int  $caseId  Case ID
     * @param  string  $newStatusKey  Legal status key (e.g., 'investigation', 'prosecution')
     * @param  string|null  $notes  Optional notes for timeline (action name)
     * @param  int|null  $actorId  Actor ID (defaults to auth()->id())
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \InvalidArgumentException
     */
    public function transition(int $caseId, string $newStatusKey, ?string $notes = null, ?int $actorId = null): bool
    {
        return $this->changeStatus($caseId, $newStatusKey, $notes, $actorId);
    }

    /**
     * Change case status to a new legal status (legacy method name, kept for backward compatibility).
     *
     * This method should ONLY be called for explicit legal events,
     * never for task completion or publishing.
     *
     * @param  int  $caseId  Case ID
     * @param  string  $newStatusKey  Legal status key (e.g., 'investigation', 'prosecution')
     * @param  string|null  $notes  Optional notes for timeline (action name)
     * @param  int|null  $actorId  Actor ID (defaults to auth()->id())
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \InvalidArgumentException
     */
    public function changeStatus(int $caseId, string $newStatusKey, ?string $notes = null, ?int $actorId = null): bool
    {
        // Validate status key is a legal status
        if (! in_array($newStatusKey, self::LEGAL_STATUSES)) {
            throw new \InvalidArgumentException(
                "Status '{$newStatusKey}' is not a valid legal case status. ".
                'Legal statuses are: '.implode(', ', self::LEGAL_STATUSES)
            );
        }

        // Authorization check - use granular permission for status changes
        if (! auth()->user()->can('case.status_change')) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'You do not have permission to change case status.'
            );
        }

        $case = CaseModel::findOrFail($caseId);
        $oldStatusKey = $case->status?->key;

        // Validate transition is allowed
        if (! $this->canTransition($oldStatusKey, $newStatusKey)) {
            throw new \InvalidArgumentException(
                "Invalid status transition from '{$oldStatusKey}' to '{$newStatusKey}'. ".
                'Allowed transitions: '.implode(', ', $this->getValidNextStatuses($oldStatusKey))
            );
        }

        // Get status ID
        $statusId = DB::table('statuses')
            ->where('key', $newStatusKey)
            ->value('id');

        if (! $statusId) {
            throw new \InvalidArgumentException("Status '{$newStatusKey}' not found in database.");
        }

        // Check if status is already set
        if ($case->status_id == $statusId) {
            Log::info("Case {$caseId} already has status '{$newStatusKey}'");

            return false;
        }

        // Use provided actorId or fallback to authenticated user
        $finalActorId = $actorId ?? auth()->id();
        $actorName = auth()->user()->name ?? 'System';

        DB::beginTransaction();

        try {
            // Update case status
            $case->update([
                'status_id' => $statusId,
                'updated_at' => now(),
            ]);

            // Always log to timeline with action name or default message
            $timelineNotes = $notes ?? "Action: Status changed from '{$oldStatusKey}' to '{$newStatusKey}' by {$actorName}.";

            DB::table('case_timelines')->insert([
                'case_id' => $caseId,
                'actor_id' => $finalActorId,
                'notes' => $timelineNotes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("Case {$caseId} status changed from '{$oldStatusKey}' to '{$newStatusKey}' by user {$finalActorId}");

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error changing case {$caseId} status: ".$th->getMessage());
            throw $th;
        }
    }

    /**
     * Force change status (audit-only). Bypasses transition validation but requires explicit audit permission.
     * This should be restricted to super-admins or users with `case.audit` permission.
     *
     * @param int $caseId
     * @param string $newStatusKey
     * @param string|null $notes
     * @param int|null $actorId
     * @return bool
     */
    public function forceChangeStatus(int $caseId, string $newStatusKey, ?string $notes = null, ?int $actorId = null): bool
    {
        // Ensure caller has audit capability
        if (! (auth()->user()->can('case.audit') || auth()->user()->hasRole('super-admin'))) {
            throw new \Illuminate\Auth\Access\AuthorizationException('You do not have permission to force change case status.');
        }

        // Validate status key exists as legal status
        if (! in_array($newStatusKey, self::LEGAL_STATUSES)) {
            throw new \InvalidArgumentException("Status '{$newStatusKey}' is not a valid legal case status.");
        }

        $statusId = DB::table('statuses')->where('key', $newStatusKey)->value('id');
        if (! $statusId) {
            throw new \InvalidArgumentException("Status '{$newStatusKey}' not found in database.");
        }

        $case = CaseModel::findOrFail($caseId);

        // If already set, do nothing
        if ($case->status_id == $statusId) {
            Log::info("Case {$caseId} already has status '{$newStatusKey}' (force)");

            return false;
        }

        $finalActorId = $actorId ?? auth()->id();
        $actorName = auth()->user()->name ?? 'System';

        DB::beginTransaction();

        try {
            $case->update(['status_id' => $statusId, 'updated_at' => now()]);

            $timelineNotes = $notes ?? "Force: Status changed to '{$newStatusKey}' by {$actorName} (audit override).";

            DB::table('case_timelines')->insert([
                'case_id' => $caseId,
                'actor_id' => $finalActorId,
                'notes' => $timelineNotes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::warning("Case {$caseId} force-changed to '{$newStatusKey}' by user {$finalActorId}");

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error force-changing case {$caseId} status: ".$th->getMessage());
            throw $th;
        }
    }

    /**
     * Get current legal status of a case.
     *
     * @return string|null Status key or null if not set
     */
    public function getCurrentStatus(int $caseId): ?string
    {
        $case = CaseModel::findOrFail($caseId);

        return $case->status?->key;
    }

    /**
     * Check if a status key is a valid legal status.
     */
    public function isValidLegalStatus(string $statusKey): bool
    {
        return in_array($statusKey, self::LEGAL_STATUSES);
    }
}
