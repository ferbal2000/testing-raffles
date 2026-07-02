# Tasks: Admin Raffle Publication Management

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 260-390 |
| 500-line budget risk | Low |
| 400-line guard risk | Medium |
| Chained PRs recommended | No |
| Suggested split | Single PR; commit by model, route/controller, then index UI work units. |
| Delivery strategy | ask-always |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Medium
500-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Add draft-only model eligibility. | PR 1 | Keep `Raffle::publish()` lifecycle authority intact. |
| 2 | Add protected publish endpoint. | PR 1 | Route must exist in both `routes/admin.php` admin host branches. |
| 3 | Add index UI and scoped copy. | PR 1 | Keep action draft-only and feedback publish-scoped. |

## Phase 1: Model Lifecycle TDD

- [x] 1.1 RED: Extend `tests/Feature/Raffles/RaffleLifecycleTest.php` proving `Raffle::canPublish()` is true only for `draft` and false for non-draft states.
- [x] 1.2 GREEN: Update `app/Models/Raffle.php` with `canPublish(): bool` and make `publish()` use it after `ensureIsPersisted()`.
- [x] 1.3 REFACTOR: Run `bin/test` sequentially and keep `canPublish()` strictly `status === draft`, with no date or participation coupling.

## Phase 2: Route and Controller TDD

- [x] 2.1 RED: Create `tests/Feature/Raffles/AdminRafflePublicationTest.php` for admin success, guest rejection, stale non-draft rejection, public visibility, and unchanged participation timestamps.
- [x] 2.2 GREEN: Add `admin.raffles.publish` POST routes to both admin branches in `routes/admin.php`.
- [x] 2.3 GREEN: Add `publish(Raffle $raffle): RedirectResponse` to `app/Http/Controllers/Admin/RaffleController.php`, delegating to `$raffle->publish()` and catching `InvalidRaffleTransition` with `withErrors(['publish' => ...])`.
- [x] 2.4 GREEN: Add Spanish `publish_action`, `publish_confirm`, and `publish_success` copy to `lang/es/admin-raffles.php`.
- [x] 2.5 REFACTOR: Run `bin/test` sequentially and remove any duplicated controller status checks.

## Phase 3: Admin Index TDD

- [x] 3.1 RED: Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` for draft-only publish control, confirmation copy, scoped publish success, and scoped publish rejection feedback.
- [x] 3.2 GREEN: Update `resources/views/admin/raffles/index.blade.php` to render publish errors and a CSRF POST form only when `$raffle->canPublish()`.
- [x] 3.3 REFACTOR: Keep row actions compact and ensure create/update/participation flashes are not emitted for publish rejection.

## Phase 4: Verification

- [x] 4.1 Run full `bin/test` sequentially and confirm all publication, index, and existing participation scenarios pass.
- [x] 4.2 Confirm no edit-screen publishing, published-to-draft reversal, moderation, tickets, winners, draw behavior, or automatic date publication was added.
