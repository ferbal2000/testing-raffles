```yaml
schema: gentle-ai.verify-result/v1
evidence_revision: sha256:853bc461820264b59f700aca2c5b94938c678bfb195884b8ef95e8e41a07d932
verdict: pass
blockers: 0
critical_findings: 0
requirements: 4/4
scenarios: 17/17
test_command: FORWARD_DB_PORT=55432 bin/test
test_exit_code: 0
test_output_hash: sha256:b157f3e8260aade89eb46d2cf6db5b02962b28553b2a08ce5abf82500cd5c2fc
build_command: bin/composer exec pint -- --test lang/es/admin-raffles.php lang/es/public-raffles.php tests/Feature/Raffles/AdminRaffleCloseTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/PublicRaffleCatalogTest.php tests/Feature/Raffles/PublicRaffleDetailTest.php && git diff --cached --check
build_exit_code: 0
build_output_hash: sha256:2016f65a3eaacf716def1b5c31643fad4be7a282087eb6bc3c27936262781063
```

## Verification Report

**Change**: `admin-raffle-title-v2`
**Version**: N/A
**Mode**: Strict TDD
**Authority**: Native acquire `state: proceed`; work unit `independent-final-verify`; token `sha256:d066aca404ef63f0e9c851edadfeaf36275a9561f658d0eb46f42f113584341a`
**Candidate**: 20 staged paths, 627 insertions, 23 deletions, 650 changed lines; no unstaged or untracked paths before this report was written.

### Completeness

| Metric | Value |
|---|---:|
| Requirements total / complete | 4 / 4 |
| Scenarios total / compliant | 17 / 17 |
| Tasks total / complete / incomplete | 11 / 11 / 0 |
| Staged candidate paths | 20 |
| Staged changed lines | 650 |

### Build and Test Execution

| Check | Command | Exit | Result | Output SHA256 |
|---|---|---:|---|---|
| Focused feature tests | `FORWARD_DB_PORT=55432 bin/test tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/PublicRaffleCatalogTest.php tests/Feature/Raffles/PublicRaffleDetailTest.php` | 0 | 101 passed, 540 assertions, 0 failed, 0 skipped | `sha256:403e38bda0f5ecf1c69a3d86a83eaf3e0628a917b8781801285bd702a60ca59a` |
| Full suite | `FORWARD_DB_PORT=55432 bin/test` | 0 | 245 passed, 1296 assertions, 0 failed, 0 skipped | `sha256:b157f3e8260aade89eb46d2cf6db5b02962b28553b2a08ce5abf82500cd5c2fc` |
| Pint plus staged whitespace check | `bin/composer exec pint -- --test lang/es/admin-raffles.php lang/es/public-raffles.php tests/Feature/Raffles/AdminRaffleCloseTest.php tests/Feature/Raffles/AdminRaffleIndexTest.php tests/Feature/Raffles/AdminRaffleRegistrationsTest.php tests/Feature/Raffles/PublicRaffleCatalogTest.php tests/Feature/Raffles/PublicRaffleDetailTest.php && git diff --cached --check` | 0 | Pint passed for 7 changed PHP files; staged diff check produced no output | `sha256:2016f65a3eaacf716def1b5c31643fad4be7a282087eb6bc3c27936262781063` |

The first focused invocation without `FORWARD_DB_PORT` exited 1 before Pest started because local port `5432` was already occupied. The authoritative rerun used the compose-supported host-port override `FORWARD_DB_PORT=55432`; application and test-database settings were unchanged. Its failed pre-test output hash is `sha256:4c5ba44c33e7a3f1dccc34bae23b9a221983233eaaf4564714fb99ce384469c4`.

**Coverage**: Skipped — no coverage capability artifact or coverage driver was detected. This is informational under Strict TDD verification.

### Spec Compliance Matrix

| Requirement | Scenario | Runtime coverage | Result |
|---|---|---|---|
| Admin raffle list | Persisted raffles appear with title and ID | `AdminRaffleIndexTest.php` — primary title/secondary ID plus retained routes, lifecycle actions, counts, and order | ✅ COMPLIANT |
| Admin raffle list | Sparse raffle values still render safely | `AdminRaffleIndexTest.php` — nullable availability placeholders retain identity | ✅ COMPLIANT |
| Admin raffle list | Authored markup is not interpreted | `AdminRaffleIndexTest.php` — escaped script-like title and raw-markup rejection | ✅ COMPLIANT |
| Admin raffle list | Backfilled title does not replace the ID reference | `AdminRaffleIndexTest.php` — `Sorteo #42` and separate ID cell | ✅ COMPLIANT |
| Admin participation list | Authenticated admin opens a raffle registration list | `AdminRaffleRegistrationsTest.php` — identity, newest-first rows, actions, pagination, and counts | ✅ COMPLIANT |
| Admin participation list | Guest requests a raffle registration list | `AdminRaffleRegistrationsTest.php` — HTML redirect and JSON 401 | ✅ COMPLIANT |
| Admin participation list | Current raffle title is escaped | `AdminRaffleRegistrationsTest.php` — escaped heading and raw-markup rejection | ✅ COMPLIANT |
| Admin participation list | Backfilled identity keeps both values visible | `AdminRaffleRegistrationsTest.php` — `Sorteo #42` plus separate numeric reference | ✅ COMPLIANT |
| Admin participation list | Registration JSON remains unchanged | `AdminRaffleRegistrationsTest.php` — authoritative snapshots, exact fields/semantics, pagination, mutation, and XSS-safe JSON | ✅ COMPLIANT |
| Public raffle catalog | Published raffles are listed with title and ID | `PublicRaffleCatalogTest.php` — published title, separate ID, numeric detail link | ✅ COMPLIANT |
| Public raffle catalog | Non-visible raffles stay hidden | `PublicRaffleCatalogTest.php` and `AdminRaffleCloseTest.php` — draft/closed exclusion and close-flow removal | ✅ COMPLIANT |
| Public raffle catalog | Authored markup is not interpreted | `PublicRaffleCatalogTest.php` — escaped script-like title and raw-markup rejection | ✅ COMPLIANT |
| Public raffle catalog | Backfilled identity preserves routing and ordering | `PublicRaffleCatalogTest.php` — separate ID, numeric link, descending-ID order | ✅ COMPLIANT |
| Public raffle detail | Friendly status copy is shown separately | `PublicRaffleDetailTest.php` — translated lifecycle text separate from title and ID | ✅ COMPLIANT |
| Public raffle detail | Availability dates remain informational | `PublicRaffleDetailTest.php` — dates do not control lifecycle or eligibility messaging | ✅ COMPLIANT |
| Public raffle detail | Authored markup is not interpreted | `PublicRaffleDetailTest.php` — escaped title inside `h1` and raw-markup rejection | ✅ COMPLIANT |
| Public raffle detail | Backfilled identity preserves public behavior | `PublicRaffleDetailTest.php` plus full suite — title, ID, lifecycle, numeric route, visibility, and eligibility | ✅ COMPLIANT |

**Compliance summary**: 4/4 requirements and 17/17 scenarios are compliant through passing HTTP integration tests.

### Correctness and Compatibility

| Contract | Status | Evidence |
|---|---|---|
| Title-primary and ID-secondary on all four surfaces | ✅ Implemented | Four existing Blade views use escaped `{{ $raffle->title }}` and distinct numeric-ID elements. |
| Escaping and authored-markup safety | ✅ Preserved | Four malicious-title feature cases passed; Blade escaped interpolation is used. |
| Backfilled-title behavior | ✅ Preserved | All four surfaces pass `Sorteo #42` plus separate `42` assertions. |
| Separate public lifecycle status | ✅ Preserved | Detail status remains in its own translated definition-list field. |
| Numeric routes and route resolution | ✅ Unchanged | No route/controller files changed; focused and full routing assertions passed. |
| Lifecycle and visibility | ✅ Unchanged | Index action boundaries, public draft/closed exclusion, detail 404 behavior, and close-flow removal passed. |
| Participation eligibility | ✅ Unchanged | Open/closed participation form and registration-flow assertions passed. |
| Ordering | ✅ Unchanged | Admin newest-first rows and public descending-ID catalog assertions passed. |
| Counts and actions | ✅ Unchanged | Admin raffle counts, registration summaries, bounded status actions, and pagination passed. |
| Registration JSON | ✅ Unchanged | Snapshot fields, counts, pagination, canonicalization, mutation responses, and XSS-safe encoding passed. |

### Design Coherence

| Decision | Followed? | Notes |
|---|---|---|
| Keep changes at the Blade presentation boundary | ✅ Yes | Product edits are limited to four Blade views and two translation files. |
| Keep title, ID, and detail lifecycle status separate | ✅ Yes | Separate semantic elements remain visible, including backfilled titles. |
| Use narrowly scoped Spanish labels | ✅ Yes | Only title/identity labels changed. |
| Prove compatibility through focused HTTP tests | ✅ Yes | Four focused paths passed, followed by the complete suite. |
| Avoid controller, model, schema, route, JSON-resource, JavaScript, and shared-component changes | ✅ Yes | No such paths are present in the staged candidate. |

### TDD Compliance

| Check | Result | Details |
|---|---|---|
| TDD evidence reported | ✅ | `apply-progress.md` contains task-level safety-net, RED, GREEN, triangulation, and refactor evidence. |
| All implementation tasks have tests | ✅ | 8/8 RED/GREEN surface tasks map to existing focused test files; all 11/11 planned tasks are complete. |
| RED confirmed | ✅ | 4/4 reported focused test files exist; task evidence records failing pre-implementation runs. |
| GREEN confirmed | ✅ | 4/4 focused files pass now: 101 tests and 540 assertions. |
| Triangulation adequate | ✅ | Each surface covers authored, escaped, backfilled, and compatibility variants. |
| Safety net for modified files | ✅ | 4/4 surface cycles report passing baselines before modification; the compatibility correction was found by the full suite and rerun. |

**TDD compliance**: 6/6 checks passed.

### Test Layer Distribution

| Layer | Tests | Files | Tools |
|---|---:|---:|---|
| Unit | 0 | 0 | Pest |
| Integration | 114 | 5 | Pest/Laravel HTTP and database test harness |
| E2E | 0 | 0 | Not installed for this change |
| **Total changed-file tests** | **114** | **5** | |

The four primary focused files account for 101 tests; `AdminRaffleCloseTest.php` contributes 13 compatibility tests in the full suite.

### Changed File Coverage

Coverage analysis skipped — no coverage tool was detected.

### Assertion Quality

All five changed test files were inspected. No tautologies, assertions detached from production execution, ghost loops, orphan empty-only checks, type-only assertions, smoke-only assertions, CSS-class coupling, or mock-heavy files were found.

**Assertion quality**: ✅ All assertions verify real behavior.

### Quality Metrics

**Linter**: ✅ Pint check-only passed for all 7 changed PHP files.
**Type checker**: ➖ No separate type-check capability detected.
**Whitespace**: ✅ `git diff --cached --check` passed with no output.
**Scope hygiene before report**: ✅ Exactly 20 staged candidate paths; no unstaged or untracked paths.

### Findings

**CRITICAL**: None.
**WARNING**: None.
**SUGGESTION**: None.

### Evidence Revision

The evidence revision is the SHA256 of the following exact canonical UTF-8 bytes with no trailing newline:

```json
{"build_exit_code":0,"build_output_hash":"sha256:2016f65a3eaacf716def1b5c31643fad4be7a282087eb6bc3c27936262781063","candidate_diff_hash":"sha256:1694a8f42b7f0ca0ac4ae41377234a9c766261fead0677c11c641aec912b75fa","focused_exit_code":0,"focused_output_hash":"sha256:403e38bda0f5ecf1c69a3d86a83eaf3e0628a917b8781801285bd702a60ca59a","full_exit_code":0,"full_output_hash":"sha256:b157f3e8260aade89eb46d2cf6db5b02962b28553b2a08ce5abf82500cd5c2fc","requirements":"4/4","scenarios":"17/17"}
```

**Evidence revision**: `sha256:853bc461820264b59f700aca2c5b94938c678bfb195884b8ef95e8e41a07d932`
**Staged candidate diff hash**: `sha256:1694a8f42b7f0ca0ac4ae41377234a9c766261fead0677c11c641aec912b75fa`

### Verdict

**PASS** — all 4 requirements and all 17 scenarios are covered by passing runtime tests; Strict TDD evidence, staged scope, design coherence, compatibility behavior, and check-only quality gates are green.
