# Tasks: Admin Raffle List Basic

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 220-320 |
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
| 1 | Admin raffle index slice with tests, route, controller, view, and copy | PR 1 | Single reviewable slice; keep tests with behavior |

## Phase 1: Access + Empty State (RED → GREEN)

- [x] 1.1 Create `tests/Feature/Raffles/AdminRaffleIndexTest.php` with local admin-host helpers and RED cases for guest redirect, JSON 401, and authenticated admin access to `GET /raffles`.
- [x] 1.2 In `tests/Feature/Raffles/AdminRaffleIndexTest.php`, add a RED scenario for the explicit empty state when no `Raffle` records exist.
- [x] 1.3 Update `routes/admin.php` and create `app/Http/Controllers/Admin/RaffleController.php` so `admin.raffles.index` resolves behind `auth:admin` and returns a basic view response.

## Phase 2: Persisted Rows (RED → GREEN)

- [x] 2.1 Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` with a RED scenario asserting one row per persisted raffle showing `id`, `status`, `starts_at`, `ends_at`, and `created_at` in newest-first order.
- [x] 2.2 In `tests/Feature/Raffles/AdminRaffleIndexTest.php`, add a RED sparse-values scenario proving nullable `starts_at` / `ends_at` render safe placeholders without invented data.
- [x] 2.3 Implement `App\Http\Controllers\Admin\RaffleController@index` with direct `Raffle::query()->latest('id')->get()` loading and pass `raffles` to the view.
- [x] 2.4 Create `resources/views/admin/raffles/index.blade.php` with inline Tailwind table markup, required columns, and a dedicated empty state; do not add action controls.

## Phase 3: Copy + Refactor

- [x] 3.1 Add raffle-index translation keys in `lang/es/admin-raffles.php` or extend `lang/es/home.php` for heading, description, empty state, placeholders, and column labels used by the Blade view.
- [x] 3.2 Refactor `tests/Feature/Raffles/AdminRaffleIndexTest.php` helpers/fixtures to remove duplication while keeping every scenario green under `bin/test`.
- [x] 3.3 Refactor `resources/views/admin/raffles/index.blade.php` for the `max-w-4xl` layout constraint so the table stays readable without custom CSS or dashboard/nav work.

## Phase 4: Verification + Scope Guard

- [x] 4.1 Verify `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` and then `bin/test` pass after the refactor stage.
- [x] 4.2 Review the final diff to confirm only `routes/admin.php`, `app/Http/Controllers/Admin/RaffleController.php`, `resources/views/admin/raffles/index.blade.php`, the chosen `lang/es/*` file, and `tests/Feature/Raffles/AdminRaffleIndexTest.php` changed.
