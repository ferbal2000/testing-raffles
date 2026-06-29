# Apply Progress: public-raffle-detail-status

## Mode

Strict TDD

## Completed Tasks

- [x] 1.1 Create `tests/Feature/Raffles/PublicRaffleDetailTest.php` for public-host `GET /raffles/{id}` success on `published` and `404` on `draft` / `closed`.
- [x] 1.2 In `tests/Feature/Raffles/PublicRaffleDetailTest.php`, add scenarios proving no registration/ticket actions appear and `starts_at` / `ends_at` stay informational only.
- [x] 1.3 Create `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php` to assert translated Spanish copy is rendered and raw enum values like `published` never appear.
- [x] 1.4 Extend `tests/Feature/Raffles/RaffleLifecycleTest.php` with scope/helper coverage for published-only public visibility queries.
- [x] 2.1 Update `app/Models/Raffle.php` with a reusable `publiclyVisible()` query scope/helper limited to `RaffleStatus::Published`.
- [x] 2.2 Create `app/Http/Controllers/Public/RaffleController.php` to resolve raffles via `Raffle::query()->publiclyVisible()->findOrFail($id)` and return a read-only view.
- [x] 2.3 Modify `routes/web.php` to add the public-host route and name without altering admin bindings or global `Raffle` resolution.
- [x] 3.1 Create `lang/es/public-raffles.php` with friendly lifecycle, availability, labels, and empty-date copy for the public page.
- [x] 3.2 Create `resources/views/public/raffles/show.blade.php` to render friendly status and availability messaging, optional metadata, and no raw status labels.
- [x] 3.3 Keep the page body free of raffle numeric ID text; allow the identifier only in the URL for this slice.
- [x] 4.1 Refactor duplicated status/availability mapping between `app/Http/Controllers/Public/RaffleController.php` and `resources/views/public/raffles/show.blade.php` only if tests are green.
- [x] 4.2 Re-check `routes/web.php` and `app/Models/Raffle.php` so public resolution stays isolated from admin flows.
- [x] 5.1 Run `bin/test --filter=PublicRaffleDetailTest` and fix failures before widening scope.
- [x] 5.2 Run `bin/test --filter=PublicRaffleDetailTranslationsTest` and `bin/test --filter=RaffleLifecycleTest`.
- [x] 5.3 Run full `bin/test`; no frontend build is expected unless Blade changes introduce new assets.
- [x] 6.1 Extend `tests/Feature/Raffles/PublicRaffleDetailTest.php` to prove non-numeric `/raffles/not-a-number` requests return `404`.
- [x] 6.2 Constrain the public detail route in `routes/web.php` to numeric IDs while preserving admin/public route separation.
- [x] 6.3 Extend `tests/Feature/Routing/HomeTranslationsTest.php` to prove the public home remains non-discovery only with no raffle links.
- [x] 6.4 Strengthen `tests/Feature/Raffles/RaffleLifecycleTest.php` with explicit `remains published` / `remains closed` state assertions.
- [x] 6.5 Re-run `bin/test --filter=PublicRaffleDetailTranslationsTest`, `bin/test --filter=PublicRaffleDetailTest`, `bin/test --filter=HomeTranslationsTest`, `bin/test --filter=RaffleLifecycleTest`, and full `bin/test`.

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (new) | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTest` passed after route/controller/view implementation | ✅ Published resolves, draft 404, closed 404 | ✅ Kept public lookup scoped in controller |
| 1.2 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (new) | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTest` passed with read-only page copy | ✅ Open participation, closed participation, date metadata scenarios | ✅ No action links and no numeric ID rendered |
| 1.3 | `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php` | Integration | ✅ `HomeTranslationsTest` 2/2 | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTranslationsTest` passed | ✅ Translation override plus raw `published` rejection | ➖ None needed |
| 1.4 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Integration | ✅ `RaffleLifecycleTest` 18/18 | ✅ Written | ✅ `bin/test --filter=RaffleLifecycleTest` passed after scope implementation | ✅ Published included, draft/closed excluded | ➖ None needed |
| 2.1 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Integration | ✅ `RaffleLifecycleTest` 18/18 | ✅ Written | ✅ `bin/test --filter=RaffleLifecycleTest` passed after `publiclyVisible()` scope was added | ✅ Collection and `find()` coverage use different code paths | ✅ Added reusable scope without touching existing transitions |
| 2.2 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (new) | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTest` passed after `Public\RaffleController` was added | ✅ Visible/not-found and availability branches | ✅ Controller only prepares display strings |
| 2.3 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | ✅ `HostSeparationTest` 4/4 | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTest` passed after public route registration | ✅ Public host route resolves while hidden raffles stay 404 | ✅ Admin bindings left untouched |
| 3.1 | `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php` | Integration | ✅ `HomeTranslationsTest` 2/2 | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTranslationsTest` passed after `lang/es/public-raffles.php` was added | ✅ Translation labels and availability copy both exercised | ➖ None needed |
| 3.2 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (new) | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTest` passed after Blade view implementation | ✅ Status, availability, and metadata rendering covered | ✅ View stays read-only |
| 3.3 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (new) | ✅ Written | ✅ `bin/test --filter=PublicRaffleDetailTest` passed with `assertDontSeeText((string) $raffle->id)` | ✅ URL uses id while body hides it | ➖ None needed |
| 4.1 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (new) | ✅ Written | ✅ Existing green suite stayed green | ✅ Controller-view seam exercised by multiple page states | ✅ Avoided status/availability duplication by centralizing messages in controller |
| 4.2 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | ✅ `HostSeparationTest` 4/4 and `RaffleLifecycleTest` 18/18 | ✅ Written | ✅ Relevant suites stayed green | ✅ Public route resolution and scope isolation both covered | ✅ No global route-model binding changes |
| 5.1 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | N/A (verification task) | ✅ Written earlier | ✅ `bin/test --filter=PublicRaffleDetailTest` | ➖ Single verification command | ➖ None needed |
| 5.2 | `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php`, `tests/Feature/Raffles/RaffleLifecycleTest.php` | Integration | N/A (verification task) | ✅ Written earlier | ✅ `bin/test --filter=PublicRaffleDetailTranslationsTest` and `bin/test --filter=RaffleLifecycleTest` | ➖ Single verification step | ➖ None needed |
| 5.3 | Full suite | Integration | N/A (verification task) | ✅ Written earlier | ✅ `bin/test` (95 passed) | ➖ Single verification step | ➖ None needed |
| 6.1 | `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Integration | ✅ `bin/test --filter=PublicRaffleDetailTest` 3/3 | ✅ Added `/raffles/not-a-number` expectation first; targeted run failed with `500` before the route fix | ✅ `bin/test --filter=PublicRaffleDetailTest` passed after adding numeric route constraints | ✅ Published, draft, closed, and non-numeric paths now cover distinct resolution branches | ✅ Kept the fix at the route boundary instead of changing controller semantics |
| 6.2 | `tests/Feature/Raffles/PublicRaffleDetailTest.php`, `tests/Feature/Routing/HostSeparationTest.php` | Integration | ✅ `bin/test --filter=PublicRaffleDetailTest` 3/3 and `bin/test --filter=HostSeparationTest` 4/4 | ✅ Existing route regression from 6.1 described the missing numeric guard before production changes | ✅ `bin/test --filter=PublicRaffleDetailTest` stayed green after `whereNumber('raffle')` was added in both route branches | ✅ Public resolution and host-boundary coverage still pass together | ➖ None needed |
| 6.3 | `tests/Feature/Routing/HomeTranslationsTest.php` | Integration | ✅ `bin/test --filter=HomeTranslationsTest` 2/2 | ✅ Added runtime non-discovery assertions before any production change; behavior was already compliant so the new coverage acted as an approval-style RED/GREEN step | ✅ `bin/test --filter=HomeTranslationsTest` passed with no production code changes required | ✅ Translation copy and absence of raffle links are asserted together | ➖ None needed |
| 6.4 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Integration | ✅ `bin/test --filter=RaffleLifecycleTest` 20/20 | ✅ Added explicit `Published` / `Draft` / `Closed` state assertions before touching production code; behavior was already compliant so this was approval-style coverage strengthening | ✅ `bin/test --filter=RaffleLifecycleTest` passed with the stronger assertions | ✅ Published-open, published-unopened, draft, participation-closed, and overall-closed paths are all exercised | ➖ None needed |
| 6.5 | Targeted suites and full suite | Integration | N/A (verification task) | ✅ Written earlier | ✅ `bin/test --filter=PublicRaffleDetailTranslationsTest`, `bin/test --filter=PublicRaffleDetailTest`, `bin/test --filter=HomeTranslationsTest`, `bin/test --filter=RaffleLifecycleTest`, and `bin/test` all passed | ✅ Targeted regression coverage plus full-suite confirmation | ➖ None needed |

## Test Summary

- **Total tests written**: 6
- **Total tests passing**: 95
- **Layers used**: Unit (0), Integration (6), E2E (0)
- **Approval tests** (refactoring): 2 approval-style coverage extensions (`HomeTranslationsTest`, `RaffleLifecycleTest`) for already-correct runtime behavior
- **Pure functions created**: 0

## Deviations from Design

None — implementation matches the confirmed design decisions, keeps numeric IDs out of the page body, and updates the artifacts to the confirmed ID-first future `/raffles/{id}/{slug?}` strategy.

## Issues Found

None.

## Workload / PR Boundary

- Mode: single PR
- Current work unit: Unit 1 follow-up — verify corrections for route safety, non-discovery coverage, and lifecycle assertions
- Boundary: Fixes verification gaps for the public raffle detail slice only; leaves catalog, optional slug decoration, and participation actions out of scope
- Estimated review budget impact: Small follow-up inside the original single-unit slice

## Status

20/20 tasks complete. Ready for verify.
