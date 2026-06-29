## Exploration: public-raffle-catalog

### Current State
The public surface currently exposes `GET /` as a static Blade view in `routes/web.php`, rendered by `resources/views/public/home.blade.php` with only translation-backed title and description copy. Public raffle detail already exists at `GET /raffles/{raffle}` via `App\Http\Controllers\Public\RaffleController::show()`, constrained to numeric IDs and resolved through `Raffle::query()->publiclyVisible()->findOrFail($raffle)`, so only `published` raffles are reachable. The raffle model already provides the main listing rule through `scopePubliclyVisible()` and the participation/read-only lifecycle helper through `canAcceptParticipants()`. On the admin side, `App\Http\Controllers\Admin\RaffleController::index()` and `resources/views/admin/raffles/index.blade.php` show the existing table/listing pattern: controller loads a collection, view handles empty state and row rendering, and tests assert newest-first ordering plus explicit UI states.

### Affected Areas
- `routes/web.php` — public home will need to stop being a pure `Route::view()` if it must receive a raffle collection.
- `app/Http/Controllers/Public/RaffleController.php` — current public controller owns detail only; a future implementation may extend it with catalog/home listing or introduce a dedicated home/catalog controller.
- `app/Models/Raffle.php` — `scopePubliclyVisible()` is the canonical published-only filter and likely future listing entry point.
- `resources/views/public/home.blade.php` — current static home page would become the catalog/board entry view.
- `resources/views/public/raffles/show.blade.php` — likely consumes links from the future catalog but should remain detail-only.
- `tests/Feature/Raffles/PublicRaffleDetailTest.php` — existing detail coverage defines the published-only public contract that catalog links must honor.
- `tests/Feature/Routing/HomeTranslationsTest.php` — current public home test explicitly asserts there are no raffle links today; it will need replacement or expansion in a future slice.
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — useful conceptual reference for empty-state, ordering, and list rendering expectations.
- `openspec/specs/public-raffle-detail/spec.md` — explicitly kept catalog and home-page raffle links out of scope before; this new slice can now add them without reopening detail rules.

### Approaches
1. **Home-as-catalog** — turn the existing public home page into the first published-raffle board and link each row/card to the existing detail route.
   - Pros: Smallest user-visible slice; reuses the existing `/` entry point; matches the request for discovery before detail; keeps URL surface minimal.
   - Cons: Requires replacing `Route::view()` with controller-backed rendering; `HomeTranslationsTest` must be intentionally updated because it currently enforces “no links”.
   - Effort: Medium

2. **Separate catalog route** — keep `GET /` unchanged and add a dedicated public route such as `GET /raffles` for the catalog.
   - Pros: Lower regression risk to current home copy; cleaner separation between marketing/home and raffle listing.
   - Cons: Adds an extra navigation problem because discovery still starts at home; less aligned with the request wording about a public board/home discovery step.
   - Effort: Medium

### Recommendation
Use **Home-as-catalog** for the first slice, but keep the boundary narrow: list only `published` raffles, order them predictably (prefer newest-first by `id`, matching the admin list’s current convention unless product requirements say otherwise), render a clear empty state, and link to the existing numeric detail route. Do not add search, filters, pagination, slugs, featured sections, or participation CTAs in this slice.

### Risks
- The repo has no public catalog query or presentation contract yet, so ordering and the minimal card/row fields must be specified explicitly in proposal/spec work.
- `tests/Feature/Routing/HomeTranslationsTest.php` currently locks in “no `/raffles/` links on home”; that expectation must change with care rather than being silently removed.
- Reusing `Raffle::publiclyVisible()` means `closed` raffles stay excluded; if the product later wants historical/publicly closed raffles in the board, that is a separate domain change.

### Ready for Proposal
Yes — tell the user the codebase already supports the critical published-only detail contract, and the recommended next slice is a narrow home-page catalog that lists published raffles and links to existing detail pages without adding search, pagination, or new lifecycle rules.
