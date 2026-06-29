# Proposal: Public Raffle Catalog

## Intent

Expose published raffles on the public home so visitors can discover active raffles before opening the existing detail page. This closes the current gap where `/` is static and discovery depends on already knowing a numeric raffle URL.

## Scope

### In Scope
- Turn `GET /` into a controller-backed public catalog view.
- List only publicly visible raffles and link each item to `GET /raffles/{id}`.
- Define temporary explicit ordering as highest raffle `id` first, plus a minimal empty state and item fields.

### Out of Scope
- Search, filters, pagination, slugs, featured sections, or participation CTAs.
- Showing `closed` raffles; that can wait for filters or a dedicated closed-raffles section.

## Capabilities

### New Capabilities
- `public-raffle-catalog`: Public home lists discoverable raffles that are already published and links to the existing numeric detail route.

### Modified Capabilities
- `public-raffle-detail`: Remove the current “no home-page discovery links” restriction while preserving the published-only numeric detail contract.

## Approach

Use the home-as-catalog boundary from exploration. Query through `Raffle::publiclyVisible()` so `draft` and `closed` raffles stay excluded, then render the collection in `resources/views/public/home.blade.php`. For this slice, order results by `id` descending. This is an explicit temporary fallback because the schema has no `published_at`; it MUST be documented as "newest created record first," not true publication-date semantics.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/web.php` | Modified | Replace static home route with controller-backed catalog rendering. |
| `app/Http/Controllers/Public/*` | Modified/New | Add catalog query/composition entry point. |
| `app/Models/Raffle.php` | Modified | Reuse `scopePubliclyVisible()` as the published-only boundary. |
| `resources/views/public/home.blade.php` | Modified | Render catalog items and empty state. |
| `tests/Feature/Routing/HomeTranslationsTest.php` | Modified | Replace current “no raffle links” expectation. |
| `openspec/specs/public-raffle-detail/spec.md` | Modified | Remove discovery out-of-scope constraint. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Users may read ID order as publication order | Med | State in spec/design that `id DESC` is temporary until real publication metadata exists. |
| Home test regressions from static-to-dynamic shift | Med | Update tests to assert copy, empty state, and valid links explicitly. |

## Rollback Plan

Restore the static `Route::view('/')`, remove catalog rendering from the public home view, and drop the new catalog requirements while keeping existing raffle detail behavior unchanged.

## Dependencies

- Existing `public-raffle-detail` contract and `Raffle::publiclyVisible()` query scope.
- No `published_at` column exists on `raffles` in this slice.

## Success Criteria

- [ ] Public home shows published raffles only and never shows `draft` or `closed` raffles.
- [ ] Each visible catalog item links to the existing numeric detail route.
- [ ] Empty catalog state is explicit and ordering is specified as temporary `id` descending fallback, not publication-date behavior.
