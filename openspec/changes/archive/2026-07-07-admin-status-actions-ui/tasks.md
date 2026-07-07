# Tasks: Admin Status Actions UI

Issue: [#39](https://github.com/ferbal2000/testing-raffles/issues/39)

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 260-380 |
| 400-line budget risk | Medium |
| Chained PRs recommended | No |
| Suggested split | Single PR; keep commits by work unit |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Domain transitions plus RED/GREEN coverage | PR 1 | Model helpers, exception, first failing feature tests. |
| 2 | Admin POST routes/controllers plus RED/GREEN coverage | PR 1 | Both admin host branches; transactional locked mutations. |
| 3 | Blade status UI, totals, Spanish copy plus RED/GREEN coverage | PR 1 | Keep public/realtime unchanged. |

## Phase 1: RED Coverage

- [x] 1.1 In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, add failing tests for status display, active-only actions, terminal no-action rows, newest-first preservation, and separated active/flagged/cancelled/total summaries.
- [x] 1.2 In the same test file, add failing POST tests for successful flag/cancel, terminal rejection with unchanged status, nested raffle guard, and guest/admin auth behavior.
- [x] 1.3 Add a public-regression test only if existing public coverage does not prove registration eligibility is unchanged.

## Phase 2: Domain GREEN

- [x] 2.1 Create `app/Exceptions/InvalidRaffleRegistrationTransition.php` for unavailable registration status mutations.
- [x] 2.2 Update `app/Models/RaffleRegistration.php` with `canBeFlagged()`, `canBeCancelled()`, `markForReview()`, and `cancel()` using only `active -> flagged|cancelled` transitions.

## Phase 3: Admin Routing and Controller GREEN

- [x] 3.1 Update `routes/admin.php` in both configured-host branches with explicit POST `flag` and `cancel` registration routes; do not add payload-driven status routes.
- [x] 3.2 Update `app/Http/Controllers/Admin/RaffleController.php` registrations query to select `status` and load active/flagged/cancelled aliases plus total persisted count.
- [x] 3.3 Add controller flag/cancel handlers that wrap scoped `lockForUpdate()` lookup, transition, and save in one `DB::transaction()`.
- [x] 3.4 Translate invalid transition exceptions to `registration_status` errors and successful actions to scoped translated flashes.

## Phase 4: UI and Copy GREEN

- [x] 4.1 Update `lang/es/admin-raffles.php` with Spanish status labels, action labels, confirmations, success flashes, unavailable-action error, and separated total labels.
- [x] 4.2 Update `resources/views/admin/raffles/registrations.blade.php` with status badges, active-row POST forms, terminal no-action rendering, feedback, and separated totals.

## Phase 5: REFACTOR and Verification Prep

- [x] 5.1 Refactor duplicated controller lookup/transition code without adding generic status setters or widening scope.
- [x] 5.2 Run `bin/test` during apply and mark these tasks complete only after the RED tests pass GREEN.
