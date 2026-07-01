# Apply Progress: admin-raffle-participation-list

## Mode
Strict TDD

## Delivery
- Strategy: chained PRs
- Chain strategy: stacked-to-main
- Work unit: Unit 2 / PR 2 — admin raffle index entry point + counts + list polish
- Scope guard: finished the planned PR 2 slice on top of the merged PR 1 foundation without adding filters, export, mutation, or unrelated raffle-management semantics

## Completed Tasks
- [x] 1.1 Create `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` with failing guest HTML/JSON auth scenarios for `GET /raffles/{raffle}/registrations`.
- [x] 1.2 In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, add failing admin scenarios for empty state, newest-first rows, allowed fields, and `user_id`-missing rows.
- [x] 1.3 In `tests/Feature/Raffles/AdminRaffleIndexTest.php`, add failing assertions for the registrations link on every raffle row and persisted zero/non-zero counts.
- [x] 2.1 Update `routes/admin.php` to register `admin.raffles.registrations.index` inside both admin-host auth groups.
- [x] 2.2 Update `app/Http/Controllers/Admin/RaffleController.php` so `index()` uses `withCount('registrations')` and `registrations(Raffle $raffle): View` loads `registrations()->latest('id')`.
- [x] 2.3 Create `resources/views/admin/raffles/registrations.blade.php` with raffle context, read-only table columns, linked-user signal, and explicit empty state.
- [x] 3.1 Update `resources/views/admin/raffles/index.blade.php` to add the registrations entry point in each actions cluster and show the persisted count label without management controls.
- [x] 3.2 Update `lang/es/admin-raffles.php` with registrations-page headings, table labels, linked-user copy, empty-state text, and index action/count labels.
- [x] 3.3 Make `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` assert no ticket/payment/export/mutation controls appear on the page.
- [x] 4.1 Refactor shared admin-raffle feature-test helpers/fixtures between `AdminRaffleIndexTest.php` and `AdminRaffleRegistrationsTest.php` once both files are green.
- [x] 4.2 Refactor `resources/views/admin/raffles/registrations.blade.php` and `RaffleController.php` to keep the linked-user signal derived only from registration data.
- [x] 5.1 Run `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` and confirm all registration-list scenarios pass.
- [x] 5.2 Run `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` and confirm the index entry-point/count scenarios pass.

## Files Changed
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — feature coverage for guest auth, empty state, non-empty newest-first rendering, back-to-index CTA, linked-account signals, and absence of management semantics
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — RED/GREEN coverage for per-row registrations links and persisted zero/non-zero registration counts
- `tests/Feature/Raffles/RaffleAdminTestSupport.php` — shared admin raffle host/url and registration fixture helpers for the two feature files
- `tests/Pest.php` — loads the shared admin raffle feature-test support file
- `routes/admin.php` — registered the protected admin registrations route in both admin-host auth groups
- `app/Http/Controllers/Admin/RaffleController.php` — added `withCount('registrations')` to the index query and restricted the registrations load to registration-owned columns ordered by `latest('id')`
- `resources/views/admin/raffles/index.blade.php` — added per-row registrations entry points and persisted registration count labels in the actions cluster
- `resources/views/admin/raffles/registrations.blade.php` — added the back-to-index CTA and kept the linked-account label derived only from `user_id` presence
- `lang/es/admin-raffles.php` — added index action/count labels and the registrations-page back-link copy
- `openspec/changes/admin-raffle-participation-list/tasks.md` — checked off the completed PR 2 tasks
- `openspec/changes/admin-raffle-participation-list/apply-progress.md` — merged PR 1 + PR 2 cumulative progress and TDD evidence

## TDD Cycle Evidence
| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → 20 passed | ✅ Wrote failing guest HTML + JSON auth tests first (404 before route existed) | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 3 passed | ✅ 2 auth cases (HTML redirect + JSON 401) | ➖ None needed |
| 1.2 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → 10 passed | ✅ Added a failing populated-registration scenario first; current page rendered no row content | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 4 passed | ✅ Empty-state + populated newest-first + sparse `user_id` row coverage in the same file | ➖ None needed |
| 1.3 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 17 passed | ✅ Added failing link + persisted count assertions before touching the index query/view | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` → 15 passed | ✅ Separate every-row-link and zero/non-zero count scenarios force both branches to render real output | ➖ None needed |
| 2.1 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 20-test baseline above | ✅ Failing route-driven auth/access tests required route registration first | ✅ Protected route passes both admin-host auth behaviors | ✅ Exercised route through HTML + JSON entry paths | ➖ None needed |
| 2.2 | `tests/Feature/Raffles/AdminRaffleIndexTest.php`, `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 17-test baseline above | ✅ Empty-state + index count tests required the controller to finish both `latest('id')` loading and `withCount('registrations')` | ✅ Index and registrations controller paths pass with targeted execution | ✅ Count rendering and newest-first registration rendering now cover both controller branches | ✅ Limited registrations loading to registration-owned columns so linked-account output stays derived from `user_id` only |
| 2.3 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 10-test safety-net above | ✅ Populated-row assertions failed before the view had a non-empty path | ✅ Minimal read-only table now renders rows, timestamps, and linked-account states | ✅ Empty + populated paths now prove both branches render real output | ➖ None needed |
| 3.1 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | ✅ Same 17-test baseline above | ✅ Added failing assertions for the per-row registrations entry point and visible persisted counts | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` → 15 passed | ✅ Zero-count and non-zero-count rows both render the same read-only entry point | ➖ None needed |
| 3.2 | `tests/Feature/Raffles/AdminRaffleIndexTest.php`, `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 4 passed | ✅ Added failing back-link copy and index count/action copy assertions before extending translations | ✅ Both targeted feature files resolve the new Spanish copy successfully | ✅ Registrations page copy, index action label, zero/non-zero count labels, and back CTA all render in exercised scenarios | ➖ None needed |
| 3.3 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 10-test safety-net above | ✅ Added failing assertions that populated pages must not show ticket/payment/export/mutation semantics | ✅ Page stays read-only with those forbidden semantics absent | ✅ Verified absence alongside real non-empty row rendering | ➖ None needed |
| 4.1 | `tests/Feature/Raffles/AdminRaffleIndexTest.php`, `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 19 passed | ✅ Approval baseline captured with the fully green index + registrations feature files before extracting shared helpers | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → 26 passed | ➖ Refactor-only task | ✅ Extracted shared admin raffle host/url + registration fixture helpers into `tests/Feature/Raffles/RaffleAdminTestSupport.php` |
| 4.2 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | ✅ Same 19-test approval baseline above | ✅ Approval baseline captured before tightening the controller/view linkage derivation | ✅ Same 26-test regression pass confirmed behavior unchanged | ✅ Empty + populated registrations still resolve linked-account labels using only registration data | ✅ Controller now selects registration-owned columns only and the view resolves one derived label per row |
| 5.1 | `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Integration | N/A (verification task) | ✅ Targeted verification command selected before execution | ✅ `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → 4 passed | ✅ Empty + populated coverage both executed | ➖ None needed |
| 5.2 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | N/A (verification task) | ✅ Targeted verification command selected before execution | ✅ `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` → 15 passed | ✅ Link coverage + zero/non-zero persisted count coverage both executed | ➖ None needed |

## Test Summary
- Total tests written: 6 feature tests added/extended for this change
- Total tests passing: 26 targeted tests / 171 assertions in the final focused regression run
- Layers used: Integration (index, registrations, lifecycle)
- Approval tests: 19 targeted feature tests used as the refactor baseline for tasks 4.1 and 4.2
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
9. `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → PASS, 17 tests / 71 assertions
10. `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` → FAIL, 2 tests failing because the index had no registrations link/count output yet
11. `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` → PASS, 15 tests / 60 assertions
12. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → FAIL, 2 tests failing because the registrations page lacked the back-to-index CTA
13. `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` → PASS, 4 tests / 24 assertions
14. `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` → PASS, 26 tests / 171 assertions

## Deviations from Design
- None — implementation matches the planned PR 2 slice and keeps the registrations surface read-only.

## Issues Found
- The tasks intentionally grouped some PR 1 and PR 2 behavior under the same checklist items, so the merged apply-progress preserves partial-vs-complete history in the evidence rather than pretending PR 1 already finished those tasks.

## Remaining Tasks
- [ ] None.

## Status
13 / 13 tasks completed. Unit 2 / PR 2 is ready for verify or reviewer inspection.
