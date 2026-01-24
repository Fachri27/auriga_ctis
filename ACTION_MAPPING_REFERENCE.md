# Action Mapping Quick Reference

## 🎬 User Actions → Status Transitions

This document provides a quick reference for the action-based workflow system.

---

## 📋 Action Definitions

| Action Key | Action Label (UI) | Target Status | Allowed From Status |
|------------|-------------------|---------------|---------------------|
| `verify_report` | Verify Report | `open` | `open` (report flow) |
| `convert_to_case` | Convert to Case | `investigation` | `open` |
| `complete_investigation` | Complete Investigation | `prosecution` | `investigation` |
| `start_prosecution` | Start Prosecution | `prosecution` | `investigation` (alias) |
| `start_trial` | Start Trial | `trial` | `prosecution` |
| `execute_verdict` | Execute Verdict | `executed` | `trial` |
| `close_case` | Close Case | `closed` | `investigation`, `prosecution`, `trial`, `executed` |
| `reject_case` | Reject Case | `rejected` | `open`, `investigation` |

---

## 🔄 Status Flow Diagram

```
Report Flow:
┌─────────┐    verify_report    ┌─────────┐    convert_to_case    ┌──────────────┐
│  open   │ ─────────────────→ │ verified│ ───────────────────→ │ investigation │
│ (report)│                     │(report) │                      │    (case)     │
└─────────┘                     └─────────┘                      └──────────────┘
     │                                                                  │
     │ reject_report                                                   │
     │                                                                  │
     ↓                                                                  ↓
┌─────────┐                                                      ┌──────────────┐
│rejected │                                                      │ investigation │
│(report) │                                                      │    (case)     │
└─────────┘                                                      └──────────────┘
                                                                        │
                                                                        │ complete_investigation
                                                                        │ OR
                                                                        │ reject_case
                                                                        │ OR
                                                                        │ close_case
                                                                        │
                                                                        ↓
                                                              ┌──────────────┐
                                                              │ prosecution  │
                                                              └──────────────┘
                                                                        │
                                                                        │ start_trial
                                                                        │ OR
                                                                        │ close_case
                                                                        │
                                                                        ↓
                                                              ┌──────────────┐
                                                              │    trial     │
                                                              └──────────────┘
                                                                        │
                                                                        │ execute_verdict
                                                                        │ OR
                                                                        │ close_case
                                                                        │
                                                                        ↓
                                                              ┌──────────────┐
                                                              │   executed   │
                                                              └──────────────┘
                                                                        │
                                                                        │ close_case
                                                                        │
                                                                        ↓
                                                              ┌──────────────┐
                                                              │    closed    │
                                                              └──────────────┘
                                                                    (FINAL)
```

---

## 📊 Status Groups (Virtual - UI Only)

| Group | Statuses | Purpose |
|-------|----------|---------|
| `review` | `open` | Initial review stage |
| `working` | `investigation` | Active work in progress |
| `decision` | `prosecution`, `trial` | Decision-making phase |
| `final` | `executed`, `closed` | Final stages |
| `rejected` | `rejected` | Rejected/terminated |

---

## 🎯 UI Action Button Rules

### Investigation Stage

**Shown Actions:**
- ✅ `complete_investigation` → Move to Prosecution
- ✅ `reject_case` → Reject Case
- ✅ `close_case` → Close Case

**Hidden Actions:**
- ❌ `start_trial` (not allowed from investigation)
- ❌ `execute_verdict` (not allowed from investigation)

### Prosecution Stage

**Shown Actions:**
- ✅ `start_trial` → Move to Trial
- ✅ `close_case` → Close Case

**Hidden Actions:**
- ❌ `complete_investigation` (already past this)
- ❌ `reject_case` (not allowed from prosecution)

### Trial Stage

**Shown Actions:**
- ✅ `execute_verdict` → Move to Executed
- ✅ `close_case` → Close Case

**Hidden Actions:**
- ❌ `start_trial` (already in trial)
- ❌ `complete_investigation` (already past this)

---

## 🔐 Transition Validation Rules

### Valid Transitions

```php
'open' → ['investigation', 'rejected']
'investigation' → ['prosecution', 'closed', 'rejected']
'prosecution' → ['trial', 'closed']
'trial' → ['executed', 'closed']
'executed' → ['closed']
'closed' → [] // Final - no transitions
'rejected' → [] // Final - no transitions
```

### Invalid Transitions (Blocked)

- ❌ `investigation` → `trial` (must go through prosecution)
- ❌ `prosecution` → `executed` (must go through trial)
- ❌ `open` → `prosecution` (must convert to case first)
- ❌ `closed` → `investigation` (final status, no transitions)
- ❌ `rejected` → `investigation` (final status, no transitions)

---

## 📝 Code Examples

### Check Allowed Actions

```php
$case = CaseModel::find($caseId);
$actionService = app(CaseActionService::class);

$allowedActions = $actionService->getAllowedActions($case);
// Returns: ['complete_investigation', 'reject_case', 'close_case']
```

### Execute Action

```php
$actionService = app(CaseActionService::class);

// Execute action (validates, transitions status, logs timeline)
$actionService->executeAction(
    $caseId,
    'complete_investigation',
    'Investigation completed successfully. Evidence collected.'
);
```

### Check If Action Allowed

```php
$actionService = app(CaseActionService::class);

if ($actionService->isActionAllowed($case, 'start_trial')) {
    // Show "Start Trial" button
}
```

### Get Action Label for UI

```php
$actionService = app(CaseActionService::class);

$label = $actionService->getActionLabel('complete_investigation');
// Returns: "Complete Investigation"
```

---

## ✅ Timeline Logging

Every action automatically creates a timeline entry:

```php
// Timeline Entry Format:
[
    'case_id' => 123,
    'actor_id' => 1, // User ID
    'notes' => 'Action: Complete Investigation - Investigation completed successfully.',
    'created_at' => '2025-01-XX 10:30:00',
]
```

**Timeline Entry Includes:**
- ✅ Action name
- ✅ Actor (who performed action)
- ✅ Timestamp (when action was performed)
- ✅ Optional notes (user-provided context)

---

## 🚨 Important Notes

1. **Tasks Never Auto-Change Status**: Tasks are checklists only. Completing all tasks does NOT automatically change case status.

2. **Status Changes Only Via Actions**: Users should use action buttons, not direct status dropdowns (when available).

3. **Timeline Always Logged**: Every status change must be logged to timeline with actor and timestamp.

4. **Backward Compatibility**: Direct status changes via `CaseStatusService::changeStatus()` still work, but should validate transitions.

5. **Admin Use**: `CaseModal` still allows direct status editing for administrative use, but this bypasses action system.

---

## 📚 Related Documentation

- `SIMPLIFIED_INTERNAL_FLOW.md` - Full implementation details
- `REFACTORING_CASE_STATUS_SEPARATION.md` - Previous refactoring notes
- `app/Services/CaseActionService.php` - Action service implementation
- `app/Services/CaseStatusService.php` - Status service implementation

---

**Last Updated**: 2025-01-XX  
**Version**: 1.0.0  
**Status**: ✅ Production Ready

