# Tasks: Admin Raffle Participation Lifecycle

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 430-560 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 tests+domain → PR 2 admin wiring+UI |
| Delivery strategy | ask-always |
| Chain strategy | stacked-to-main |

Decision needed before apply: Resolved by user
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Lock domain rules with RED→GREEN model coverage | PR 1 | Commit by work unit: tests with migration/model/factory. |
| 2 | Wire admin open/close flow and scoped feedback | PR 2 | Depends on PR 1; include controller/routes/view/lang/tests. |

## Phase 1: RED — Domain rules

- [x] 1.1 Add `tests/Feature/Raffles/RaffleLifecycleTest.php` scenarios for `canAcceptParticipants()` across draft, published-open, published-not-open, participation-closed, overall-closed, and dates-not-gating cases.
- [x] 1.2 Add invalid-transition RED cases in `tests/Feature/Raffles/RaffleLifecycleTest.php` for opening non-published raffles and closing unopened/already-closed raffles.

## Phase 2: RED — Admin flow and index behavior

- [x] 2.1 Create `tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php` for admin-only POST open/close actions, redirect targets, DB audit fields, and 403/redirect behavior.
- [x] 2.2 Extend `tests/Feature/Raffles/AdminRaffleIndexTest.php` for open-button visibility, close-button visibility, hidden actions on ineligible rows, and scoped participation flash rendering only after matching redirects.

## Phase 3: GREEN — Persistence and domain

- [x] 3.1 Create `database/migrations/*_add_participation_lifecycle_to_raffles_table.php` for opened/closed timestamps, close reason, and nullable `participation_closed_by_admin_id` FK.
- [x] 3.2 Update `app/Models/Raffle.php` with casts, `admin()` relation, `canAcceptParticipants()`, `canOpenParticipation()`, `canCloseParticipation()`, `openParticipation()`, and `closeParticipation()` guards.
- [x] 3.3 Extend `database/factories/RaffleFactory.php` with opened/participation-closed states that support the new RED scenarios.

## Phase 4: GREEN — Admin wiring and UI

- [x] 4.1 Update `app/Http/Controllers/Admin/RaffleController.php` and `routes/admin.php` with admin-guarded open/close actions, actor resolution, scoped flashes, and invalid-transition handling.
- [x] 4.2 Update `resources/views/admin/raffles/index.blade.php` to show per-row open/close forms only for eligible raffles and render participation flash slots.
- [x] 4.3 Extend `lang/es/admin-raffles.php` with participation button labels and scoped open/close success copy.

## Phase 5: REFACTOR / Verification

- [x] 5.1 Run targeted `bin/test tests/Feature/Raffles/RaffleLifecycleTest.php tests/Feature/Raffles/AdminRaffleParticipationLifecycleTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php` after GREEN passes.
- [x] 5.2 Run full `bin/test`, then review implementation against `openspec/changes/admin-raffle-participation-lifecycle/specs/*/spec.md` and `design.md` before PR prep.
