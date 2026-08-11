# Tasks: Admin Raffle Title V2

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | Product: 180–260; OpenSpec evidence: 330–390; complete candidate: 510–650 |
| 400-line budget risk | Medium |
| Chained PRs recommended | No |
| Suggested split | One PR; four work-unit commits |
| Delivery strategy | exception-ok |
| Chain strategy | size-exception |

Accepted `size:exception` keeps evidence and product changes together.

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: size-exception
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|---|---|---|---|---|---|
| 1 | Admin index identity | Single PR | `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php` | Same HTTP test | Index view/test/labels |
| 2 | Registrations identity | Single PR | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Same HTML/JSON test | Registrations view/test/labels |
| 3 | Public catalog identity | Single PR | `bin/test tests/Feature/Raffles/PublicRaffleCatalogTest.php` | Same HTTP test | Home view/test/label |
| 4 | Public detail identity | Single PR | `bin/test tests/Feature/Raffles/PublicRaffleDetailTest.php` | Same HTTP test | Detail view/test/label |

## Phase 1: Admin Index — RED/GREEN/REFACTOR

- [x] 1.1 RED: Extend `AdminRaffleIndexTest.php` for title/ID, escaping, backfill, sparse dates, and retained order/routes/actions/counts; record focused failure.
- [x] 1.2 GREEN/REFACTOR: Update `index.blade.php` and admin labels only; simplify markup and record focused green.

## Phase 2: Admin Registrations — RED/GREEN/REFACTOR

- [x] 2.1 RED: Extend `AdminRaffleRegistrationsTest.php` for escaped title/ID/backfill and retained auth, ordering, actions, pagination, counts, and exact JSON; record failure.
- [x] 2.2 GREEN/REFACTOR: Update `registrations.blade.php` and admin labels; normalize the heading, record green, and preserve snapshot markup.

## Phase 3: Public Catalog — RED/GREEN/REFACTOR

- [x] 3.1 RED: Extend `PublicRaffleCatalogTest.php` for title/ID, escaping, backfill, visibility, descending-ID order, and numeric links; record failure.
- [x] 3.2 GREEN/REFACTOR: Update `public/home.blade.php` and its ID label; polish markup and record focused green.

## Phase 4: Public Detail — RED/GREEN/REFACTOR

- [x] 4.1 RED: Extend `PublicRaffleDetailTest.php` for title `h1`, separate ID/status, escaping, backfill, routing, visibility, dates, lifecycle, and eligibility; replace only the obsolete assertion and record failure.
- [x] 4.2 GREEN/REFACTOR: Update `public/raffles/show.blade.php`, reuse the ID label, polish markup, and record focused green.

## Phase 5: Apply Readiness Evidence

- [x] 5.1 Run the four test paths together, then full `bin/test`; record commands, exits, totals, failures, and skips.
- [x] 5.2 Finish source-mutating normalization; then run check-only Pint `--test` and `git diff --check`. If bytes changed, repeat focused/full tests before freezing apply evidence.
- [x] 5.3 Record `apply-progress.md` with a Git-status-discovered manifest reconciled to scope: exploration, proposal, specs, design, tasks, apply-progress, implementation, translations, and tests. Apply is complete and green; native review is next.

## Post-Apply Review Boundary (Outside Apply Completion)

After source-mutating normalization and current apply evidence are frozen, rediscover every current Git-status path and reconcile it to scope. Before review START, stage one all-or-none candidate containing all paths actually present, including exploration, proposal, specs, design, tasks, apply-progress, product, translations, and tests; `verify-report.md` is not a START prerequisite. Independent `sdd-verify` runs only after terminal native review as directed by the dispatcher, and its report is later evidence. Native dispatcher/gates own subsequent routing.
