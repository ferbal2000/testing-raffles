# Tasks: Admin Raffle Create Basic

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 300-380 |
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
| 1 | Admin raffle create slice end-to-end | PR 1 | Keep tests, routes, controller, views, and translations together. |

## Phase 1: RED - Create Flow Specs in Tests

- [x] 1.1 Create `tests/Feature/Raffles/AdminRaffleCreateTest.php` with guest HTML/JSON protection and authenticated `GET /raffles/create` render scenarios.
- [x] 1.2 Add POST create failure/success scenarios in `tests/Feature/Raffles/AdminRaffleCreateTest.php` for blank values → `null`, invalid `datetime-local` values → errors + old input, and valid `datetime-local` values → redirect + persisted draft.
- [x] 1.3 Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` with create CTA, scoped create-success flash, and no-flash-without-session coverage.

## Phase 2: GREEN - HTTP Entry Points and Persistence

- [x] 2.1 Update `routes/admin.php` to register authenticated `GET /raffles/create` and `POST /raffles` beside `admin.raffles.index`.
- [x] 2.2 Extend `app/Http/Controllers/Admin/RaffleController.php` with `create(): View` and `store(Request): RedirectResponse` using inline nullable `date_format:Y-m-d\TH:i` validation.
- [x] 2.3 In `RaffleController::store`, normalize blank `starts_at` / `ends_at` to `null`, persist through `Raffle::query()->create(...)`, and redirect to `route('admin.raffles.index')` with a dedicated create-success flash.

## Phase 3: GREEN - Admin UI and Copy

- [x] 3.1 Create `resources/views/admin/raffles/create.blade.php` with Tailwind v4 inline utilities, `datetime-local` inputs, field errors, old input, CSRF, submit, and cancel back to `admin.raffles.index`.
- [x] 3.2 Update `resources/views/admin/raffles/index.blade.php` to show a scoped create CTA and render the create-success flash without broad layout/navigation changes.
- [x] 3.3 Extend `lang/es/admin-raffles.php` with Spanish create labels, helper text, actions, and minimal success feedback keys used by both views.

## Phase 4: REFACTOR - Tighten the Slice

- [x] 4.1 Refactor duplicated admin raffle test helpers between `AdminRaffleCreateTest.php` and `AdminRaffleIndexTest.php` only if needed to keep scenarios readable without changing coverage.
- [x] 4.2 Run `bin/test` for the raffle/admin feature suite, then adjust naming or markup only where required to keep RED-GREEN-REFACTOR clean.
