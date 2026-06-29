# Design: Public Raffle Catalog

## Technical Approach

Turn `GET /` from a static Blade route into a controller-backed catalog in the existing public raffle surface. Reuse `App\Http\Controllers\Public\RaffleController` and `Raffle::publiclyVisible()` so the home catalog and numeric detail page share the same published-only boundary. The controller will load visible raffles with `latest('id')`, pass them to `resources/views/public/home.blade.php`, and the view will render either simple catalog cards or an explicit empty state. In this slice, cards show raffle identity, status, availability, and the detail link; richer `starts_at` / `ends_at` metadata stays on the existing detail page.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Public entry point | New home controller vs extend `Public\RaffleController` | Extend `Public\RaffleController` with `index()` | Public raffle routes already live together and formatting helpers already exist there; adding a second controller would add indirection without new domain value. |
| Visibility boundary | Inline `status = published` query vs reuse model scope | Reuse `Raffle::publiclyVisible()` | Keeps catalog and detail access aligned, so `draft` and `closed` exclusions stay consistent in one place. |
| Catalog ordering | `created_at DESC`, `id DESC`, or add publication metadata | `id DESC` for this slice | Product approved it, `published_at` does not exist, and admin index already uses newest-first `latest('id')`, so this matches an existing project pattern. |
| Card content | Show full date metadata on cards vs keep cards availability-first | Simple cards keyed by raffle ID with status, availability, and detail link only | The user approved a lean first pass, the table has no public title/slug field, and the detail page already carries richer `starts_at` / `ends_at` metadata without crowding the catalog. |

## Data Flow

1. Public user requests `/`.
2. `routes/web.php` sends the request to `Public\RaffleController@index`.
3. `index()` loads `Raffle::query()->publiclyVisible()->latest('id')->get()`.
4. Controller returns `public.home` with the raffle collection.
5. Blade renders either:
   - simple cards linking to `route('public.raffles.show', $raffle)` with status/availability summaries only, or
   - an empty-state panel when the collection is empty.

```text
Browser -> GET /
  -> routes/web.php
  -> Public\RaffleController@index
  -> Raffle::publiclyVisible()->latest('id')->get()
  -> public.home Blade
  -> /raffles/{id} links
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | Modify | Replace `Route::view('/')` with controller-backed home route while keeping host scoping and existing detail route intact. |
| `app/Http/Controllers/Public/RaffleController.php` | Modify | Add `index()` and reuse/extend lightweight translation helpers for status/availability-only catalog card metadata. |
| `resources/views/public/home.blade.php` | Modify | Replace placeholder copy-only view with intro copy, explicit empty state, and simple catalog cards that omit `starts_at` / `ends_at`. |
| `lang/es/home.php` | Modify | Update stale “coming soon” public copy and add catalog-specific empty-state/action labels. |
| `tests/Feature/Routing/HomeTranslationsTest.php` | Modify | Assert translated home copy still renders while the page now allows raffle links. |
| `tests/Feature/Raffles/PublicRaffleCatalogTest.php` | Create | Cover visible-only results, `id DESC` ordering, numeric detail links, and empty state. |

## Interfaces / Contracts

```php
// Controller -> view contract
return view('public.home', [
    'raffles' => Collection<int, Raffle>,
]);

// Query contract
Raffle::query()
    ->publiclyVisible()   // published only
    ->latest('id')        // temporary newest-created-record fallback
    ->get();
```

The home view must not expose slug routes, search inputs, pagination controls, participation CTAs, or `starts_at` / `ends_at` fields on cards. Each card links only to `/raffles/{id}`. The detail page remains the richer public surface for date metadata.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | None beyond current model scope behavior | Keep this slice at feature level; no new isolated domain logic is introduced. |
| Integration | Public home catalog visibility, ordering, empty state, links, and absence of card dates | Add Pest feature coverage using published/draft/closed factories, `assertSeeInOrder`, and assertions that `starts_at` / `ends_at` values stay off catalog cards. |
| E2E | N/A | No browser test layer is configured in this repository. |

## Migration / Rollout

No migration required. This is a route/controller/view change on top of existing raffle data.

## Open Questions

None.
