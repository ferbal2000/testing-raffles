# Tasks: Public Raffle Catalog

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 260-360 |
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
| 1 | Deliver public home catalog with tests and copy updates | PR 1 | Single review slice; keep RED/GREEN/REFACTOR evidence in the same branch |

## Phase 1: RED — Feature Coverage

- [x] 1.1 Update `tests/Feature/Routing/HomeTranslationsTest.php` to keep translated home-copy assertions while allowing catalog rendering and removing the old “no raffle links” rule.
- [x] 1.2 Create `tests/Feature/Raffles/PublicRaffleCatalogTest.php` for published-only visibility and explicit empty-state behavior from `public-raffle-catalog/spec.md`.
- [x] 1.3 Extend `tests/Feature/Raffles/PublicRaffleCatalogTest.php` for `id DESC` ordering, `/raffles/{id}` links, and absence of search, filters, pagination, CTA, `starts_at`, and `ends_at` on cards.

## Phase 2: GREEN — Route, Query, and View

- [x] 2.1 Modify `routes/web.php` so both public-host branches send `GET /` to `App\Http\Controllers\Public\RaffleController@index` and keep `public.raffles.show` numeric-only.
- [x] 2.2 Add `index()` in `app/Http/Controllers/Public/RaffleController.php` to load `Raffle::query()->publiclyVisible()->latest('id')->get()` and prepare status/availability strings for cards.
- [x] 2.3 Replace `resources/views/public/home.blade.php` with catalog markup that renders intro copy, simple cards, numeric detail links, and an explicit empty state only.
- [x] 2.4 Update `lang/es/home.php` with catalog-focused public copy and empty-state labels that no longer promise a future registration flow.

## Phase 3: REFACTOR — Keep the Slice Lean

- [x] 3.1 Refactor `app/Http/Controllers/Public/RaffleController.php` so `show()` and `index()` share only the smallest safe status/availability formatting helpers after tests are green.
- [x] 3.2 Tighten `tests/Feature/Raffles/PublicRaffleCatalogTest.php` assertions to prove the temporary newest-first fallback without coupling to unnecessary HTML structure.

## Phase 4: Verification

- [x] 4.1 Run `bin/test tests/Feature/Routing/HomeTranslationsTest.php tests/Feature/Raffles/PublicRaffleCatalogTest.php tests/Feature/Raffles/PublicRaffleDetailTest.php` to prove catalog visibility, numeric-link, slug-404, and empty-state scenarios.
- [x] 4.2 Re-read `openspec/changes/public-raffle-catalog/specs/public-raffle-catalog/spec.md` and `.../public-raffle-detail/spec.md`, then confirm no search/filter/pagination/slug/date-card scope leaked into the implementation.
