# Apply Progress: Admin Raffle Edit Basic

**Change**: admin-raffle-edit-basic  
**Mode**: Strict TDD  
**Delivery**: single PR within forecasted review budget

## Completed Tasks

- [x] 1.1 Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` with failing checks for row edit links, scoped update flash, and no invented flash.
- [x] 1.2 Create `tests/Feature/Raffles/AdminRaffleEditTest.php` with failing auth and form-render scenarios for `GET /raffles/{raffle}/edit` and `PATCH /raffles/{raffle}`.
- [x] 1.3 In `tests/Feature/Raffles/AdminRaffleEditTest.php`, add failing scenarios for invalid `Y-m-d\TH:i` input, old input retention, blank values becoming `null`, and updates for `draft`/`published`/`closed`.
- [x] 2.1 Update `routes/admin.php` in both admin host branches to add `admin.raffles.edit` and `admin.raffles.update` under existing `auth:admin` protection.
- [x] 2.2 Extend `app/Http/Controllers/Admin/RaffleController.php` with `edit(Raffle)` returning the edit view and `update(Request, Raffle)` using inline nullable `date_format:Y-m-d\TH:i` validation.
- [x] 2.3 In `app/Http/Controllers/Admin/RaffleController.php`, persist only `starts_at` and `ends_at`, coerce empty input to `null`, redirect to `admin.raffles.index`, and set `admin.raffles.update_success`.
- [x] 3.1 Create `resources/views/admin/raffles/edit.blade.php` with `datetime-local` fields, old-input precedence, existing value formatting, inline errors, and cancel/save actions.
- [x] 3.2 Update `resources/views/admin/raffles/index.blade.php` to render per-row edit links plus separate scoped create/update success flashes.
- [x] 3.3 Update `lang/es/admin-raffles.php` with Spanish edit page labels, row action text, and update success flash copy.
- [x] 4.1 Run `bin/test tests/Feature/Raffles/AdminRaffleEditTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php`; keep fixes minimal and do not extract a shared create/edit partial in this slice.
- [x] 4.2 Re-check implementation against `openspec/changes/admin-raffle-edit-basic/specs/admin-raffle-edit/spec.md` and `specs/admin-raffle-list/spec.md` so only the approved availability-edit scope ships.

## Files Changed

| File | Action | Notes |
|------|--------|-------|
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modified | Added edit-link and scoped update flash coverage. |
| `tests/Feature/Raffles/AdminRaffleEditTest.php` | Created | Added protected edit/update flow coverage for auth, validation, nullable persistence, and allowed statuses. |
| `routes/admin.php` | Modified | Registered protected admin edit/update routes in both host-aware branches. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Added edit/update actions with inline nullable datetime validation and scoped success flash. |
| `resources/views/admin/raffles/edit.blade.php` | Created | Added edit form with datetime-local inputs, old input precedence, inline errors, and actions. |
| `resources/views/admin/raffles/index.blade.php` | Modified | Added row edit action plus separate create/update success flashes. |
| `lang/es/admin-raffles.php` | Modified | Added Spanish edit labels, action copy, and update flash text. |
| `openspec/changes/admin-raffle-edit-basic/tasks.md` | Modified | Marked all apply tasks complete. |

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Added failing edit-link/update-flash assertions first | ✅ `bin/test tests/Feature/Raffles/AdminRaffleEditTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` passed | ✅ Edit link, update flash, and no-invented-flash scenarios | ✅ Kept existing helper/test structure with minimal assertion additions |
| 1.2 | `tests/Feature/Raffles/AdminRaffleEditTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Added failing auth + form-render scenarios before implementation | ✅ `bin/test tests/Feature/Raffles/AdminRaffleEditTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` passed | ✅ Covered HTML + JSON auth paths and populated form rendering | ✅ Reused local host/url helpers to keep new file focused |
| 1.3 | `tests/Feature/Raffles/AdminRaffleEditTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Added failing invalid-input/null/status update scenarios first | ✅ `bin/test tests/Feature/Raffles/AdminRaffleEditTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` passed | ✅ Invalid input, blank-to-null, and `draft`/`published`/`closed` updates | ✅ Used a dataset for status coverage without broadening scope |
| 2.1 | `tests/Feature/Raffles/AdminRaffleEditTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Route expectations already failing via RED tests | ✅ Protected GET/PATCH routes resolved in passing suite | ✅ Guest HTML + JSON auth cases verify both host branches contract | ➖ None needed |
| 2.2 | `tests/Feature/Raffles/AdminRaffleEditTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Edit form/update validation expectations written first | ✅ Passing suite proves edit view + inline validation flow | ✅ Form render + validation redirect exercise different paths | ➖ None needed |
| 2.3 | `tests/Feature/Raffles/AdminRaffleEditTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Persistence/success-flash expectations written first | ✅ Passing suite proves nullable persistence + redirect flash | ✅ Blank-null and three-status updates force real persistence logic | ➖ None needed |
| 3.1 | `tests/Feature/Raffles/AdminRaffleEditTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Edit form assertions written before view existed | ✅ Passing suite proves values, fields, and action wiring render | ✅ Existing value rendering + old-input retention cover separate states | ✅ Duplicated only the two-field form per design |
| 3.2 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Index assertions failed before list changes | ✅ Passing suite proves row edit link and scoped update flash | ✅ Create flash, update flash, and no-flash cases cover alternate outputs | ➖ None needed |
| 3.3 | `tests/Feature/Raffles/AdminRaffleEditTest.php`, `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | ✅ 16/16 baseline (`AdminRaffleIndexTest` + `AdminRaffleCreateTest`) | ✅ Spanish copy expectations were part of RED assertions | ✅ Passing suite proves translated labels/flash text resolve | ✅ Edit page and index flash assertions cover different translation branches | ➖ None needed |
| 4.1 | `tests/Feature/Raffles/AdminRaffleEditTest.php`, `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Integration | N/A | ✅ Verification command defined by task | ✅ `bin/test tests/Feature/Raffles/AdminRaffleEditTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` passed (19 tests) | ➖ Command already covers happy + edge paths | ➖ None needed |
| 4.2 | `openspec/changes/admin-raffle-edit-basic/specs/admin-raffle-edit/spec.md`, `openspec/changes/admin-raffle-edit-basic/specs/admin-raffle-list/spec.md` | Spec Review | N/A | ✅ Scope checks reviewed before finalizing | ✅ Final code matches scoped edit/update behavior | ✅ Compared edit spec and list delta against implementation surface | ➖ None needed |

## Test Summary

- **Total tests written**: 13
- **Total tests passing**: 19
- **Layers used**: Unit (0), Integration (19), E2E (0)
- **Approval tests** (refactoring): None — no refactoring tasks
- **Pure functions created**: 0

## Deviations from Design

None — implementation matches design.

## Issues Found

None.

## Remaining Tasks

None — apply scope is complete.

## Workload / PR Boundary

- Mode: single PR
- Current work unit: Unit 1 — admin raffle edit/update with tests, translations, and index wiring
- Boundary: Adds protected edit/update routes, controller actions, view, translations, index link/flash handling, and slice-specific feature coverage only
- Estimated review budget impact: Within forecasted single-PR range

## Status

11/11 tasks complete. Ready for verify.
