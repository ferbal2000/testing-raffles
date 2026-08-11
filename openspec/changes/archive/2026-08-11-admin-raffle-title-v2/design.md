# Design: Admin Raffle Title V2

## Technical Approach

Implement the four modified capabilities entirely at the Blade presentation boundary. The existing controllers already pass complete `Raffle` models to every view, and their current queries, numeric routes, visibility scopes, ordering, lifecycle messages, participation checks, registration counts, and registration snapshot remain sufficient. Each surface will render `{{ $raffle->title }}` as the primary heading/cell content and the numeric ID in a distinct secondary element. Escaped Blade interpolation is mandatory, including for backfilled `Sorteo #<ID>` titles.

## Architecture Decisions

| Decision | Alternatives considered | Rationale |
|---|---|---|
| Render identity directly in each existing Blade view | Shared component; presenter/view model; controller-composed identity | Four small surface-specific layouts do not justify a new abstraction. Direct rendering preserves existing data flow and keeps escaping visible at the output boundary. |
| Keep title, numeric ID, and public-detail lifecycle status as separate elements | Concatenated identity string; title replacing ID; status as the heading | Separate elements satisfy all four specs, preserve operational ID context, and prevent backfilled titles from suppressing the ID. |
| Add only narrowly scoped Spanish identity labels | Hard-coded labels; broad copy rewrite | Translation keys preserve the existing Spanish-copy convention without changing unrelated wording. |
| Prove compatibility through focused HTTP feature tests | Controller/model changes; broad snapshot replacement | Current behavior is already covered at the HTTP boundary; extending those tests detects presentation regressions without changing business logic. |

## Data Flow

    Existing numeric route/query ──→ existing Raffle model ──→ existing Blade view
                                                               ├─ escaped title (primary)
                                                               ├─ numeric ID (secondary)
                                                               └─ existing status/actions/data unchanged

The registrations JSON branch returns `RaffleRegistrationSnapshot` before Blade rendering and therefore remains byte-structure independent of the new HTML heading.

## File Changes

| File | Action | Description |
|---|---|---|
| `resources/views/admin/raffles/index.blade.php` | Modify | Add a title column as primary identity; retain the ID column separately. |
| `resources/views/admin/raffles/registrations.blade.php` | Modify | Render title heading and a separate ID reference without touching snapshot markup. |
| `resources/views/public/home.blade.php` | Modify | Replace synthesized `Sorteo #ID` heading with persisted title plus separate ID; retain numeric link and status. |
| `resources/views/public/raffles/show.blade.php` | Modify | Use persisted title as `h1`, add separate ID, and retain lifecycle status in its existing definition list. |
| `lang/es/admin-raffles.php` | Modify | Add only admin title/identity labels required by the two views. |
| `lang/es/public-raffles.php` | Modify | Add the reusable public numeric-ID label. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modify | Add RED cases for title/ID, escaping, backfilled identity, sparse data, routes, actions, counts, and ordering. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Modify | Add RED cases for heading identity and escaping while retaining auth, newest-first rows, actions, pagination, counts, and exact JSON contract assertions. |
| `tests/Feature/Raffles/PublicRaffleCatalogTest.php` | Modify | Add RED cases for identity, escaping, backfill, visibility, descending-ID order, and numeric links. |
| `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Modify | Add RED cases for identity and escaping; update the obsolete ID-hidden assertion while retaining visibility, lifecycle, dates, and participation coverage. |

No controller, query, model, schema, migration, route, JSON resource, JavaScript, or shared-component change is planned. If implementation evidence makes one necessary, stop as blocked rather than widening scope.

## Interfaces / Contracts

No API or domain interface changes. Numeric URLs and the `RaffleRegistrationSnapshot` field set and semantics remain unchanged. HTML contracts gain escaped title-primary and ID-secondary elements; public detail keeps status as a separate translated value.

## Testing Strategy

Strict TDD applies per surface: (1) add the smallest focused RED assertion set and run its test file through `bin/test`; (2) make only the corresponding Blade/translation change; (3) rerun GREEN; (4) refactor markup only while green. After all four cycles, run the focused four files, then full `bin/test`. Existing assertions remain the compatibility net for routes, lifecycle/actions, visibility, participation eligibility, ordering, pagination/counts, and registration JSON; do not replace them with title-only tests.

## Threat Matrix

N/A — this design changes no routing, shell, subprocess, VCS/PR automation, executable-file classification, or process-integration boundary.

## Migration / Rollout

No migration or feature flag is required. Roll back the four Blade edits, the two small translation edits, and their focused test changes together; persisted titles and existing behavior remain intact.

Final normalize/verify → stage complete candidate → review START is a delivery boundary only, not architecture or product behavior. This design phase does not cross it.

## Open Questions

None.
