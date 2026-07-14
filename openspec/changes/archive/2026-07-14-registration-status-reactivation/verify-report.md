```yaml
schema: gentle-ai.verify-result/v1
evidence_revision: sha256:b6fb8a6daa3ecc87a66a252d29e5376d8bfa4b114b52581f0798302319e6c126
verdict: pass
blockers: 0
critical_findings: 0
requirements: 4/4
scenarios: 16/16
test_command: bin/test
test_exit_code: 0
test_output_hash: sha256:23e763dbeaea2151123c72dd59c68a12bd8a6a237519afd11c37646c4e209706
build_command: ""
build_exit_code: 0
build_output_hash: sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
```

# Verification Report: Registration Status Reactivation

**Change**: `registration-status-reactivation`  
**Issue**: [#41](https://github.com/ferbal2000/testing-raffles/issues/41)  
**Mode**: Strict TDD  
**Artifact mode**: Hybrid  
**Review authority**: Approved lineage `review-1f317eeb54cab231`, generation 1  
**Final verdict**: **PASS**

The implementation, all three required sequential application test commands, and the corrective executed documentation-contract check pass. All four realtime candidate-map scenarios now have deterministic executed evidence without adding a repository test or modifying application behavior.

## Completeness

| Metric | Result |
|---|---:|
| Requirements | 4/4 compliant |
| Scenarios | 16/16 compliant |
| Implementation tasks | 10/10 complete |
| Incomplete implementation tasks | 0 |
| Archive | Correctly deferred to the later archive phase |

Actual totals were counted from the three retrieved delta specs: four requirements and sixteen scenarios.

## Runtime Evidence

Commands were executed in the required order and never in parallel.

| # | Exact command | Exit | Result | Exact output SHA-256 | Bytes |
|---:|---|---:|---|---|---:|
| 1 | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php --filter=restore` | 0 | 15 passed, 69 assertions | `sha256:026dc5372cb904795111ad1d1ea75a0a5f4925b13525958b33348f64f44e180d` | 2,768 |
| 2 | `bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | 0 | 36 passed, 196 assertions | `sha256:b8fc51cbde67ecce23c9b613a8f3605db7c144917f265e76289bce3510a96cc2` | 5,709 |
| 3 | `bin/test` | 0 | 160 passed, 807 assertions | `sha256:23e763dbeaea2151123c72dd59c68a12bd8a6a237519afd11c37646c4e209706` | 24,294 |

The output preimages were captured byte-for-byte, including ANSI sequences and container output, at:

- `/tmp/opencode/registration-status-reactivation-verify-1.out`
- `/tmp/opencode/registration-status-reactivation-verify-2.out`
- `/tmp/opencode/registration-status-reactivation-verify-3.out`

No build command is configured in `openspec/config.yaml`; its canonical output preimage is the exact empty byte string (0 bytes), whose SHA-256 is `sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`.

Coverage, linter, type checker, and E2E tooling are explicitly unavailable in the cached project capabilities. Their absence is informational.

### Corrective Documentation-Contract Evidence

The prior application outputs were reused because no application code, test, spec, task, or design changed after their successful sequential execution. The following deterministic contract command was executed against the active realtime delta and current runtime diff:

```text
python3 -c 'import pathlib,re,subprocess,sys; p=pathlib.Path(sys.argv[1]); s=p.read_text(encoding="utf-8"); scenarios=["Delivered public visibility change is captured","Delivered count surfaces are captured","Delivered admin status change is captured as future-only","Undelivered workflow is excluded"]; assert all(f"#### Scenario: {x}" in s for x in scenarios), "missing scenario"; rows=[line for line in s.splitlines() if line.startswith("|") and "---" not in line and "Change |" not in line]; assert len(rows)==6, f"expected 6 candidate rows, got {len(rows)}"; assert all("(not implemented)" in row for row in rows), "future label not marked not implemented"; assert "| Draft raffle published | Admin raffle list | Public catalog/detail | `RafflePublished` (not implemented) |" in s, "public visibility mapping invalid"; assert "| Guest registration created | Admin raffle-list counts, registration-list summary | Public detail count visibility while open | `RegistrationCreated` (not implemented) |" in s, "count surfaces mapping invalid"; assert "no runtime realtime behavior SHALL be implied" in s, "runtime exclusion missing for counts"; assert "| Registration flagged, cancelled, or restored to active from flagged | Admin registration-list status/totals | None | `RegistrationStatusChanged` (not implemented) |" in s, "status mapping invalid"; assert "no public screen or runtime transport SHALL be implied" in s, "status runtime/public exclusion missing"; assert "runtime realtime behavior is out of scope" in s and "no future event label SHALL imply implemented runtime behavior" in s, "undelivered/runtime exclusion missing"; runtime_paths=["app","routes","resources/js","bootstrap","config","composer.json","composer.lock","package.json","package-lock.json"]; diff=subprocess.run(["git","diff","--unified=0","--",*runtime_paths],check=True,capture_output=True,text=True).stdout; added="\n".join(line[1:] for line in diff.splitlines() if line.startswith("+") and not line.startswith("+++")); untracked=subprocess.run(["git","ls-files","--others","--exclude-standard","--",*runtime_paths],check=True,capture_output=True,text=True).stdout.splitlines(); extra="\n".join(pathlib.Path(x).read_text(encoding="utf-8",errors="ignore") for x in untracked); forbidden=re.compile(r"(?i)(reverb|laravel-echo|shouldbroadcast|pusher|websocket|broadcast\s*\(|event\s*\(|dispatch\s*\(|registrationstatuschanged\s*::|listener)"); matches=forbidden.findall(added+"\n"+extra); assert not matches, f"runtime transport additions found: {matches}"; print("PASS documentation contract: 4/4 scenarios; future labels=6; runtime transport additions=0")' openspec/changes/registration-status-reactivation/specs/realtime-update-candidate-map/spec.md
```

| Field | Value |
|---|---|
| Exit code | `0` |
| Exact command bytes | `2,733` |
| Exact command SHA-256 | `sha256:511ccc27d2500f9188289a51e2ac1be45b27bd3e8b2dcd83875cf43e06f69393` |
| Exact output preimage | `PASS documentation contract: 4/4 scenarios; future labels=6; runtime transport additions=0\n` |
| Output bytes | `91` |
| Output SHA-256 | `sha256:30f233f3db8d5667e696c7f50a4c416d95c5787ca209460b121e6bbba1f2c8a4` |
| Captured preimage | `/tmp/opencode/registration-status-reactivation-doc-contract.out` |

The assertion exits non-zero for a missing scenario heading, missing/extra candidate row, any future label lacking `(not implemented)`, incorrect public/count/status mapping, missing no-runtime/no-public clauses, or a changed/untracked runtime file containing Reverb, Laravel Echo, broadcasting, listener, event dispatch, Pusher, or WebSocket transport markers.

## Scenario Compliance

### Admin raffle participation list

| Requirement | Scenario | Passing runtime evidence | Result |
|---|---|---|---|
| Protected per-raffle registration visibility | Authenticated admin opens a raffle registration list | `shows status-specific actions, separated totals, and registrations newest-first`; `shows existing registrations newest-first with allowed fields and read-only linked-account signals` | ✅ COMPLIANT |
| Protected per-raffle registration visibility | Guest requests a raffle registration list | `redirects guests to the admin login page for html raffle registration list requests`; JSON companion also passed | ✅ COMPLIANT |
| Explicit empty and sparse registration states | Raffle has no registrations | `shows an explicit empty state for authenticated admins when a raffle has no registrations`; zero-summary companion passed | ✅ COMPLIANT |
| Explicit empty and sparse registration states | Registration has no linked-user signal | `shows existing registrations newest-first with allowed fields and read-only linked-account signals` | ✅ COMPLIANT |

### Raffle registration status

| Requirement | Scenario | Passing runtime evidence | Result |
|---|---|---|---|
| Status foundation has no operational side effects | Status does not change public entry eligibility | `preserves public registration eligibility and creates active registrations` | ✅ COMPLIANT |
| Status foundation has no operational side effects | Active registration is marked for review | `flags and cancels active registrations with scoped success feedback` — `flag` dataset | ✅ COMPLIANT |
| Status foundation has no operational side effects | Active registration is cancelled | `flags and cancels active registrations with scoped success feedback` — `cancel` dataset | ✅ COMPLIANT |
| Status foundation has no operational side effects | Active registration rejects restore | `rejects restore for non-flagged registrations with unchanged status and scoped errors` — `active` dataset; repeated stale action also passed | ✅ COMPLIANT |
| Status foundation has no operational side effects | Flagged registration review is cleared | `restores a flagged registration to active with scoped success feedback`; render-submit-rerender harness also passed | ✅ COMPLIANT |
| Status foundation has no operational side effects | Restore rejects a registration outside the requested raffle | `returns bare not found when restore targets another raffles scope` | ✅ COMPLIANT |
| Status foundation has no operational side effects | Flagged registration rejects non-restore status actions | `rejects terminal registration status actions with unchanged status and scoped errors` — flagged flag/cancel datasets | ✅ COMPLIANT |
| Status foundation has no operational side effects | Cancelled registration rejects every status mutation | `rejects terminal registration status actions with unchanged status and scoped errors` — cancelled flag/cancel datasets; restore rejection `cancelled` dataset | ✅ COMPLIANT |

### Realtime update candidate map

| Requirement | Scenario | Evidence | Result |
|---|---|---|---|
| Delivered observable changes are mapped | Delivered public visibility change is captured | Executed contract asserts the publication row maps admin plus public catalog/detail and marks `RafflePublished` `(not implemented)`. | ✅ COMPLIANT |
| Delivered observable changes are mapped | Delivered count surfaces are captured | Executed contract asserts admin list/summary and public detail count surfaces, `RegistrationCreated` `(not implemented)`, and the no-runtime clause. | ✅ COMPLIANT |
| Delivered observable changes are mapped | Delivered admin status change is captured as future-only | Executed contract asserts flagged/cancelled/flagged-to-active maps only to admin status/totals, public is `None`, and `RegistrationStatusChanged` is `(not implemented)`. | ✅ COMPLIANT |
| Delivered observable changes are mapped | Undelivered workflow is excluded | Executed contract asserts all six labels are explicitly not implemented and scans changed/untracked runtime paths for forbidden realtime transport additions. | ✅ COMPLIANT |

**Compliance summary**: 16/16 scenarios are compliant through passing executed evidence.

## Required Boundary Checks

| Check | Evidence | Result |
|---|---|---|
| Flagged-only restore | Bounded model eligibility plus active/flagged/cancelled dataset; flagged HTTP success | ✅ |
| Active/cancelled rejection | Non-flagged restore dataset preserves both statuses and returns scoped error | ✅ |
| Repeated stale action | First restore succeeds; second returns unavailable and remains active | ✅ |
| Cross-raffle 404/no flash | Parent-scoped request returns 404, preserves flagged, has no success flash/errors | ✅ |
| Guest HTML/JSON | HTML redirects to admin login; JSON returns 401 | ✅ |
| GET rejection | GET returns 405 and preserves flagged | ✅ |
| Both numeric constraints | Separate raffle and registration malformed datasets return 404; route contract asserts both regexes | ✅ |
| Web/auth middleware | Route contract asserts `web` and `auth:admin` | ✅ |
| CSRF field rendering | Rendered hidden `_token` input is asserted | ✅ |
| Row-action boundaries | Active has flag/cancel only; flagged has restore only; cancelled has none | ✅ |
| Copy and flash | Clear-review confirmation, success, and unavailable feedback are asserted | ✅ |
| Counts | Before/after status totals and zero/non-zero totals are asserted | ✅ |
| Sparse/empty | Empty state and unlinked registration rendering pass | ✅ |
| Ordering | Newest-first ordering passes | ✅ |
| Linked user | Linked and unlinked signals pass | ✅ |
| Public behavior | Public submission remains eligible, normalized, active, and singular | ✅ |
| No realtime runtime | Executed contract scanned tracked additions and untracked files across runtime/dependency paths and found zero transport additions | ✅ executed evidence |

Standard Laravel feature POSTs do not prove negative CSRF rejection because testing bypasses CSRF middleware. The approved design asks for rendered token evidence plus `web` middleware placement unless a non-bypassed harness is used; both requested checks pass.

## Design Coherence

| Decision | Evidence | Result |
|---|---|---|
| Bounded model API | `canBeRestored()` accepts only `Flagged`; `restoreToActive()` throws otherwise | ✅ Followed |
| Explicit route in both host branches | The named POST route appears in both configured-host and fallback branches | ✅ Followed |
| Numeric route constraints | Both `{raffle}` and `{registration}` use `whereNumber()` | ✅ Followed |
| Parent-scoped transaction and lock reuse | `restoreRegistration()` delegates to `transitionRegistration()`, which uses `$raffle->registrations()`, `DB::transaction()`, and `lockForUpdate()` | ✅ Followed |
| Bounded UI | Restore form renders only when `canBeRestored()`; active/cancelled boundaries remain intact | ✅ Followed |
| No realtime runtime | Delta remains future-only; no app/route realtime implementation found | ✅ Followed |

No design deviations were found.

## Strict TDD Compliance

| Check | Result | Details |
|---|---|---|
| TDD evidence reported | ✅ | `apply-progress.md` contains the required per-task TDD Cycle Evidence table. |
| All tasks have tests/evidence | ✅ | 10/10 implementation tasks reference the changed feature test or full suite. |
| RED confirmed | ✅ | Test file exists; behavioral tasks record prior failures. Refactor/verification-only tasks correctly record RED as N/A. |
| GREEN confirmed | ✅ | Focused, full-file, and full-suite commands all passed in this independent run. |
| Triangulation adequate | ✅ | Status states, stale repeat, scope, auth formats, methods, malformed parameters, rendering, counts, sparse/empty, linked user, ordering, and public behavior vary inputs and outcomes. |
| Safety net for modified file | ✅ | Every task row records a pre-change or pre-refactor passing baseline. |

**TDD compliance**: 6/6 checks passed.

### Test Layer Distribution

| Layer | Executions | Files | Tool |
|---|---:|---:|---|
| Unit/domain-only | 3 | 1 mixed feature file | Pest |
| Integration | 33 | 1 | Laravel HTTP tests via Pest |
| E2E | 0 | 0 | Not installed |
| **Total** | **36** | **1** | |

The three domain-only executions are the `canBeRestored()` status dataset. All other executions in the changed test file exercise Laravel persistence, routing, rendering, or HTTP behavior.

### Changed File Coverage

Coverage analysis skipped — no coverage tool is available.

### Assertion Quality

**Assertion quality**: ✅ All assertions in the changed test file verify production behavior. No tautologies, orphan empty checks, type-only-only checks, ghost assertion loops, smoke-only tests, or mock-heavy tests were found. The single `foreach` constructs fixtures and contains no assertions.

### Quality Metrics

**Linter**: ➖ Not available  
**Type checker**: ➖ Not available

## Authority and Evidence Identity

The approved native receipt was read from the Git common directory and matches:

| Field | Value |
|---|---|
| Lineage | `review-1f317eeb54cab231` |
| Generation | `1` |
| State | `approved` |
| Candidate tree | `d4bbcdff5748c86e2c94f487fc5fedb4c911f355` |
| Paths digest | `sha256:fd475c874b3e1b1af3f3b0ef81ab6221a11e761045eac77faf3df882f690d001` |
| Policy hash | `sha256:34fb63d7f29f8613cd4431382b1057398a4816f8a4c20fc34677fffc80a184f6` |
| Evidence revision | `sha256:b6fb8a6daa3ecc87a66a252d29e5376d8bfa4b114b52581f0798302319e6c126` |

No new review, correction, or refuter was started.

## Strict Routing Envelope Preimage

The strict routing-envelope preimage is the exact UTF-8 content inside the following block, beginning with `schema` and ending with the newline after `build_output_hash`. The fence is not part of the preimage.

```yaml
schema: gentle-ai.verify-result/v1
evidence_revision: sha256:b6fb8a6daa3ecc87a66a252d29e5376d8bfa4b114b52581f0798302319e6c126
verdict: pass
blockers: 0
critical_findings: 0
requirements: 4/4
scenarios: 16/16
test_command: bin/test
test_exit_code: 0
test_output_hash: sha256:23e763dbeaea2151123c72dd59c68a12bd8a6a237519afd11c37646c4e209706
build_command: ""
build_exit_code: 0
build_output_hash: sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
```

**Routing-envelope preimage length**: 467 bytes  
**Routing-envelope preimage SHA-256**: `sha256:37b6ccb694ebbc98a67ec9d6afdb89940cdb9358ee307bc9aee9c41808673844`

## Canonical Verification-Evidence Preimage

For `complete-final-verification` handoff, the exact canonical evidence bytes are the single JSON line below plus its terminating newline. The fence is not part of the preimage. This binds the authority evidence revision, reused full-suite output, exact documentation-contract command hash, documentation-contract output, and empty configured-build output.

```json
{"schema":"gentle-ai.verification-evidence/v1","evidence_revision":"sha256:b6fb8a6daa3ecc87a66a252d29e5376d8bfa4b114b52581f0798302319e6c126","verdict":"pass","requirements":"4/4","scenarios":"16/16","test_command":"bin/test","test_exit_code":0,"test_output_hash":"sha256:23e763dbeaea2151123c72dd59c68a12bd8a6a237519afd11c37646c4e209706","documentation_contract_command_hash":"sha256:511ccc27d2500f9188289a51e2ac1be45b27bd3e8b2dcd83875cf43e06f69393","documentation_contract_exit_code":0,"documentation_contract_output_hash":"sha256:30f233f3db8d5667e696c7f50a4c416d95c5787ca209460b121e6bbba1f2c8a4","build_command":"","build_exit_code":0,"build_output_hash":"sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"}
```

**Canonical evidence preimage length**: 731 bytes  
**Canonical evidence preimage SHA-256**: `sha256:63a7c06995fc7f90d317c9bf6ea38b0d7891187701cf603a7bf7766efaaef389`

## Issues Found

### CRITICAL

None.

### WARNING

None.

### SUGGESTION

None. Verification does not prescribe a fix.

## Verdict

**PASS** — application behavior, design coherence, TDD evidence, and all sixteen scenarios have passing executed evidence. The four prior documentation blockers are resolved by the deterministic contract check without repository behavior changes.
