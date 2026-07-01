# Tasks: Admin Raffle Participation List

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 430-520 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 page foundation → PR 2 index entry point + list polish |
| Delivery strategy | ask-on-risk |
| Chain strategy | stacked-to-main |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Ship protected registrations page foundation | PR 1 | Route, controller action, empty-state/auth tests, page shell, copy |
| 2 | Add index entry point and full row rendering | PR 2 | Depends on PR 1; count loading, newest-first rows, sparse-user assertions |

## Phase 1: RED

- [x] 1.1 Create `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` with failing guest HTML/JSON auth scenarios for `GET /raffles/{raffle}/registrations`.
- [x] 1.2 In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, add failing admin scenarios for empty state, newest-first rows, allowed fields, and `user_id`-missing rows.
- [ ] 1.3 In `tests/Feature/Raffles/AdminRaffleIndexTest.php`, add failing assertions for the registrations link on every raffle row and persisted zero/non-zero counts.

## Phase 2: GREEN Foundation

- [x] 2.1 Update `routes/admin.php` to register `admin.raffles.registrations.index` inside both admin-host auth groups.
- [ ] 2.2 Update `app/Http/Controllers/Admin/RaffleController.php` so `index()` uses `withCount('registrations')` and `registrations(Raffle $raffle): View` loads `registrations()->latest('id')`.
- [x] 2.3 Create `resources/views/admin/raffles/registrations.blade.php` with raffle context, read-only table columns, linked-user signal, and explicit empty state.

## Phase 3: GREEN Integration

- [ ] 3.1 Update `resources/views/admin/raffles/index.blade.php` to add the registrations entry point in each actions cluster and show the persisted count label without management controls.
- [ ] 3.2 Update `lang/es/admin-raffles.php` with registrations-page headings, table labels, linked-user copy, empty-state text, and index action/count labels.
- [x] 3.3 Make `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` assert no ticket/payment/export/mutation controls appear on the page.

## Phase 4: REFACTOR

- [ ] 4.1 Refactor shared admin-raffle feature-test helpers/fixtures between `AdminRaffleIndexTest.php` and `AdminRaffleRegistrationsTest.php` once both files are green.
- [ ] 4.2 Refactor `resources/views/admin/raffles/registrations.blade.php` and `RaffleController.php` to keep the linked-user signal derived only from registration data.

## Phase 5: Verification

- [ ] 5.1 Run `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` and confirm all registration-list scenarios pass.
- [ ] 5.2 Run `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` and confirm the index entry-point/count scenarios pass.
