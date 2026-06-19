## Verification Report

**Change**: raffle-lifecycle-basic  
**Version**: N/A  
**Mode**: Strict TDD  
**Persistence mode**: Hybrid  
**OpenSpec source**: `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/verify-report.md`  
**Archive path used**: Yes. The active `openspec/changes/raffle-lifecycle-basic` folder is absent and the change is archived under `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic`, so this archived report is the correct OpenSpec source to update.

### Completeness
| Metric | Value |
|--------|-------|
| Tasks total | 11 |
| Tasks complete | 11 |
| Tasks incomplete | 0 |
| Original spec scenarios | 9/9 compliant with passing runtime coverage |
| Strengthened initial draft invariant | 3/3 runtime checks passed: default create, attempted `published` create, attempted `closed` create |
| Post-verify blocker coverage | 3/3 verified: mass-assignment initial status bypass, unsaved `publish()`, `bin/test` DB safety guard |
| Out-of-scope complexity | None found in current implementation |

### Build & Tests Execution
**Build**: Not applicable - no separate build step exists for this PHP/Laravel slice.

**Tests**: PASS - all verification commands passed or intentionally refused unsafe execution where expected.
```text
Command: ./bin/test --filter=RaffleLifecycleTest
Result: PASS - 11 passed, 15 assertions

Command: ./bin/test tests/Feature/Tooling/ContainerRuntimeTest.php
Result: PASS - 6 passed, 38 assertions

Command: DB_TEST_DATABASE=raffles ./bin/test
Result: PASS as safety verification - refused before container execution with exit code 1
Output: Refusing to run tests against the development database: TEST_DB_DATABASE == DEV_DB_DATABASE (raffles).

Command: DB_TEST_DATABASE=postgres ./bin/test
Result: PASS as safety verification - refused before container execution with exit code 1
Output: Refusing to run tests against a dangerous database name: postgres.

Command: bash -lc 'if DB_TEST_DATABASE=raffles ./bin/test >/tmp/opencode/raffles-guard-dev.out 2>&1; then exit 1; else status=$?; if [ "$status" -eq 1 ]; then exit 0; fi; exit "$status"; fi'
Result: PASS - wrapper confirmed the dev-database refusal exits 1

Command: bash -lc 'if DB_TEST_DATABASE=postgres ./bin/test >/tmp/opencode/raffles-guard-reserved.out 2>&1; then exit 1; else status=$?; if [ "$status" -eq 1 ]; then exit 0; fi; exit "$status"; fi'
Result: PASS - wrapper confirmed the reserved-name refusal exits 1

Command: ./bin/test
Result: PASS - 32 passed, 136 assertions
```

**Coverage**: Not available.
```text
Command: docker compose run --rm -T app php -r '$extensions = array_intersect(["xdebug", "pcov"], array_map("strtolower", get_loaded_extensions())); if ($extensions === []) { fwrite(STDOUT, "no coverage extension loaded\n"); exit(0); } fwrite(STDOUT, implode("\n", $extensions)."\n");'
Result: no coverage extension loaded
```

### TDD Compliance
| Check | Result | Details |
|-------|--------|---------|
| TDD Evidence reported | PASS | `apply-progress` contains a TDD Cycle Evidence table and post-verify remediation rows. |
| All tasks have tests | PASS | 11/11 planned tasks map to `tests/Feature/Raffles/RaffleLifecycleTest.php`; DB guard remediation maps to `tests/Feature/Tooling/ContainerRuntimeTest.php` plus negative command checks. |
| RED confirmed (tests exist) | PASS | `RaffleLifecycleTest.php` and `ContainerRuntimeTest.php` exist and were executed through `bin/test`. |
| GREEN confirmed (tests pass) | PASS | Focused lifecycle, focused tooling, guard-negative wrappers, and full suite all passed. |
| Triangulation adequate | PASS | Initial state is checked through default create plus attempted `published` and `closed` creation; transitions include happy paths and rejection paths. |
| Safety Net for modified files | PASS | Current focused and full reruns pass after the post-verify blocker fix. |

**TDD Compliance**: 6/6 checks passed

---

### Test Layer Distribution
| Layer | Tests | Files | Tools |
|-------|-------|-------|-------|
| Unit | 0 | 0 | not used |
| Integration | 17 | 2 | Pest via `bin/test` |
| E2E | 0 | 0 | not installed / not used |
| **Total** | **17** | **2** | |

---

### Changed File Coverage
Coverage analysis skipped - no coverage extension detected in the PHP container.

---

### Assertion Quality
| File | Line | Assertion | Issue | Severity |
|------|------|-----------|-------|----------|
| `tests/Feature/Raffles/RaffleLifecycleTest.php` | 20-21 | `toBeNull()` for `starts_at` / `ends_at` | Acceptable in context: paired with production `Raffle::query()->create([])` and status value assertion; verifies default persisted nullable fields. | None |
| `tests/Feature/Tooling/ContainerRuntimeTest.php` | 22-25 | Loop over wrapper scripts | Acceptable in context: fixed non-empty script list and direct file/executable assertions. | None |

**Assertion quality**: PASS - all assertions verify real behavior; no trivial, tautological, ghost-loop, smoke-only, or mock-heavy assertions found.

---

### Quality Metrics
**Linter**: PASS - no Pint errors.
```text
Command: docker compose run --rm -T app sh -lc './vendor/bin/pint --test -v tests/Feature/Raffles/RaffleLifecycleTest.php tests/Feature/Tooling/ContainerRuntimeTest.php app/Models/Raffle.php app/Enums/RaffleStatus.php app/Exceptions/InvalidRaffleTransition.php database/factories/RaffleFactory.php database/migrations/2026_06_18_160000_create_raffles_table.php'
Result: PASS - 7 files
```

**Type Checker**: Not available - no project type-check command detected.

### Spec Compliance Matrix
| Requirement | Scenario | Test | Result |
|-------------|----------|------|--------|
| Persisted raffle lifecycle record | Create a draft raffle record | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it persists a new raffle as draft by default` | COMPLIANT |
| Persisted raffle lifecycle record | Strengthened initial draft invariant rejects mass-assigned `published` | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it does not persist a new raffle initially as published` | COMPLIANT |
| Persisted raffle lifecycle record | Strengthened initial draft invariant rejects mass-assigned `closed` | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it does not persist a new raffle initially as closed` | COMPLIANT |
| Persisted raffle lifecycle record | Unsupported lifecycle state is rejected | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it rejects unsupported persisted lifecycle states` | COMPLIANT |
| Publish from draft only | Publish a draft raffle | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it publishes a persisted draft raffle` | COMPLIANT |
| Publish from draft only | Closed raffle cannot be republished | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it does not publish a raffle from any state other than draft` | COMPLIANT |
| Publish from draft only | Unsaved raffle cannot be published | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it does not publish an unsaved raffle` | COMPLIANT |
| Close from published only | Close a published raffle | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it closes a published raffle` | COMPLIANT |
| Close from published only | Draft raffle cannot close directly | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it does not close a draft raffle directly` | COMPLIANT |
| Availability fields are basic lifecycle data | Persist explicit availability values | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it persists explicit availability fields on a raffle record` | COMPLIANT |
| Availability fields are basic lifecycle data | Time does not auto-transition lifecycle | `tests/Feature/Raffles/RaffleLifecycleTest.php` > `it does not auto change lifecycle state from persisted availability dates` | COMPLIANT |
| Lifecycle verification uses the canonical test runner | Lifecycle suite runs through repository runner | `./bin/test --filter=RaffleLifecycleTest` and `./bin/test` | COMPLIANT |
| Test DB isolation guard | Development database name is refused | `DB_TEST_DATABASE=raffles ./bin/test` and wrapper exit-code assertion | COMPLIANT |
| Test DB isolation guard | Reserved PostgreSQL database name is refused | `DB_TEST_DATABASE=postgres ./bin/test` and wrapper exit-code assertion | COMPLIANT |

**Compliance summary**: 14/14 verified scenarios compliant

### Correctness (Static Evidence)
| Requirement | Status | Notes |
|------------|--------|-------|
| Persisted raffle lifecycle record | Implemented and runtime-verified | Migration creates `raffles` with `status`, `starts_at`, `ends_at`; model casts status and dates; tests pass. |
| New raffles MUST start in draft | Implemented and runtime-verified | `Raffle::creating` forces `RaffleStatus::Draft`; tests prove attempted `published` and `closed` mass assignment are persisted as `draft`. |
| Supported states limited to draft/published/closed | Implemented and runtime-verified | `RaffleStatus` has only three cases; unsupported string assignment throws `ValueError`; test passes. |
| Publish from draft only | Implemented and runtime-verified | `publish()` requires a persisted model and current `draft`; happy path, closed rejection, and unsaved rejection all pass. |
| Close from published only | Implemented and runtime-verified | `close()` requires a persisted model and current `published`; happy path and draft rejection pass. |
| Availability fields are stored only | Implemented and runtime-verified | `starts_at` and `ends_at` are immutable datetime casts; no automatic mutation exists; no auto-transition test passes. |
| Factory states preserve real lifecycle transitions | Implemented and runtime-verified | `published()` and `closed()` use `afterCreating()` and call `publish()` / `close()` after persistence. |
| Test DB isolation guard | Implemented and runtime-verified | `bin/test` defaults to `raffles_testing`, rejects empty/matching/reserved test DB names, and negative guard commands exit 1 before Docker execution. |
| Out-of-scope complexity absent | Implemented | No admin raffle HTTP surface, no `drawn`/`cancelled` states, no reopen/rollback, no winner selection, no scheduling automation, no `isAvailableAt()` helper. |

### Coherence (Design)
| Decision | Followed? | Notes |
|----------|-----------|-------|
| Use plain Laravel model + migration + factory | Yes | Implementation lives in standard Laravel paths and does not introduce a module skeleton. |
| Keep transition rules in the model with enum + domain exception | Yes | `publish()` and `close()` are model methods backed by `RaffleStatus` and `InvalidRaffleTransition`. |
| Keep `starts_at` / `ends_at` as stored fields only | Yes | No time-driven state change or scheduler behavior is present. |
| Defer optional `isAvailableAt()` helper | Yes | Helper remains absent, matching the scoped decision. |
| Keep verification through canonical runner | Yes | Lifecycle and tooling tests were executed through `bin/test`; full `bin/test` passed. |

### Issues Found
**CRITICAL**: None

**WARNING**: None

**SUGGESTION**:
- Install `xdebug` or `pcov` only if changed-file coverage percentages should become a future hard gate.

### Verdict
PASS

The post-verify blocker fix is effective. The strengthened initial `draft` invariant is enforced against mass assignment, unsaved lifecycle transitions are rejected, the canonical test runner refuses unsafe test database names, all spec scenarios have passing runtime coverage, and no out-of-scope lifecycle complexity was introduced.
