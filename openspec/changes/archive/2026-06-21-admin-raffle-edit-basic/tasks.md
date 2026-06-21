# Tasks: Admin Raffle Edit Basic

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 280-360 |
| 400-line budget risk | Medium |
| Chained PRs recommended | No |
| Suggested split | single PR |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Deliver admin raffle edit/update with tests, translations, and index wiring | PR 1 | Single PR from main; keep RED-GREEN-REFACTOR within the same review slice |

## Phase 1: RED Tests

- [x] 1.1 Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` with failing checks for row edit links, scoped update flash, and no invented flash.
- [x] 1.2 Create `tests/Feature/Raffles/AdminRaffleEditTest.php` with failing auth and form-render scenarios for `GET /raffles/{raffle}/edit` and `PATCH /raffles/{raffle}`.
- [x] 1.3 In `tests/Feature/Raffles/AdminRaffleEditTest.php`, add failing scenarios for invalid `Y-m-d\TH:i` input, old input retention, blank values becoming `null`, and updates for `draft`/`published`/`closed`.

## Phase 2: GREEN Routes and Controller

- [x] 2.1 Update `routes/admin.php` in both admin host branches to add `admin.raffles.edit` and `admin.raffles.update` under existing `auth:admin` protection.
- [x] 2.2 Extend `app/Http/Controllers/Admin/RaffleController.php` with `edit(Raffle)` returning the edit view and `update(Request, Raffle)` using inline nullable `date_format:Y-m-d\TH:i` validation.
- [x] 2.3 In `app/Http/Controllers/Admin/RaffleController.php`, persist only `starts_at` and `ends_at`, coerce empty input to `null`, redirect to `admin.raffles.index`, and set `admin.raffles.update_success`.

## Phase 3: GREEN Views and Copy

- [x] 3.1 Create `resources/views/admin/raffles/edit.blade.php` with `datetime-local` fields, old-input precedence, existing value formatting, inline errors, and cancel/save actions.
- [x] 3.2 Update `resources/views/admin/raffles/index.blade.php` to render per-row edit links plus separate scoped create/update success flashes.
- [x] 3.3 Update `lang/es/admin-raffles.php` with Spanish edit page labels, row action text, and update success flash copy.

## Phase 4: REFACTOR and Verification

- [x] 4.1 Run `bin/test tests/Feature/Raffles/AdminRaffleEditTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php`; keep fixes minimal and do not extract a shared create/edit partial in this slice.
- [x] 4.2 Re-check implementation against `openspec/changes/admin-raffle-edit-basic/specs/admin-raffle-edit/spec.md` and `specs/admin-raffle-list/spec.md` so only the approved availability-edit scope ships.
