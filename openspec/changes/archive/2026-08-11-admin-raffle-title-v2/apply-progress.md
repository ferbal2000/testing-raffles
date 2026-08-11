# Apply Progress: Admin Raffle Title V2
**Status**: 11/11 tasks complete; Strict TDD; `size:exception`; apply is green; native dispatcher `nextRecommended: review`; no staging performed.
**Native authority**: `presentation-identity-v2`, acquire `state: proceed`, token `sha256:a08da426905bb2a794fc092075d372df40ef70035f11bd67f4250d21c6aaa5d7`; evidence revision `sha256:0786e4199fd98b71a200ab3e1df81feb6c441f6091813d5619222ec4577a33ce`.
**Normalization**: source-mutating Pint ran before final verification; the initial six-file pass changed no bytes, then the lifecycle regression correction was normalized and Pint fixed one `fully_qualified_strict_types` style issue; all tests were repeated afterward.
## TDD Cycle Evidence
| Task | Test file/layer | Safety net | RED | GREEN | TRIANGULATE | REFACTOR |
|---|---|---|---|---|---|---|
| 1.1 | `AdminRaffleIndexTest.php` HTTP integration | 23 passed | exit 1: 1 failed, 23 passed | 24 passed, 116 assertions | authored markup + backfill | N/A; direct markup clean |
| 1.2 | `AdminRaffleIndexTest.php` HTTP integration | 23 passed | task 1.1 RED | exit 0 | retained sparse/order/routes/actions/counts | green after minimal Blade/label change |
| 2.1 | `AdminRaffleRegistrationsTest.php` HTTP/JSON integration | 64 passed | exit 1: 3 failed, 63 passed | 66 passed, 325 assertions | authored markup + backfill | N/A; snapshot markup untouched |
| 2.2 | `AdminRaffleRegistrationsTest.php` HTTP/JSON integration | 64 passed | task 2.1 RED | exit 0 | auth/order/actions/pagination/counts/JSON retained | heading normalized while green |
| 3.1 | `PublicRaffleCatalogTest.php` HTTP integration | 3 passed | exit 1: 4 failed, 1 passed | 5 passed, 35 assertions | authored markup + backfill + multiple IDs | visibility/order/numeric links retained |
| 3.2 | `PublicRaffleCatalogTest.php` HTTP integration | 3 passed | task 3.1 RED | exit 0 | visible/hidden and newest/older paths | N/A; direct markup clean |
| 4.1 | `PublicRaffleDetailTest.php` HTTP integration | 4 passed | exit 1: 4 failed, 2 passed | 6 passed, 64 assertions | authored markup + backfill + open/closed eligibility | obsolete ID-hidden assertion replaced |
| 4.2 | `PublicRaffleDetailTest.php` HTTP integration | 4 passed | task 4.1 RED | exit 0 | separate title/ID/status paths | N/A; reused public ID label |
| 5.1 | Four focused paths + full suite | N/A; evidence task | N/A | final focused/full exit 0 | focused plus whole-suite compatibility | lifecycle expectation corrected, then repeated |
| 5.2 | Pint and Git whitespace checks | N/A; quality task | N/A | both exit 0 | mutating then check-only passes | one style issue normalized before final tests |
| 5.3 | Git-status manifest | N/A; artifact task | N/A | 20 paths reconciled | expected verify artifact checked separately | no staging |
## Execution Evidence
- Baseline: `bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/PublicRaffleCatalogTest.php tests/Feature/Raffles/PublicRaffleDetailTest.php` → exit 0, 94 passed, 490 assertions.
- RED commands: `bin/test <each focused path>` → index exit 1 (1 failed/23 passed/107 assertions); registrations exit 1 (3 failed/63 passed/313); catalog exit 1 (4 failed/1 passed/13); detail exit 1 (4 failed/2 passed/42).
- Initial GREEN commands: same per-file commands → index 24/116, registrations 66/325, catalog 5/35, detail 6/64; all exit 0.
- Full-suite compatibility exposed obsolete catalog copy in `AdminRaffleCloseTest.php`: focused four had 101/540, then full exit 1 with 1 failed/244 passed/1289; correcting only that presentation assertion and running `bin/test tests/Feature/Raffles/AdminRaffleCloseTest.php` yielded exit 0, 13/81.
- Final: four focused paths together → exit 0, 101 passed, 540 assertions, 0 failures/skips; full `bin/test` → exit 0, 245 passed, 1296 assertions, 0 failures/skips.
- Quality: `bin/composer exec pint -- --test` on 7 changed PHP files → exit 0, 7 files; `git diff --check` → exit 0, no output.
## Work Unit Evidence
- Admin index: focused/runtime `AdminRaffleIndexTest.php` 24/116; rollback index view, index labels, and index identity tests.
- Registrations: focused/runtime `AdminRaffleRegistrationsTest.php` 66/325; rollback registrations view/labels/tests without changing JSON snapshot code.
- Public catalog: focused/runtime `PublicRaffleCatalogTest.php` 5/35; rollback home view, public ID label, catalog tests, and lifecycle copy assertion.
- Public detail: focused/runtime `PublicRaffleDetailTest.php` 6/64; rollback detail view/detail tests while retaining lifecycle and eligibility behavior.
## Git-Status-Discovered Manifest
- SDD: `exploration.md`, `proposal.md`, four `specs/*/spec.md`, `design.md`, `tasks.md`, `apply-progress.md` under `openspec/changes/admin-raffle-title-v2/`.
- Presentation/translations: `lang/es/{admin-raffles,public-raffles}.php`; `resources/views/admin/raffles/{index,registrations}.blade.php`; `resources/views/public/home.blade.php`; `resources/views/public/raffles/show.blade.php`.
- Tests: `AdminRaffleCloseTest.php`, `AdminRaffleIndexTest.php`, `AdminRaffleRegistrationsTest.php`, `PublicRaffleCatalogTest.php`, `PublicRaffleDetailTest.php` under `tests/Feature/Raffles/`.
- After source-mutating normalization and this apply evidence are frozen, stage every current Git-status-discovered path all-or-none before review START, including all listed SDD, product, translation, and test paths actually present. `verify-report.md` is later independent-verification evidence, not a review START prerequisite.
**Changed-line count**: 650 authored additions plus deletions across 20 paths (includes 37-line apply progress); native maximum 650 exactly.
**Deviations/risks**: No production-design deviation; one lifecycle compatibility test outside the original four-file table required copy-only maintenance after the full suite proved its synthesized-title assertion obsolete. Independent `sdd-verify` follows terminal native review only when directed by the dispatcher; native dispatcher/gates own subsequent routing.
