# Apply Progress: Raffle Close Verification Blockers

## Status

- Change: `raffle-close-verification-blockers`
- Mode: Strict TDD (`strict_tdd: true`, canonical runner `bin/test`)
- Apply tasks: **10/10 complete**
- Runtime work unit: complete and ready for parent-owned review
- Delivery boundary: later PR1 contains runtime/tests and targets `main`; later PR2 contains archive/stable specs and targets updated `main`. The approved `size:exception` applies only to PR2.

## Changed Symbols

| File | Symbol / change |
|---|---|
| `app/Http/Controllers/Admin/RaffleController.php` | `RaffleController::publish()` now uses `DB::transaction()`, fresh key lookup, `lockForUpdate()`, and domain `publish()` on the locked model. |
| `tests/Feature/Raffles/AdminRafflePublicationTest.php` | Added `publicationBusinessSnapshot()` and stale draft-bound publish regression coverage. |
| `tests/Feature/Raffles/AdminRaffleCloseTest.php` | Added publish to the lock-before-update SQL listener matrix. |
| `openspec/changes/raffle-close-verification-blockers/tasks.md` | Marked all and only the 10 apply-completable tasks complete. |
| `openspec/changes/raffle-close-verification-blockers/apply-progress.md` | Created cumulative apply evidence. |

No route, model, schema, UI, translation, service, factory, public controller, proposal, exploration, delta spec, design, stable spec, issue, branch, Gentle AI state, or lifecycle-gate artifact was modified.

## Command Evidence

All database tests ran sequentially with `COMPOSE_PROJECT_NAME=raffles`; no parallel execution was used. Output hashes were not emitted or captured by the runner.

| Phase | Exact command | Exit | Result |
|---|---|---:|---|
| Safety net | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 0 | 17 passed, 97 assertions. |
| RED refinement 1 | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 1 | 2 failed, 17 passed, 100 assertions. Stale publish produced no error bag because current code took the success path; publish lock query was absent. |
| RED refinement 2 | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 1 | 2 failed, 17 passed, 101 assertions. Stale publish incorrectly set the success flash; publish lock query remained absent. |
| Authoritative RED | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 1 | 2 failed, 17 passed, 101 assertions. Closed snapshot expected `status=closed` but actual was `status=published`; SQL listener found no raffle `FOR UPDATE` for publish. No unrelated failure occurred. |
| GREEN | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 0 | 19 passed, 107 assertions. |
| Model lifecycle | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/RaffleLifecycleTest.php` | 0 | 28 passed, 77 assertions. |
| REFACTOR regression | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 0 | 19 passed, 107 assertions. |
| Final focused | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 0 | 19 passed, 107 assertions. |
| Full suite | `COMPOSE_PROJECT_NAME=raffles bin/test` | 0 | 191 passed, 969 assertions. |
| Whitespace | `git diff --check` | 0 | No output; no whitespace errors. |

## RED Proof

The authoritative RED run proved both intended blockers before any production edit:

1. The route-bound object retained `draft` while a fresh instance published and closed the row. Current `publish()` saved the stale object and changed the committed snapshot from expected `closed` to actual `published`, demonstrating that closure could be reopened.
2. The SQL listener found the raffle update but `search()` returned `false` for a raffle-table `SELECT ... FOR UPDATE` in the publish dataset, demonstrating the absent publish lock.

The stale regression also requires existing invalid-transition feedback, no publish-success flash, unchanged availability fields, participation audit, and registration count, plus public detail `404`. All passed after GREEN.

## TDD Cycle Evidence

| Task | Test file / layer | Safety net | RED | GREEN | TRIANGULATE | REFACTOR |
|---|---|---|---|---|---|---|
| 1.1 | `AdminRafflePublicationTest.php` / feature integration | 17/17 focused baseline | Stale closed snapshot test written first; failed with `closed` versus `published`. | Passed in 19/19 focused run. | Existing draft, guest, current non-draft, public visibility, and participation-preservation cases exercise distinct paths. | Snapshot helper keeps business-state assertions explicit. |
| 1.2 | `AdminRaffleCloseTest.php` / SQL integration | 17/17 focused baseline | Publish dataset written first; failed because lock index was `false`. | Passed with lock index before update index. | Existing close/open/participation-close datasets retain three other mutation paths. | Existing listener helper structure retained. |
| 1.3 | Both feature files / integration | 17/17 focused baseline | Authoritative run: 2 intended failures, no unrelated failures. | Covered by subsequent 19/19 GREEN. | Both independent blockers failed in one sequential matrix. | Assertion ordering refined only to expose the exact stale overwrite. |
| 2.1 | Both feature files / integration | RED established before edit | Production edit prohibited until authoritative RED completed. | Minimal controller-only transaction/fresh-lock implementation passed. | Draft success and committed-closed rejection prove both domain branches. | No production refactor beyond established controller pattern. |
| 2.2 | Both feature files / integration | 17/17 baseline | Two new cases were RED. | 19 passed, 107 assertions. | Draft, guest, non-draft, stale-close/public-404, and SQL-order paths all exercised. | No further change required. |
| 2.3 | `RaffleLifecycleTest.php` / model integration | Existing suite | New controller behavior did not alter model API. | 28 passed, 77 assertions. | Draft success plus published/closed rejection and unsaved rejection remain covered. | Model unchanged. |
| 3.1 | Touched feature tests / integration | Focused GREEN retained | N/A: test-only cleanup after GREEN. | Helper-based snapshot remained green. | Snapshot includes status, availability, four audit fields, and registration count. | Only useful helper extraction retained; explicit SQL assertions unchanged. |
| 3.2 | Both feature files / integration | Prior 19/19 GREEN | N/A: evidence rerun task. | 19 passed, 107 assertions. | Both deterministic evidence mechanisms rerun together. | No additional refactor needed. |
| 4.1 | Full test suite / integration | Focused and model suites green | N/A: verification command task. | 191 passed, 969 assertions; diff check clean. | Full repository suite covers unrelated boundaries. | N/A: structural execution task. |
| 4.2 | Delta/stable specs / read-only structural review | Stable specs read without edits | N/A: documentation preflight. | All six complete `MODIFIED` replacements are archive-safe. | Every retained stable scenario was compared, not only changed text. | N/A: read-only structural task. |

## Work Unit Evidence

| Evidence | Result |
|---|---|
| Focused test | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` — exit 0, 19 passed, 107 assertions. |
| Runtime harness | Feature-level stale-bound controller publish after a fresh instance publishes and closes the same row — rejection, unchanged closed snapshot, and public detail 404 all passed. |
| Rollback boundary | Revert `RaffleController::publish()` plus the two feature-test changes. No model, route, schema, UI, translation, service, factory, or public-controller rollback is required. Before archive, corrective OpenSpec change artifacts can be removed together without touching stable specs. |

## Archive-Safety Preflight

Result: **PASS (read-only)**. Stable specs were not modified.

| Delta capability / complete `MODIFIED` requirement | Comparison result |
|---|---|
| `admin-raffle-publication-management` / Admins publish draft raffles only | Preserves all 3 stable scenarios and adds committed-state rejection and deterministic SQL-order evidence. The separate stable “Publishing changes public visibility only” requirement remains untouched. |
| `raffle-lifecycle` / Publish from draft only | Preserves stable draft success and closed rejection, strengthens fresh committed-state semantics, and adds stale-draft rejection. |
| `raffle-lifecycle` / Close from published only | Preserves both stable scenarios and fully carries published-only close, irreversible close, atomic participation closure, duplicate rejection, rollback, and conceptual future-draw-only contracts. |
| `admin-raffle-list` / Minimal persisted raffle rows are visible | Preserves both stable row/sparse-value scenarios while removing only the contradictory read-only prohibition and deferring controls to dedicated requirements. |
| `admin-raffle-list` / Explicit empty state without broader admin restructuring | Preserves both stable empty/narrow-scope scenarios while removing only the contradiction with dedicated row actions. |
| `realtime-update-candidate-map` / Delivered observable changes are mapped | Preserves all 4 stable scenarios and all table rows, narrows close to authenticated-admin overall closure, and adds a documentation-only close scenario with explicit no-runtime semantics. Other stable realtime requirements remain untouched. |

The `raffle-participation-lifecycle` delta is `ADDED`, not `MODIFIED`; it carries active-close audit, prior-audit preservation, never-opened nulls, stale submission rejection, registration preservation, and atomic rollback contracts without replacing stable requirements.

## Evidence Limitation

The stale-object regression and SQL listener ordering are deterministic protocol evidence. They are **not true concurrency stress** and do not prove simultaneous-session lock waiting or scheduler interleavings.

## Scope and Deviations

- Scope deviations: none.
- Production change remained limited to `RaffleController::publish()`.
- Unexpected exceptions remain observable because only `InvalidRaffleTransition` is caught.
- The route-bound model supplies identity only and is never saved.
- No archive, independent verify, review/refuter/Judgment Day, commit, stage, push, PR, issue, branch/worktree, stable-spec, or Gentle AI state action was performed.

## Task Matrix

| Task | Status |
|---|---|
| 1.1 | Complete |
| 1.2 | Complete |
| 1.3 | Complete |
| 2.1 | Complete |
| 2.2 | Complete |
| 2.3 | Complete |
| 3.1 | Complete |
| 3.2 | Complete |
| 4.1 | Complete |
| 4.2 | Complete |

## Next Boundary

Return to the parent for bounded post-apply review. Independent `sdd-verify` and archive remain parent-owned and must not begin before the required review receipt exists.
