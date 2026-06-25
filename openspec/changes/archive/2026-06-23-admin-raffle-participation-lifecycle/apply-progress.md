# Apply Progress: Admin Raffle Participation Lifecycle

## Summary

- Change: `admin-raffle-participation-lifecycle`
- Mode: `Strict TDD`
- Delivery: `stacked PR slice`
- Current work unit: `PR 2 / Work Unit 2 — admin HTTP/routes/controller/views/lang + admin flow tests`
- Scope guard: `Kept PR 1 foundation intact; did not broaden into participants, tickets, payments, funding automation, or reopening`

## Completed Tasks

- [x] 1.1 Add `tests/Feature/Raffles/RaffleLifecycleTest.php` scenarios for `canAcceptParticipants()` across draft, published-open, published-not-open, participation-closed, overall-closed, and dates-not-gating cases.
- [x] 1.2 Add invalid-transition RED cases in `tests/Feature/Raffles/RaffleLifecycleTest.php` for opening non-published raffles and closing unopened/already-closed raffles.
- [x] 2.1 Create `tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` for admin-only POST open/close actions, redirect targets, DB audit fields, and 403/redirect behavior.
- [x] 2.2 Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` for open-button visibility, close-button visibility, hidden actions on ineligible rows, and scoped participation flash rendering only after matching redirects.
- [x] 3.1 Create `database/migrations/*_add_participation_lifecycle_to_raffles_table.php` for opened/closed timestamps, close reason, and nullable `participation_closed_by_admin_id` FK.
- [x] 3.2 Update `app/Models/Raffle.php` with casts, `admin()` relation, `canAcceptParticipants()`, `canOpenParticipation()`, `canCloseParticipation()`, `openParticipation()`, and `closeParticipation()` guards.
- [x] 3.3 Extend `database/factories/RaffleFactory.php` with opened/participation-closed states that support the new RED scenarios.
- [x] 4.1 Update `app/Http/Controllers/Admin/RaffleController.php` and `routes/admin.php` with admin-guarded open/close actions, actor resolution, scoped flashes, and invalid-transition handling.
- [x] 4.2 Update `resources/views/admin/raffles/index.blade.php` to show per-row open/close forms only for eligible raffles and render participation flash slots.
- [x] 4.3 Extend `lang/es/admin-raffles.php` with participation button labels and scoped open/close success copy.
- [x] 5.1 Run targeted `bin/test tests/Feature/Raffles/RaffleLifecycleTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` after GREEN passes.
- [x] 5.2 Run full `bin/test`, then review implementation against `openspec/changes/admin-raffle-participation-lifecycle/specs/*/spec.md` and `design.md` before PR prep.

## Remaining Tasks

- None.

## Files Changed

| File | Action | What Was Done |
|------|--------|---------------|
| `tests/Feature/Raffles/RaffleLifecycleTest.php` | Modified | Added RED→GREEN coverage for participation eligibility, timestamps-as-metadata, open/close transitions, and admin audit semantics. |
| `database/migrations/2026_06_22_230000_add_participation_lifecycle_to_raffles_table.php` | Created | Added nullable participation timestamps, close reason, and nullable admin audit foreign key with `nullOnDelete()`. |
| `app/Models/Raffle.php` | Modified | Added participation casts, admin relation, canonical eligibility checks, and guarded open/close transition methods. |
| `database/factories/RaffleFactory.php` | Modified | Added participation-open and participation-closed states used by lifecycle tests. |
| `tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` | Created | Added admin-route RED→GREEN coverage for auth protection, open/close redirects, audit persistence, invalid transitions, and dates-not-gating behavior. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modified | Added open/close action visibility checks and scoped participation flash coverage on the admin index. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Added admin participation open/close actions, strict admin actor resolution, scoped success flashes, and invalid-transition redirects with errors. |
| `routes/admin.php` | Modified | Registered admin-only POST participation open/close routes in both domain and fallback host setups. |
| `resources/views/admin/raffles/index.blade.php` | Modified | Rendered scoped participation success flashes and per-row open/close forms only when the raffle is eligible. |
| `lang/es/admin-raffles.php` | Modified | Added participation button labels and scoped success copy for open/close actions. |
| `openspec/changes/admin-raffle-participation-lifecycle/tasks.md` | Modified | Marked the cumulative PR 1 + PR 2 task slices complete and recorded the resolved chain strategy. |
| `openspec/changes/admin-raffle-participation-lifecycle/apply-progress.md` | Modified | Merged PR 1 and PR 2 progress into one cumulative strict-TDD artifact. |

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Feature/Model | ✅ `11/11` baseline passing | ✅ Wrote failing `canAcceptParticipants()` scenarios first | ✅ `18/18` passing after implementation | ✅ Covered published-open, published-unopened, draft, participation-closed, overall-closed, and dates-not-gating paths | ✅ Tightened assertions around persisted values and relation-backed audit |
| 1.2 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Feature/Model | ✅ `11/11` baseline passing | ✅ Wrote failing invalid-transition cases first | ✅ `18/18` passing after guards were implemented | ✅ Covered open failures for draft/opened/closed and close failures for unopened/already-closed | ✅ Reused canonical transition helpers instead of duplicating rule checks |
| 2.1 | `tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` | Feature/HTTP | ✅ `45/45` admin/domain safety net passing | ✅ Wrote failing guest/auth/open/close/invalid-transition HTTP scenarios before routes existed | ✅ `7/7` passing after route/controller wiring landed | ✅ Covered open success, close success, guest redirect, JSON 401, invalid open, invalid close, and date-metadata behavior through the admin flow | ✅ Split invalid-state setup into dataset factories and kept assertions on persisted behavior only |
| 2.2 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Feature/Blade | ✅ `45/45` admin/domain safety net passing | ✅ Added failing index assertions for open/close action visibility and scoped participation flashes first | ✅ `12/12` passing after view/lang wiring landed | ✅ Covered open-only rows, close-only rows, hidden ineligible rows, open flash, close flash, and no invented success feedback | ✅ Isolated flash scenarios into separate tests to avoid cross-session leakage |
| 3.1 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Feature/Model | ✅ `11/11` baseline passing | ✅ New lifecycle tests failed before schema existed | ✅ `18/18` passing after migration added columns | ✅ Same scenarios exercised open/close and persisted audit data across different state combinations | ➖ Structural migration only after green |
| 3.2 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Feature/Model | ✅ `11/11` baseline passing | ✅ New lifecycle tests referenced missing model API first | ✅ `18/18` passing after model API was added | ✅ Covered eligibility, guards, timestamps, and admin audit behavior | ✅ Kept `canAcceptParticipants()` as canonical rule source |
| 3.3 | `tests/Feature/Raffles/RaffleLifecycleTest.php` | Feature/Model | ✅ `11/11` baseline passing | ✅ Tests referenced missing factory states first | ✅ `18/18` passing after factory states were added | ✅ Used opened and participation-closed states across valid and invalid combinations | ✅ Factory states stay data-oriented so tests can compose edge cases cleanly |
| 4.1 | `tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` | Feature/HTTP | ✅ `45/45` admin/domain safety net passing | ✅ Controller/route expectations were encoded in failing HTTP tests before implementation | ✅ `7/7` passing after open/close endpoints and actor resolution were implemented | ✅ Same test file forced guest redirect, JSON 401, success redirects, audit persistence, and invalid-transition handling | ✅ Kept controller logic thin by delegating eligibility to the model and only handling transport concerns |
| 4.2 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Feature/Blade | ✅ `45/45` admin/domain safety net passing | ✅ View action/flash assertions failed before buttons and flash slots existed | ✅ `12/12` passing after Blade updates | ✅ Covered mutually exclusive action buttons and scoped flash rendering on matching redirects only | ✅ Grouped actions into one row container without adding behavior outside this slice |
| 4.3 | `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Feature/Blade | ✅ `45/45` admin/domain safety net passing | ✅ Index tests referenced missing participation labels/copy before lang entries existed | ✅ `12/12` passing after translation keys were added | ✅ Open and close copy are both exercised by UI assertions and scoped flash scenarios | ➖ Translation-only change once the behavioral tests were green |
| 5.1 | `tests/Feature/Raffles/RaffleLifecycleTest.php`, `tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php`, `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Verification | ✅ `45/45` admin/domain safety net passing before PR 2 RED work | ✅ Verification scope was defined by the task before the final combined run | ✅ `38/38` passing via `bin/test tests/Feature/Raffles/RaffleLifecycleTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` | ✅ Combined domain + HTTP + Blade coverage for the full PR 2 slice | ➖ No refactor in verification step |
| 5.2 | `bin/test` full suite | Verification | ✅ `45/45` admin/domain safety net passing before PR 2 RED work | ✅ Full-suite check remained pending until PR 2 implementation existed | ✅ `89/89` passing via full `bin/test` after targeted green | ✅ Reviewed the resulting behavior against proposal/spec/design boundaries and confirmed dates stay metadata-only with no participant/payment scope creep | ➖ No refactor in verification step |

## Test Summary

- Total tests written: 18
- Total tests passing: 89
- Layers used: Feature/Model (18), Feature/HTTP (7), Feature/Blade (13), Full-suite verification (89)
- Approval tests: None — no refactor-only task in this slice
- Pure functions created: 0

## Deviations from Design

None — implementation matches the design and kept PR 2 within the requested boundary.

## Issues Found

- None.

## Workload / PR Boundary

- Mode: `stacked PR slice`
- Boundary: starts at admin participation RED tests and ends at admin routes/controller/view/lang wiring plus targeted/full verification only
- Estimated review budget impact: focused PR 2 slice across tests, controller, routes, Blade, lang, and spec-progress artifacts; no participant/payment/funding/reopen expansion

## Status

12/12 tasks complete. Ready for verify.
