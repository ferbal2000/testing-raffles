# Tasks: Registration Status Reactivation

## Review Workload Forecast

| Field | Value |
|---|---|
| Authored application changes | 55–80 lines |
| Authored test changes | 150–220 lines |
| OpenSpec/change + archive diff | 430–520 lines |
| Estimated total commit diff | 635–820 lines |
| Delivery strategy | ask-on-risk (resolved) |
| Delivery decision | Chained PRs |
| Chained PRs recommended | Yes |
| Chain strategy | stacked-to-main |
| 400-line budget risk | High |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal / likely PR | Focused test | Runtime harness | Rollback boundary |
|---|---|---|---|---|
| 1 | Domain + secured HTTP restore / PR 1, base `main` | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php --filter=restore` | Authenticated admin POST: flagged becomes active; repeat POST is unavailable | Model methods, controller handler, restore routes, backend tests |
| 2 | Flagged-only UI + regressions / PR 2, base updated `main` after PR 1 merges | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Browser: inspect active, flagged, cancelled rows and submit restore confirmation | Blade action, Spanish copy, rendering/regression tests |
| 3 | SDD archive synchronization / PR 3, base updated `main` after PR 2 merges | `bin/test` | N/A — documentation/archive-only unit | Archived change and merged stable-spec deltas |

## Phase 1: Backend Restore (RED → GREEN)

- [x] 1.1 **RED:** In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, add restore success plus active/cancelled rejection tests; assert unchanged invalid statuses, existing unavailable-action feedback, scoped success, and flagged-only transition.
- [x] 1.2 **RED:** Add cross-raffle 404/no-flash, guest HTML redirect/JSON 401, GET rejection, separate nonnumeric `{raffle}`/`{registration}` rejection, and route `web` middleware placement coverage. Do not claim standard feature POST proves negative CSRF rejection.
- [x] 1.3 **GREEN:** Add flagged-only `canBeRestored()` and `restoreToActive()` to `app/Models/RaffleRegistration.php`, throwing `InvalidRaffleRegistrationTransition` otherwise.
- [x] 1.4 **GREEN:** Add `restoreRegistration()` to `app/Http/Controllers/Admin/RaffleController.php`, delegating to the existing parent-scoped transactional `transitionRegistration()` lock helper.
- [x] 1.5 **GREEN:** Add the explicit named POST restore route to both `routes/admin.php` host branches, constraining both route parameters numerically.

## Phase 2: Admin UI (RED → GREEN)

- [x] 2.1 **RED:** Extend row-rendering tests for active flag/cancel only, flagged-only restore form with CSRF field/token and restore URL, cancelled no mutation, Spanish restore/clear-review label, confirmation, and success flash.
- [x] 2.2 **RED:** Preserve empty/sparse rows, linked-user signal, newest-first order, status counts, and existing public behavior through focused regression assertions/tests.
- [x] 2.3 **GREEN:** Add bounded Spanish restore/clear-review copy in `lang/es/admin-raffles.php`; update `resources/views/admin/raffles/registrations.blade.php` with flagged-only CSRF form and success feedback.

## Phase 3: Refactor and Apply Verification

**Implementation checklist: 10/10 complete — verify-ready.**

- [x] 3.1 **REFACTOR:** Remove test duplication without widening the transition API or adding realtime runtime behavior.
- [x] 3.2 Run the Unit 1 and Unit 2 focused commands, then `bin/test`; record exact results and both runtime-harness outcomes.

### Archive boundary (after verify)

After verification, `sdd-archive` merges only the three approved delta specs and keeps the realtime map future-only. Archive is not complete.
