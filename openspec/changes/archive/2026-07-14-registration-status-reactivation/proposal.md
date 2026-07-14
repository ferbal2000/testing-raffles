# Proposal: Registration Status Reactivation

## Intent

Allow admins to correct a registration that was marked for review by restoring `flagged` registrations to `active`, while preserving `cancelled` as terminal traceability state.

## Scope

### In Scope
- Add an admin-only `flagged -> active` restoration path.
- Show a row action only for eligible flagged registrations.
- Clear review semantics with success/error feedback.
- Update specs and the realtime candidate map for the delivered observable status change.

### Out of Scope
- Restoring `cancelled` registrations.
- Generic status setter, bulk actions, filters/search, notifications, realtime runtime, reasons, comments, or audit history.
- Public eligibility, ticketing, draw, payment, approval/rejection, or workflow semantics.

## Capabilities

### New Capabilities
- None.

### Modified Capabilities
- `admin-raffle-participation-list`: allow a bounded restore-to-active action on flagged rows only.
- `raffle-registration-status`: change `flagged` from terminal to review-cleared restorable; keep `cancelled` terminal.
- `realtime-update-candidate-map`: document the delivered restore-to-active status change as a future-only update candidate.

## Approach

Follow the existing status-actions pattern: a bounded model transition, explicit admin POST route, controller transaction with row lock, inline Blade form for eligible rows, and Spanish UI feedback that says “restore to active” / “clear review” rather than generic workflow language.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Models/RaffleRegistration.php` | Modified | Add bounded restore guard/transition. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Add locked admin restore handler. |
| `routes/admin.php` | Modified | Add explicit restore route. |
| `resources/views/admin/raffles/registrations.blade.php` | Modified | Render restore action only for flagged rows. |
| `lang/es/admin-raffles.php` | Modified | Add labels, confirmation, success, and unavailable-action copy. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Modified | Cover success, terminal rejection, and list rendering. |
| `openspec/specs/*` | Modified | Delta specs for listed capabilities. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| UI reads like generic status workflow | Medium | Use restore/clear-review wording only. |
| Cancelled restoration sneaks into scope | Low | Server guard and specs keep `cancelled` terminal. |
| Stale row action after status change | Medium | Reuse unavailable-action feedback. |

## Rollback Plan

Revert the route, controller handler, model transition, Blade action, language strings, tests, and spec deltas. Existing registrations remain valid because no schema or data migration is planned.

## Dependencies

- Approved issue #41.
- Existing admin status-action transaction/lock pattern.

## Success Criteria

- [ ] Admins can restore flagged registrations to active.
- [ ] Cancelled registrations cannot be restored.
- [ ] The list exposes restore only for flagged rows.
- [ ] Success/error feedback is clear and bounded to review-clearing semantics.
- [ ] Specs identify the exact modified capabilities.
