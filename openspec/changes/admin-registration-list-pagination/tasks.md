# Tasks: Paginate the Admin Registration List

## Review Workload Forecast

| Field | Value |
|---|---|
| Authored changed lines | 950–1,400 total; generated `package-lock.json` excluded |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Feature Branch Chain Units

| Unit / base | Scope / forecast | Focused test | Runtime harness | Rollback boundary |
|---|---|---|---|---|
| 1 `feat/admin-registration-list-pagination-server`; PR #1 base `feat/admin-registration-list-pagination` | Snapshot/JSON, canonical read-only Blade, translations, Pest; 300–450 | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Log in; open `/admin/raffles/{id}/registrations?page=2`; inspect canonical read-only 25-row page | Resource, controller/view/layout/copy and matching Pest tests |
| 2 `feat/admin-registration-list-pagination-navigation`; PR #2 base `feat/admin-registration-list-pagination-server` | Vue/Vitest, pagination/history/global busy; 300–450 | `npm run test:js` | `npm run dev`; paginate, Back/Forward, keyboard-check busy/focus | Frontend dependencies/config, mount, component and navigation tests |
| 3 `feat/admin-registration-list-pagination-actions`; PR #3 base `feat/admin-registration-list-pagination-navigation` | Actions/recovery/expiry/toasts/a11y/evidence; 350–500 | `npm run test:js && bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | `npm run dev`; exercise actions, offline reconciliation, expired session, announcements | Action transport/state/tests/copy and final evidence; requires Units 1–2 |

## Phase 1 — Unit 1: Server Contract

- [x] 1.1 **RED** — In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, fail on 25/26 boundaries, newest-first non-overlap, whole counts, malformed/non-positive/high canonical HTML/JSON, and true-empty behavior ([Populated](specs/admin-raffle-participation-list/spec.md#scenario-populated-page), [Noncanonical](specs/admin-raffle-participation-list/spec.md#scenario-noncanonical-page), [Empty](specs/admin-raffle-participation-list/spec.md#scenario-raffle-is-truly-empty)).
- [x] 1.2 **RED** — Add negotiated JSON `200`, guest/session `401`, CSRF `419`, nested-scope `404`, stale/invalid `409` fresh-snapshot, and server-failure safe-response tests before controller changes ([Mutation](specs/admin-raffle-participation-list/spec.md#scenario-mutation-succeeds-or-is-rejected), [Expiry](specs/admin-raffle-participation-list/spec.md#scenario-session-expires)).
- [x] 1.3 **GREEN** — Create `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php`; update `app/Http/Controllers/Admin/RaffleController.php` for canonical `paginate(25)`, counts, negotiated snapshots, and locked transition responses.
- [x] 1.4 **GREEN** — Update `resources/views/admin/raffles/registrations.blade.php`, `resources/views/components/layouts/app.blade.php`, and `lang/es/admin-raffles.php`: no forms, XSS-safe JSON, CSRF meta, read-only rows/counts/notices ([Unavailable](specs/admin-raffle-participation-list/spec.md#scenario-interaction-unavailable)).
- [x] 1.5 **REFACTOR** — Remove duplicate mapping, keep route scope unchanged, then run Unit 1 focused test and runtime harness.

## Phase 2 — Unit 2: Navigation

- [x] 2.1 Add frontend/test foundation in `package.json`, generated `package-lock.json`, and `vite.config.js`: Vue 3, Vue plugin/Test Utils, Vitest/jsdom, and `test:js`.
- [x] 2.2 **RED** — Create `resources/js/admin/raffle-registrations/RaffleRegistrations.test.js` for real links, push/replace/pop history, late-GET rejection, pagination failure preservation/retry, global lock, range/focus/live semantics ([History](specs/admin-raffle-participation-list/spec.md#scenario-page-history), [Pending](specs/admin-raffle-participation-list/spec.md#scenario-operation-is-pending)).
- [x] 2.3 **GREEN** — Create `RaffleRegistrations.vue` and update `resources/js/app.js` for safe initial parsing/mount, canonical snapshot navigation, sequencing, `aria-busy`, disabled screen navigation, focus, and announcements.
- [x] 2.4 **REFACTOR** — Extract request/history helpers only when tests justify them; run Unit 2 focused test and runtime harness.

## Phase 3 — Unit 3: Actions and Recovery

- [ ] 3.1 **RED** — Extend component tests for native confirms and terminal cancel warning; POST-once/no optimism; `200/409`; transport/malformed/`5xx` one-GET reconciliation; failed reconciliation unresolved blocking; `401/419` login path; temporary toast, durable announcement, alert and focus ([Mutation](specs/admin-raffle-participation-list/spec.md#scenario-mutation-succeeds-or-is-rejected), [Uncertain](specs/admin-raffle-participation-list/spec.md#scenario-uncertain-outcome), [Expiry](specs/admin-raffle-participation-list/spec.md#scenario-session-expires)).
- [ ] 3.2 **GREEN** — Implement serialized action/recovery/expiry state in `RaffleRegistrations.vue`; complete controller snapshot feedback and `lang/es/admin-raffles.php` copy.
- [ ] 3.3 **REFACTOR** — Consolidate complete-snapshot commits and timers; run Unit 3 focused tests plus full `bin/test` and runtime harness.
- [ ] 3.4 Record keyboard/screen-reader limitations and prove no polling/push/events in verification/archive evidence ([No transport](specs/realtime-update-candidate-map/spec.md#scenario-no-runtime-transport-is-introduced), [Labels](specs/realtime-update-candidate-map/spec.md#scenario-labels-are-not-executable-contracts), [Same-browser](specs/realtime-update-candidate-map/spec.md#scenario-same-browser-update)).
