## Exploration: public-raffle-detail-status

### Current State
The public HTTP surface currently has only the host-scoped home page at `GET /` in `routes/web.php`, rendered by `resources/views/public/home.blade.php`; there is no public raffle list, detail route, public raffle controller, or public raffle-focused test coverage. The raffle domain already exposes the core state needed for a read-only public status page through `App\Models\Raffle`: `status` governs publication/visibility, `canAcceptParticipants()` is the canonical participation gate, and `starts_at` / `ends_at` remain metadata only.

### Affected Areas
- `routes/web.php` — the public host route set must grow beyond `GET /` to expose a raffle detail endpoint.
- `app/Models/Raffle.php` — existing `status` and `canAcceptParticipants()` should be reused as the source of truth for visibility/availability messaging.
- `app/Enums/RaffleStatus.php` — existing lifecycle values define the user-facing state vocabulary that the detail page must map into copy.
- `resources/views/public/home.blade.php` — useful reference for the current public Blade style and translation-driven copy, but it should stay out of scope unless a link is explicitly needed.
- `resources/views/components/layouts/app.blade.php` — the shared narrow layout favors a compact detail/status presentation over a dense catalog UI.
- `tests/Feature/Routing/HostSeparationTest.php` and `tests/Feature/Routing/HomeTranslationsTest.php` — show the existing public-host routing and translation expectations that future public detail tests should follow.
- `openspec/specs/raffle-lifecycle/spec.md` — already states that `published` controls publication/visibility and that published raffles can still be unavailable for participation.
- `openspec/specs/raffle-participation-lifecycle/spec.md` — already requires `Raffle::canAcceptParticipants()` as the only participation eligibility gate.

### Approaches
1. **Direct public raffle detail route** — add a dedicated public-host detail endpoint such as `GET /raffles/{raffle}` that renders one raffle's lifecycle and participation availability without enabling registration.
   - Pros: Smallest real product slice; reuses existing domain rules; avoids inventing catalog/search concerns; easy to test with the current Blade-first architecture.
   - Cons: Exposes detail by direct entry only unless a later slice adds discovery/navigation; route model binding needs an explicit visibility rule so hidden raffles do not leak.
   - Effort: Low

2. **Home-page or catalog-first public discovery** — expand `GET /` into a raffle listing/featured experience before adding a dedicated detail page.
   - Pros: Gives users a discoverable entry point immediately.
   - Cons: Expands scope into catalog ordering, empty states, navigation, and possibly pagination/filtering; not the smallest safe slice.
   - Effort: Medium

### Recommendation
Use the direct public raffle detail route as the first public slice. Keep it read-only and narrowly scoped to: load one publicly visible raffle, show lifecycle/status information, show participation availability derived only from `canAcceptParticipants()`, and render `starts_at` / `ends_at` as informational metadata. Do not add registration actions, ticket intent, catalog browsing, or date-based gating logic.

### Risks
- The codebase has no current public raffle visibility query, so proposal/spec work must define whether non-public raffles return `404` and whether `closed` raffles remain visible or not.
- There is no public-friendly raffle identifier yet beyond numeric `id`; if raw IDs are undesirable, that is a separate scope increase.
- Public-facing copy is currently Spanish while SDD artifacts are English, so proposal/spec work should keep artifact language English but respect existing app copy conventions for implementation later.
- If the page shows status text, the team must decide whether to present raw lifecycle labels (`draft`, `published`, `closed`) or user-friendly messaging mapped from those states.

### Ready for Proposal
No — first confirm the public visibility rule (`published` only vs `published` + `closed`) and whether the first route can use direct numeric IDs without a public catalog/discovery step.
