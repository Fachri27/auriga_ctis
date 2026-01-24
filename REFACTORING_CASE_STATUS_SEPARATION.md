# Refactoring: Case Status Separation from Task Completion

## Overview

This refactoring separates **task completion** from **legal case status**, ensuring that case status changes only occur due to explicit legal events, never automatically from task completion or publishing.

---

## Key Changes

### 1. **Database Schema Changes**

#### Migration: `add_task_completion_fields_to_cases_table`

**Added Fields:**

-   `is_tasks_completed` (boolean, default: false) - Tracks if all tasks are completed
-   `tasks_completed_at` (timestamp, nullable) - When all tasks were completed

**Purpose:** These fields track task completion separately from legal case status.

**Note:** The `is_public` and `published_at` fields already existed and are now used correctly (not as status).

---

### 2. **New Service: CaseStatusService**

**Location:** `app/Services/CaseStatusService.php`

**Purpose:** Centralized service for managing legal case status transitions.

**Key Features:**

-   ✅ Validates that only legal statuses can be set (excludes 'completed', 'published')
-   ✅ Authorization checks using Laravel permissions
-   ✅ Automatic timeline logging
-   ✅ Transaction safety
-   ✅ Clear separation of concerns

**Valid Legal Statuses:**

```php
'open', 'investigation', 'prosecution', 'trial', 'executed', 'closed', 'rejected'
```

**Usage:**

```php
$statusService = app(CaseStatusService::class);
$statusService->changeStatus($caseId, 'prosecution', 'Case moved to prosecution phase.');
```

---

### 3. **Refactored CaseDetail Component**

#### A. **Removed: `checkAutoComplateCase()`**

**Old Behavior:** Automatically changed case status to 'completed' when all tasks approved.

**Replaced With: `checkTaskCompletion()`**
**New Behavior:**

-   ✅ Only updates `is_tasks_completed` and `tasks_completed_at` flags
-   ✅ Does NOT change `status_id`
-   ✅ Logs to timeline but clarifies it's task completion, not status change
-   ✅ Can reset flags if tasks become incomplete again

#### B. **Refactored: `approveTask()`**

**Changes:**

-   ✅ Removed call to `checkAutoComplateCase()`
-   ✅ Now calls `checkTaskCompletion()` which only updates task flags
-   ✅ Task approval no longer triggers case status change

#### C. **Refactored: `publishCases()`**

**Changes:**

-   ✅ Removed status lookup/creation for 'published'
-   ✅ Removed `status_id` update
-   ✅ Only updates `is_public` and `published_at` flags
-   ✅ Timeline note clarifies: "Case published (made public). Legal status unchanged."

#### D. **Added: `changeCaseStatus()`**

**Purpose:** Public method for legal status transitions.

**Features:**

-   ✅ Uses `CaseStatusService` for validation and authorization
-   ✅ Reloads case data after status change
-   ✅ Provides user feedback via session flash messages
-   ✅ Handles authorization and validation errors gracefully

---

### 4. **Updated CaseModel**

**Added to `$fillable`:**

-   `is_tasks_completed`
-   `tasks_completed_at`

**Added to `$casts`:**

-   `tasks_completed_at` => 'datetime'
-   `is_tasks_completed` => 'boolean'
-   `is_public` => 'boolean' (already existed, now explicit)

---

## Architecture Principles

### ✅ Separation of Concerns

1. **Task Completion** → Tracked via `is_tasks_completed` flag
2. **Case Visibility** → Tracked via `is_public` flag
3. **Legal Status** → Tracked via `status_id` (only changed by legal events)

### ✅ Event-Driven Status Changes

Case status changes are now:

-   ✅ Explicit (not automatic)
-   ✅ Authorized (permission checks)
-   ✅ Logged (timeline entries)
-   ✅ Validated (only legal statuses allowed)

### ✅ No Automatic Status Changes

**Before:**

-   ❌ Task approval → Auto status change to 'completed'
-   ❌ Publishing → Auto status change to 'published'

**After:**

-   ✅ Task approval → Only updates task completion flags
-   ✅ Publishing → Only updates visibility flags
-   ✅ Status changes → Only via explicit `changeCaseStatus()` calls

---

## Migration Guide

### Running the Migration

```bash
php artisan migrate
```

This will add the new fields without breaking existing data.

### Data Migration (if needed)

If you have existing cases with 'completed' or 'published' status, you may want to:

1. **For 'completed' status:**

    ```sql
    UPDATE cases
    SET is_tasks_completed = true,
        tasks_completed_at = updated_at
    WHERE status_id IN (SELECT id FROM statuses WHERE key = 'completed');
    ```

2. **For 'published' status:**

    ```sql
    UPDATE cases
    SET is_public = true,
        published_at = updated_at
    WHERE status_id IN (SELECT id FROM statuses WHERE key = 'published');
    ```

3. **Reset status_id for these cases:**
    ```sql
    UPDATE cases
    SET status_id = (SELECT id FROM statuses WHERE key = 'investigation')
    WHERE status_id IN (
        SELECT id FROM statuses WHERE key IN ('completed', 'published')
    );
    ```

---

## Usage Examples

### Changing Case Status (Legal Event)

```php
// In Livewire component
$this->changeCaseStatus('prosecution', 'Case moved to prosecution after investigation completed.');

// Or directly via service
$statusService = app(CaseStatusService::class);
$statusService->changeStatus($caseId, 'trial', 'Trial phase initiated.');
```

### Checking Task Completion

```php
$case = CaseModel::find($caseId);

if ($case->is_tasks_completed) {
    echo "All tasks completed on: " . $case->tasks_completed_at;
}
```

### Checking Case Visibility

```php
$case = CaseModel::find($caseId);

if ($case->is_public) {
    echo "Case published on: " . $case->published_at;
}
```

---

## Testing Checklist

-   [ ] Task approval updates `is_tasks_completed` flag
-   [ ] Task approval does NOT change `status_id`
-   [ ] Publishing updates `is_public` flag
-   [ ] Publishing does NOT change `status_id`
-   [ ] `changeCaseStatus()` validates legal statuses only
-   [ ] `changeCaseStatus()` requires authorization
-   [ ] All status changes are logged to timeline
-   [ ] Migration runs without errors
-   [ ] Existing data remains intact

---

## Breaking Changes

### ⚠️ Status Keys Removed

The following status keys should no longer be used for cases:

-   `completed` → Use `is_tasks_completed` flag instead
-   `published` → Use `is_public` flag instead

### ⚠️ API Changes

If you have any code that:

-   Relies on 'completed' or 'published' as case status
-   Expects automatic status changes from task approval

You will need to update it to:

-   Check `is_tasks_completed` flag instead of status
-   Check `is_public` flag instead of status
-   Use `changeCaseStatus()` for legal status transitions

---

## Benefits

1. ✅ **Clear Separation:** Task completion ≠ Legal status
2. ✅ **Explicit Control:** Status changes are intentional and authorized
3. ✅ **Better Audit Trail:** Clear distinction between task events and legal events
4. ✅ **Flexibility:** Cases can be published without changing legal status
5. ✅ **Compliance:** Legal workflow is separate from administrative workflow

---

## Files Modified

1. ✅ `database/migrations/2026_01_03_155055_add_task_completion_fields_to_cases_table.php` (new)
2. ✅ `app/Services/CaseStatusService.php` (new)
3. ✅ `app/Livewire/Cases/CaseDetail.php` (refactored)
4. ✅ `app/Models/CaseModel.php` (updated)

---

## Next Steps

1. Run the migration: `php artisan migrate`
2. Update any views that display case status to show task completion separately
3. Add UI for legal status transitions (using `changeCaseStatus()`)
4. Consider removing 'completed' and 'published' from status seeder (optional)
5. Update documentation to reflect new architecture

---

## Simplified Case Flow (Public-facing)

As part of the effort to simplify the CTIS UX for non-technical users, we introduced a lightweight abstraction:

-   **SimpleCaseService** (`app/Services/SimpleCaseService.php`) maps a small set of user-friendly steps to existing legal statuses and provides helpers to add timeline notes.
-   **Public views** should use `CaseModel::simple_timeline` (notes + date) and **must not** show `process` / `task` / `requirement` data.
-   **CMS admin** can still update statuses via `CaseStatusService`, add timeline notes, and upload documents, but should rely on simplified flow for everyday operations.

This change preserves the legacy workflow code (kept and documented) but hides it from the public UI to reduce cognitive load for non-technical users.

---

## Questions?

If you need to:

-   Add new legal statuses → Update `CaseStatusService::LEGAL_STATUSES`
-   Change authorization rules → Update permissions in `RolePermissionSeeder`
-   Customize status transition logic → Extend `CaseStatusService`
