# Apply Progress: admin-raffle-participation-list

## Mode
Strict TDD

## Delivery
- Strategy: chained PRs
- Chain strategy: stacked-to-main
- Work unit: Unit 1 / PR 1 — protected registrations page foundation
- Scope guard: kept index entry point and counts out of scope for this batch; pulled in the minimal non-empty rendering path because the live route was otherwise incomplete

## Completed Tasks
- [x] 1.1 Create `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` with failing guest HTML/JSON auth scenarios for `GET /raffles/{raffle}/registrations`.
- [x] 1.2 In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, add failing admin scenarios for empty state, newest-first rows, allowed fields, and `user_id`-missing rows.
- [x] 2.1 Update `routes/admin.php` to register `admin.raffles.registrations.index` inside both admin-host auth groups.
- [x] 2.3 Create `resources/views/admin/raffles/registrations.blade.php` with raffle context, read-only table columns, linked-user signal, and explicit empty state.
- [x] 3.3 Make `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` assert no ticket/payment/export/mutation controls appear on the page.

## Partial Progress (not checked off)
- [~] 2.2 Added `registrations(Raffle $raffle): View` with `registrations()->latest('id')`, but left `index()->withCount('registrations')` for PR 2.
- [~] 3.2 Added registrations-page heading/description/empty-state translations plus table labels and linked-account copy; index action/count labels remain for PR 2.

## Files Changed
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — feature coverage for guest auth, empty state, non-empty newest-first rendering, linked-account signals, and absence of management semantics
- `routes/admin.php` — registered the protected admin registrations route in both admin-host auth groups
- `app/Http/Controllers/Admin/RaffleController.php` — added `registrations()` to load newest-first registrations and return the new view
- `resources/views/admin/raffles/registrations.blade.php` — added the registrations page shell plus the minimal non-empty read-only table path
- `lang/es/admin-raffles.php` — added registrations page copy, table labels, and linked-account signals
- `openspec/changes/admin-raffle-participation-list/tasks.md` — aligned chain metadata to `stacked-to-main` and checked off the newly completed PR1 tasks
- `openspec/changes/admin-raffle-participation-list/apply-progress.md` — merged the PR1 fix batch and exact TDD evidence

## TDD Cycle Evidence
| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → 20 passed | ✅ Wrote failing guest HTML + JSON auth tests first (404 before route existed) | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 3 passed | ✅ 2 auth cases (HTML redirect + JSON 401) | ➖ None needed |
| 1.2 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → 10 passed | ✅ Added a failing populated-registration scenario first; current page rendered no row content | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 4 passed | ✅ Empty-state + populated newest-first + sparse `user_id` row coverage in the same file | ➖ None needed |
| 2.1 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 20-test baseline above | ✅ Failing route-driven auth/access tests required route registration first | ✅ Protected route passes both admin-host auth behaviors | ✅ Exercised route through HTML + JSON entry paths | ➖ None needed |
| 2.2 (partial) | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 20-test baseline above | ✅ Empty-state test required controller action | ✅ Added `registrations()->latest('id')` and returned the page successfully | ➖ `withCount('registrations')` intentionally deferred to PR 2 | ➖ None needed |
| 2.3 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 10-test safety-net above | ✅ Populated-row assertions failed before the view had a non-empty path | ✅ Minimal read-only table now renders rows, timestamps, and linked-account states | ✅ Empty + populated paths now prove both branches render real output | ➖ None needed |
| 3.2 (partial) | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 10-test safety-net above | ✅ Populated-row assertions required table/header and linked-account translation keys | ✅ Registrations page copy resolves from `lang/es/admin-raffles.php` for empty and non-empty states | ➖ Index action/count labels intentionally deferred to PR 2 | ➖ None needed |
| 3.3 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 10-test safety-net above | ✅ Added failing assertions that populated pages must not show ticket/payment/export/mutation semantics | ✅ Page stays read-only with those forbidden semantics absent | ✅ Verified absence alongside real non-empty row rendering | ➖ None needed |

## Test Summary
- Total tests written: 4
- Total tests passing: 24 targeted tests passing across the registration file plus focused regression runs
- Layers used: Integration (4 registration tests)
- Approval tests: None — no refactoring task was completed
- Pure functions created: 0

## Commands Run
1. `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → PASS, 20 tests / 138 assertions
2. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → FAIL, 3 tests failing with 404 before route/controller/view existed
3. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → PASS, 3 tests / 9 assertions
4. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → PASS, 23 tests / 147 assertions
5. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → PASS, 10 tests / 96 assertions
6. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → FAIL, 1 test failing because populated registrations were not rendered on the page
7. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → PASS, 4 tests / 20 assertions
8. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → PASS, 24 tests / 158 assertions

## Deviations from Design
- Deliberately deferred the design's index integration (`withCount('registrations')` and index entry point) to PR 2 because the orchestrator explicitly limited this batch to the protected registrations page foundation.
- Pulled the minimal populated-row rendering path into PR 1 because the live registrations route was incomplete without any non-empty output.

## Issues Found
- The planned task granularity mixes PR 1 foundation work with PR 2 scope, so the controller/view/translation foundation landed without checking off the broader combined tasks.

## Remaining Tasks
- [ ] 1.3 Add index-link/count RED coverage.
- [ ] 2.2 Finish `index()->withCount('registrations')` work.
- [ ] 3.1 Add the index entry point and persisted count label.
- [ ] 3.2 Finish remaining translation keys for table and index labels.
- [ ] 4.1 Refactor shared feature-test helpers.
- [ ] 4.2 Refactor linked-user signal handling once row rendering exists.
- [ ] 5.1 Run the dedicated registrations verification scenario set once PR 2 behavior lands.
- [ ] 5.2 Run the index entry-point verification scenario set once PR 2 behavior lands.

## Status
5 / 11 tasks completed. Unit 1 / PR 1 foundation is now commit-ready; next batch should implement PR 2 index integration and persisted count work.
