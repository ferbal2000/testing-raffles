# Tasks: Resolve Raffle Close Verification Blockers

## Review Workload Forecast

| Area | Estimated changed lines |
|---|---:|
| Authored runtime/tests | 55–90 |
| Final reviewable total | 737–902 |

Chained PRs recommended: Yes
500-line budget risk: High
400-line budget risk: High
Decision needed before apply: No
Chain strategy: stacked-to-main

Approved delivery: no single oversized PR. After apply → review → verify → archive, PR1 runtime/tests targets `main`; PR2 archive/stable specs targets updated `main` after PR1 merges. The maintainer-approved `size:exception` applies only to the unavoidable archive PR. Each PR later has exactly `type:bug`; final PR uses `Closes #52`; #47 remains open.

### Work Units

| Unit | Likely PR | Focused test | Runtime harness | Rollback boundary |
|---|---|---|---|---|
| Runtime/tests | PR1 → `main` | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | Stale-bound publish after committed close | Controller and two tests |
| Archive/specs | PR2 → updated `main` | Structural delta checks | N/A: documentation-only archive | Archive and five stable specs together |

## 1. RED

- [x] 1.1 In `tests/Feature/Raffles/AdminRafflePublicationTest.php`, add a stale-bound draft test closed through a fresh instance; assert publish error/no success, unchanged status/availability/audit/registrations, and public 404.
- [x] 1.2 In `tests/Feature/Raffles/AdminRaffleCloseTest.php`, add publish to the competing-admin-mutation dataset; require raffle `FOR UPDATE` before raffle `UPDATE`.
- [x] 1.3 Run both feature-test files sequentially; record intended RED reopening/absent-lock evidence and stop on unrelated failure.

## 2. GREEN

- [x] 2.1 Only after RED, change `app/Http/Controllers/Admin/RaffleController.php::publish()` to transact, re-fetch/lock by key, and publish the locked model; preserve feedback.
- [x] 2.2 Re-run both focused feature-test files for GREEN: draft, guest, non-draft, stale-close snapshot/public 404, and SQL order.
- [x] 2.3 Run existing model lifecycle tests for draft success/non-draft rejection; preserve model behavior.

## 3. REFACTOR

- [x] 3.1 Refactor touched test helpers only; keep assertions explicit and runtime limited to `publish()` unless RED proves another file necessary.
- [x] 3.2 Re-run focused tests; state that stale-object and SQL-order evidence is deterministic, not true concurrency stress.

## 4. Apply-completable Checks

- [x] 4.1 Run full sequential suite: `COMPOSE_PROJECT_NAME=raffles bin/test`, then `git diff --check`.
- [x] 4.2 Read-only preflight: compare every complete `MODIFIED` requirement/scenario with stable specs and prove archive safety without losing close contracts.

## Parent-owned Lifecycle Gates (not implementation tasks)

1. Parent explicitly starts bounded post-apply `review/start(target)`, manages permitted corrections, and obtains a valid target-bound receipt. `sdd-apply` MUST NOT launch review.
2. Parent runs independent `sdd-verify` only after that receipt is valid and every implementation checkbox is complete.
3. Parent runs `sdd-archive` only after verify PASS; old failed Gentle AI state remains untouched.
4. Only after archive: commit → push → PR delivery in the approved two-PR order above. `sdd-apply` MUST NOT review, verify, archive, commit, push, open PRs, or merge.
