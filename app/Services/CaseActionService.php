<?php

namespace App\Services;

use App\Models\CaseModel;
use Illuminate\Support\Facades\Log;

/**
 * CaseActionService
 *
 * Maps user-friendly actions to case status transitions.
 * This service simplifies the internal workflow by converting
 * action-based UI to status changes while maintaining legal traceability.
 */
class CaseActionService
{
    /**
     * Action to status mapping
     * Each action maps to a target status that will be transitioned to
     */
    private const ACTION_TO_STATUS = [
        'verify_report' => 'open', // Report verification (report flow)
        'convert_to_case' => 'investigation', // Convert report to case
        'complete_investigation' => 'prosecution', // Investigation → Prosecution
        'start_prosecution' => 'prosecution', // Alternative action name
        'start_trial' => 'trial', // Prosecution → Trial
        'execute_verdict' => 'executed', // Trial → Executed
        'close_case' => 'closed', // Any → Closed (if allowed)
        'reject_case' => 'rejected', // Open/Investigation → Rejected
    ];

    /**
     * Action labels for UI display (English - for logging/API)
     */
    private const ACTION_LABELS = [
        'verify_report' => 'Verify Report',
        'convert_to_case' => 'Convert to Case',
        'complete_investigation' => 'Complete Investigation',
        'start_prosecution' => 'Start Prosecution',
        'start_trial' => 'Start Trial',
        'execute_verdict' => 'Execute Verdict',
        'close_case' => 'Close Case',
        'reject_case' => 'Reject Case',
    ];

    /**
     * Action labels in Indonesian for UI display (Human-friendly)
     */
    private const ACTION_LABELS_ID = [
        'verify_report' => 'Verifikasi Laporan',
        'convert_to_case' => 'Konversi ke Case',
        'complete_investigation' => 'Naik ke Penuntutan',
        'start_prosecution' => 'Mulai Penuntutan',
        'start_trial' => 'Mulai Persidangan',
        'execute_verdict' => 'Eksekusi Putusan',
        'close_case' => 'Tutup Kasus',
        'reject_case' => 'Tolak Kasus',
    ];

    /**
     * Priority list used to determine primary action for the UI
     */
    private const ACTION_PRIORITY = [
        'convert_to_case',
        'complete_investigation',
        'start_prosecution',
        'start_trial',
        'execute_verdict',
        'close_case',
        'reject_case',
    ];

    /**
     * Allowed actions per current status
     * Defines which actions are available based on current case status
     */
    private const ALLOWED_ACTIONS_BY_STATUS = [
        'open' => ['convert_to_case', 'reject_case'],
        'investigation' => ['complete_investigation', 'close_case', 'reject_case'],
        'prosecution' => ['start_trial', 'close_case'],
        'trial' => ['execute_verdict', 'close_case'],
        'executed' => ['close_case'],
        'closed' => [], // Final status, no actions
        'rejected' => [], // Final status, no actions
        null => ['convert_to_case'], // New/uninitialized cases
    ];

    /**
     * Get all allowed actions for a case based on its current status.
     *
     * @param  CaseModel|int  $case  Case model or case ID
     * @return array Array of action keys
     */
    public function getAllowedActions($case): array
    {
        $caseModel = $case instanceof CaseModel ? $case : CaseModel::findOrFail($case);
        $currentStatus = $caseModel->status?->key;

        $allowed = self::ALLOWED_ACTIONS_BY_STATUS[$currentStatus] ?? [];

        // Order actions by priority defined in ACTION_PRIORITY
        usort($allowed, function ($a, $b) {
            $p = self::ACTION_PRIORITY;
            $ia = array_search($a, $p);
            $ib = array_search($b, $p);
            $ia = $ia === false ? PHP_INT_MAX : $ia;
            $ib = $ib === false ? PHP_INT_MAX : $ib;

            return $ia <=> $ib;
        });

        return $allowed;
    }

    /**
     * Get action label for display in UI (English).
     *
     * @param  string  $actionKey
     * @return string Human-readable action label
     */
    public function getActionLabel(string $actionKey): string
    {
        return self::ACTION_LABELS[$actionKey] ?? ucfirst(str_replace('_', ' ', $actionKey));
    }

    /**
     * Get action label in Indonesian for UI display.
     *
     * @param  string  $actionKey
     * @return string Indonesian action label
     */
    public function getActionLabelIndonesian(string $actionKey): string
    {
        return self::ACTION_LABELS_ID[$actionKey] ?? ucfirst(str_replace('_', ' ', $actionKey));
    }

    /**
     * Check if an action is allowed for a case.
     *
     * @param  CaseModel|int  $case  Case model or case ID
     * @param  string  $actionKey  Action key to check
     * @return bool True if action is allowed
     */
    public function isActionAllowed($case, string $actionKey): bool
    {
        $allowedActions = $this->getAllowedActions($case);

        return in_array($actionKey, $allowedActions);
    }

    /**
     * Execute an action on a case.
     * This will transition the case to the appropriate status based on the action.
     *
     * @param  int  $caseId  Case ID
     * @param  string  $actionKey  Action key to execute
     * @param  string|null  $notes  Optional notes for timeline
     * @param  int|null  $actorId  Actor ID (defaults to auth()->id())
     * @return bool True if action was executed successfully
     * @throws \InvalidArgumentException If action is not valid or not allowed
     */
    public function executeAction(int $caseId, string $actionKey, ?string $notes = null, ?int $actorId = null): bool
    {
        // Validate action exists
        if (! isset(self::ACTION_TO_STATUS[$actionKey])) {
            throw new \InvalidArgumentException("Action '{$actionKey}' is not a valid case action.");
        }

        $case = CaseModel::findOrFail($caseId);

        // Check if action is allowed for current status
        if (! $this->isActionAllowed($case, $actionKey)) {
            $currentStatus = $case->status?->key ?? 'null';
            throw new \InvalidArgumentException(
                "Action '{$actionKey}' is not allowed for case with status '{$currentStatus}'."
            );
        }

        // Get target status for this action
        $targetStatus = self::ACTION_TO_STATUS[$actionKey];
        $actionLabel = $this->getActionLabel($actionKey);

        // Build timeline notes
        $timelineNotes = $notes ?? "Action: {$actionLabel}";

        // Use CaseStatusService to perform the transition
        $statusService = app(CaseStatusService::class);

        try {
            return $statusService->transition($caseId, $targetStatus, $timelineNotes, $actorId);
        } catch (\Throwable $th) {
            Log::error("Error executing action '{$actionKey}' on case {$caseId}: ".$th->getMessage());
            throw $th;
        }
    }

    /**
     * Get status group for UI display (virtual grouping).
     *
     * @param  string|null  $statusKey
     * @return string Status group or status key itself
     */
    public function getStatusGroupForDisplay(?string $statusKey): string
    {
        if ($statusKey === null) {
            return 'new';
        }

        $statusService = app(CaseStatusService::class);
        $group = $statusService->getStatusGroup($statusKey);

        if ($group) {
            return $group;
        }

        // Return friendly name for ungrouped statuses
        return match ($statusKey) {
            'open' => 'review',
            'rejected' => 'final',
            default => $statusKey,
        };
    }

    /**
     * Get all actions with their labels and allowed statuses.
     * Useful for building UI action menus.
     *
     * @return array Action metadata
     */
    public function getActionsMetadata(): array
    {
        $metadata = [];

        foreach (self::ACTION_TO_STATUS as $actionKey => $targetStatus) {
            $metadata[$actionKey] = [
                'key' => $actionKey,
                'label' => $this->getActionLabel($actionKey),
                'target_status' => $targetStatus,
                'allowed_for_statuses' => $this->getStatusesForAction($actionKey),
            ];
        }

        return $metadata;
    }

    /**
     * Get all statuses that allow a specific action.
     *
     * @param  string  $actionKey
     * @return array Array of status keys
     */
    private function getStatusesForAction(string $actionKey): array
    {
        $statuses = [];

        foreach (self::ALLOWED_ACTIONS_BY_STATUS as $status => $allowedActions) {
            if (in_array($actionKey, $allowedActions)) {
                $statuses[] = $status;
            }
        }

        return $statuses;
    }
}

