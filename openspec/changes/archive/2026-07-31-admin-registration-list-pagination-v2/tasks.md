# Tasks: Resilient Admin Registration List Pagination v2

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 1,250–1,700 authored: code/tests/config/localization/SDD; generated `package-lock.json` excluded from count but included in scope/identity |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 server contract/fallback → PR 2 navigation/hydration → PR 3 actions/recovery |
| Delivery strategy | ask-on-risk |
| Chain strategy | feature-branch-chain |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

Guard resolved; apply may begin only with Unit 1 on `feat/admin-registration-list-pagination-v2-server`.

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary | Start state | Finish state |
|------|------|-----------|----------------------|-----------------|-------------------|-------------|--------------|
| 1 | Server contract/fallback | `feat/admin-registration-list-pagination-v2-server` → base tracker `feat/admin-registration-list-pagination-v2` | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php --filter='pagination|snapshot|identity'` | Same; HTML/JSON GET + fallback POST | Resource, controller, routes, Blade, copy, PHP tests | Unpaginated Blade | Canonical no-JS snapshots |
| 2 | Hydration/navigation | `feat/admin-registration-list-pagination-v2-navigation` → base Unit 1 `feat/admin-registration-list-pagination-v2-server` | `bin/test --js --grep='hydration|navigation'` | Same; jsdom click/popstate races | JS tooling, validator, Vue, mount/tests | Unit 1 DOM | Atomic navigation |
| 3 | Actions/recovery | `feat/admin-registration-list-pagination-v2-actions` → base Unit 2 `feat/admin-registration-list-pagination-v2-navigation` | `bin/test --js --grep='mutation|reconciliation|expiry'` | Same; jsdom POST/409/loss/401/419 | Controller/resource/Vue/tests/delta | Unit 2 idle | Bounded terminal recovery |

## Work Unit 1: Server Contract and Fallback

- [x] 1.1 **RED:** Extend `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` for 25-row descending boundaries, whole-raffle counts, canonical pages, shared HTML/JSON snapshots, fields/actions, CSRF, and fallback.
- [x] 1.2 **RED:** In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` prove: Numeric raffle/registration IDs bind only within the raffle; malformed, absent, or cross-raffle identities fail 404 without mutation/snapshot. Pest covers every action symmetrically.
- [x] 1.3 **GREEN:** Create `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php`; update `app/Http/Controllers/Admin/RaffleController.php` and `routes/admin.php` for canonical GET negotiation and raffle-scoped transitions.
- [x] 1.4 **GREEN:** Update `resources/views/admin/raffles/registrations.blade.php` and `lang/es/admin-raffles.php` with authoritative fallback rows, counts, pagination, canonical `page`, localized feedback, and XSS-safe snapshot JSON.
- [x] 1.5 **REFACTOR:** Consolidate `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php`/`tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` fixtures; rerun Unit 1 preserving 404/visibility assertions.

## Work Unit 2: Hydration and Navigation

- [x] 2.1 **RED:** Add Vue/Vitest/jsdom to `package.json`, generated `package-lock.json`, `vite.config.js`, and `bin/test` (`--js --grep`); add failing schema/hydration cases to `resources/js/admin/raffle-registrations/RaffleRegistrations.test.js`.
- [x] 2.2 **RED:** In `resources/js/admin/raffle-registrations/RaffleRegistrations.test.js`, cover latest/aborted GETs, push/replace/popstate, latest deferral, focus/live announcements, and navigation 401/419.
- [x] 2.3 **GREEN:** Create `resources/js/admin/raffle-registrations/snapshot.js` and `resources/js/admin/raffle-registrations/RaffleRegistrations.vue`; update `resources/js/app.js` and `resources/views/admin/raffles/registrations.blade.php` for validated atomic navigation.
- [x] 2.4 **REFACTOR:** Centralize guards in `resources/js/admin/raffle-registrations/RaffleRegistrations.vue`; rerun Unit 2’s focused command.

## Work Unit 3: Actions and Recovery

- [x] 3.1 **RED:** Extend `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` for locked negotiated POST 200/409 snapshots, canonical pages, malformed payloads, and mutation 401/419.
- [x] 3.2 **RED:** Extend `resources/js/admin/raffle-registrations/RaffleRegistrations.test.js` for non-optimistic actions, exact POST/GET counts, reconciliation/retry, deferred popstate, and terminal expiry.
- [x] 3.3 **GREEN:** Update `app/Http/Controllers/Admin/RaffleController.php`, `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php`, `resources/js/admin/raffle-registrations/RaffleRegistrations.vue`, and `lang/es/admin-raffles.php` for authoritative recovery states.
- [x] 3.4 **REFACTOR:** Keep `openspec/changes/admin-registration-list-pagination-v2/specs/realtime-update-candidate-map/spec.md` as the Candidate request-response delta; add no polling, events, broadcasting, listeners, or runtime transport.
- [x] 3.5 **REFACTOR:** Simplify `app/Http/Controllers/Admin/RaffleController.php`/`resources/js/admin/raffle-registrations/RaffleRegistrations.vue` transitions; rerun Unit 3 preserving counts and confirmed data.
