# Exploration: admin-raffle-title-v2

## Exploration: Persisted raffle identity on existing surfaces

### Current State

The branch gate is satisfied: `feat/admin-raffle-title-v2` is clean at `60b0cb592d095b1835cab08f2542f043e3c9c077`, which matches `origin/main`. GitHub issue #73 is open with `enhancement`, `status:approved`, and `type:feature`; it remains the authority for the end-to-end persisted raffle title capability.

**Already delivered by PR #74 (persistence and backfill):**

- `raffles.title` exists as a bounded `varchar(100)` field.
- Existing null titles are backfilled as `Sorteo #<ID>` in stable ID-based chunks.
- The `Raffle` model and factory include `title`.
- Migration coverage verifies preservation of authored values, safe rollback behavior, and the absence of a permanent database default.

**Already delivered by PR #75 (admin authoring and required invariant):**

- Admin create and edit forms expose an escaped, required title input with a 100-character limit.
- Store and update normalize leading/trailing whitespace, reject missing, whitespace-only, non-string, and overlong values, and allow duplicates.
- Title updates remain available under the existing edit permissions for draft, published, and closed raffles.
- The database now enforces a non-null title after a defensive final backfill.
- Existing tests cover validation, persistence, editability, duplicate titles, escaping in the edit input, and the migration constraint.

**Still missing for issue #73:**

- The admin raffle index renders only numeric ID and lifecycle metadata; it does not render the persisted title.
- The admin registration/participation list heading renders only `Inscripciones del sorteo #<ID>`; it does not render the persisted title.
- The public catalog synthesizes `Sorteo #<ID>` instead of rendering the persisted title.
- The public detail heading renders lifecycle status instead of raffle identity and currently omits the numeric ID.
- Existing tests preserve those numeric-only/status-only outputs and do not prove escaped persisted-title rendering across these four read surfaces.

The minimal remaining product slice is therefore presentation-only: render the persisted title together with the numeric ID on the existing admin index, admin registration list, public catalog, and public detail surfaces, then add focused coverage for identity, escaping, visibility, and unchanged lifecycle behavior. Current controllers already load complete `Raffle` models, so no query, model, schema, route, resource, or lifecycle change is required. The registration JSON snapshot does not need a title because issue #73 explicitly excludes authoritative JSON unless an existing client requires it, and the current client does not use title identity from that snapshot.

This directly completes the remaining issue #73 acceptance criteria for visible persisted identity while preserving numeric routes and operational references. Route/slug behavior, lifecycle transitions, visibility rules, participation rules, catalog ordering, and unrelated UI redesign remain out of scope.

**V1 workflow incidents that must not be repeated:**

- Do not revisit the GitHub template detour; PR #72 already fixed it and issue #73 is already approved.
- Use `admin-raffle-title-v2` as the final change name from the start.
- Do not rename OpenSpec requirements as a workaround; future specification work should modify the affected requirements directly.
- Do not run pre-commit before the complete candidate is staged.
- Do not inspect, apply, pop, alter, or use the cancelled V1 stash as an artifact source.
- Leave historical Gentle AI authority untouched.

**Future execution constraint, not a product requirement:** after implementation, perform final normalization and verification first, stage the complete candidate, and only then start the controlled review boundary so review START observes the full candidate identity. This constraint must not appear as product behavior in issue-derived requirements.

### Affected Areas

- `resources/views/admin/raffles/index.blade.php` — add persisted title to each existing row while retaining numeric ID.
- `resources/views/admin/raffles/registrations.blade.php` — identify the current raffle by persisted title and numeric ID in the page heading.
- `resources/views/public/home.blade.php` — replace synthesized numeric-only card identity with escaped persisted title plus numeric ID.
- `resources/views/public/raffles/show.blade.php` — make persisted title the primary identity, retain status as lifecycle information, and show numeric ID.
- `lang/es/admin-raffles.php` — adjust index/registration identity labels or interpolation without embedding user-authored title in translation HTML.
- `lang/es/home.php` and `lang/es/public-raffles.php` — adjust identity labels only where needed for title-plus-ID presentation.
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — prove title-plus-ID rows, ordering compatibility, and escaped output.
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — prove title-plus-ID heading and escaped output without changing snapshot semantics.
- `tests/Feature/Raffles/PublicRaffleCatalogTest.php` — replace synthesized-label assertions with persisted-title identity, escaping, visibility, and existing ordering assertions.
- `tests/Feature/Raffles/PublicRaffleDetailTest.php` — replace the current ID-absence expectation with title-plus-ID identity and escaped-output coverage while retaining lifecycle assertions.
- `tests/Feature/Raffles/AdminRaffleCloseTest.php` — update the public-catalog regression to identify the raffle by its persisted title while preserving close visibility behavior.
- `tests/Feature/Routing/HomeTranslationsTest.php` and `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php` — update only if identity labels use new or changed translation keys.

### Approaches

1. **Direct rendering in existing Blade surfaces** — render `{{ $raffle->title }}` and the existing numeric ID in each current identity location, with small translation-key adjustments and focused feature tests.
   - Pros: Minimal scope; uses data already loaded; preserves Blade's escaped output path; no new abstraction, query, or route behavior.
   - Cons: Four templates express the identity layout independently; tests must cover each surface.
   - Effort: Low

2. **Introduce a shared raffle identity component or presenter** — centralize title-plus-ID formatting and reuse it across admin and public templates.
   - Pros: One formatting policy and potentially fewer repeated markup decisions.
   - Cons: Adds an abstraction and cross-surface coupling for four simple render sites; admin table, admin heading, public card, and public detail need different semantics and markup; expands issue #73 unnecessarily.
   - Effort: Medium

### Recommendation

Use direct rendering in the four existing Blade surfaces. Keep the persisted title as plain Blade text so Laravel escaping remains authoritative, retain the numeric ID as adjacent visible context, and preserve lifecycle/status copy in its current secondary locations. Add explicit malicious-title assertions on representative admin and public surfaces, plus title-and-ID assertions on all four surfaces. Do not change controllers, routes, model behavior, migrations, registration snapshot JSON, lifecycle rules, visibility, or ordering.

### Risks

- Current tests intentionally encode the old identity contract (`Sorteo #<ID>` in the catalog and no numeric ID on public detail); these assertions must be changed deliberately rather than broadly weakened.
- A persisted title can equal its backfilled `Sorteo #<ID>` value, so tests should use distinct authored titles to prove the source of rendered identity.
- User-authored titles must remain inside escaped Blade interpolation; raw HTML helpers or translation strings containing authored markup would violate issue #73.
- The canonical OpenSpec capability files still describe pre-title create/edit/list/detail behavior because the delivered PR increments were not archived into those specs; later spec work must reconcile current code without renaming requirements or re-implementing delivered behavior.
- Review process sequencing remains a delivery constraint and must not leak into product requirements.

### Ready for Proposal

Yes. The orchestrator should first revalidate the existing issue gate read-only: issue #73 is open, approved, uniquely typed as a feature, and its remaining acceptance gap is exactly the four title-identity render surfaces plus focused regression coverage. Proposal work may then scope only that remaining presentation slice and must preserve all explicit non-goals.
