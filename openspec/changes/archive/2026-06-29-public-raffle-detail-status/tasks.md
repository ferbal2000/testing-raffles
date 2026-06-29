# Tasks: Public Raffle Detail Status

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 220-320 |
| 400-line budget risk | Medium |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Public published-only detail route, copy, and tests | PR 1 | Keep RED/GREEN/REFACTOR together; verify with `bin/test` |

## Phase 1: RED - Visibility and content tests

- [x] 1.1 Create `tests/Feature/Raffles/PublicRaffleDetailTest.php` for public-host `GET /raffles/{id}` success on `published` and `404` on `draft` / `closed`.
- [x] 1.2 In `tests/Feature/Raffles/PublicRaffleDetailTest.php`, add scenarios proving no registration/ticket actions appear and `starts_at` / `ends_at` stay informational only.
- [x] 1.3 Create `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php` to assert translated Spanish copy is rendered and raw enum values like `published` never appear.
- [x] 1.4 Extend `tests/Feature/Raffles/RaffleLifecycleTest.php` with scope/helper coverage for published-only public visibility queries.

## Phase 2: GREEN - Domain and routing

- [x] 2.1 Update `app/Models/Raffle.php` with a reusable `publiclyVisible()` query scope/helper limited to `RaffleStatus::Published`.
- [x] 2.2 Create `app/Http/Controllers/Public/RaffleController.php` to resolve raffles via `Raffle::query()->publiclyVisible()->findOrFail($id)` and return a read-only view.
- [x] 2.3 Modify `routes/web.php` to add the public-host route and name without altering admin bindings or global `Raffle` resolution.

## Phase 3: GREEN - Public presentation

- [x] 3.1 Create `lang/es/public-raffles.php` with friendly lifecycle, availability, labels, and empty-date copy for the public page.
- [x] 3.2 Create `resources/views/public/raffles/show.blade.php` to render friendly status and availability messaging, optional metadata, and no raw status labels.
- [x] 3.3 Keep the page body free of raffle numeric ID text; allow the identifier only in the URL for this slice.

## Phase 4: REFACTOR - Tidy implementation seams

- [x] 4.1 Refactor duplicated status/availability mapping between `app/Http/Controllers/Public/RaffleController.php` and `resources/views/public/raffles/show.blade.php` only if tests are green.
- [x] 4.2 Re-check `routes/web.php` and `app/Models/Raffle.php` so public resolution stays isolated from admin flows.

## Phase 5: VERIFY

- [x] 5.1 Run `bin/test --filter=PublicRaffleDetailTest` and fix failures before widening scope.
- [x] 5.2 Run `bin/test --filter=PublicRaffleDetailTranslationsTest` and `bin/test --filter=RaffleLifecycleTest`.
- [x] 5.3 Run full `bin/test`; no frontend build is expected unless Blade changes introduce new assets.

## Phase 6: VERIFY Corrections

- [x] 6.1 Extend `tests/Feature/Raffles/PublicRaffleDetailTest.php` to prove non-numeric `/raffles/not-a-number` requests return `404`.
- [x] 6.2 Constrain the public detail route in `routes/web.php` to numeric IDs while preserving admin/public route separation.
- [x] 6.3 Extend `tests/Feature/Routing/HomeTranslationsTest.php` to prove the public home remains non-discovery only with no raffle links.
- [x] 6.4 Strengthen `tests/Feature/Raffles/RaffleLifecycleTest.php` with explicit `remains published` / `remains closed` state assertions.
- [x] 6.5 Re-run `bin/test --filter=PublicRaffleDetailTranslationsTest`, `bin/test --filter=PublicRaffleDetailTest`, `bin/test --filter=HomeTranslationsTest`, `bin/test --filter=RaffleLifecycleTest`, and full `bin/test`.
