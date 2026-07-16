```yaml
schema: gentle-ai.verify-result/v1
evidence_revision: sha256:a9cd3deb5e020ccf577a80daf00311349f5c40264adeeeab8c60466040e4885f
verdict: pass
blockers: 0
critical_findings: 0
requirements: 8/8
scenarios: 32/32
test_command: COMPOSE_PROJECT_NAME=raffles bin/test
test_exit_code: 0
test_output_hash: sha256:a1bc1b6ed5624fc2cb764abf74f4be43df29baf6ec72a2d770c53f22c187f2f3
build_command: ""
build_exit_code: 0
build_output_hash: sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
```

# Verification Report: Raffle Close Verification Blockers

**Change**: `raffle-close-verification-blockers`
**Issue**: #52 approved; parent #47 remains open
**Mode**: Strict TDD
**Artifact mode**: Hybrid
**Review authority**: approved lineage `review-03bdc5fecf10ecbe`
**Authority revision**: `sha256:a9cd3deb5e020ccf577a80daf00311349f5c40264adeeeab8c60466040e4885f`
**Receipt hash**: `sha256:03ff733cfedc5c3b941dcd2e64821fc8cbc5bfb6f12fb16d7dfca4cace448a46`
**Verified base/HEAD/main/origin-main**: `7cc840ba837c353bfd73a8ff64307a701adba18a`
**Final verdict**: **PASS**

All eight requirements and thirty-two scenarios independently pass through current runtime tests or the executed deterministic structural/archive-safety contract. All database commands ran sequentially with `COMPOSE_PROJECT_NAME=raffles`; no repair, archive, delivery, review, or Git/GitHub mutation was performed.

## Completeness

| Metric | Result |
|---|---:|
| Requirements | 8/8 compliant |
| Scenarios | 32/32 compliant |
| Apply tasks | 10/10 complete |
| Incomplete apply tasks | 0 |
| Delta files | 5/5 inspected |
| Complete `MODIFIED` requirements | 6/6 archive-safe |
| Retained stable scenarios in `MODIFIED` blocks | 15/15 |

Actual totals were independently counted from the five active delta specs. The reported planning totals of 8 requirements and 32 scenarios are accurate.

## Runtime and Structural Evidence

Commands were executed in the required order and never in parallel.

| # | Exact command | Exit | Result | Exact output SHA-256 | Bytes |
|---:|---|---:|---|---|---:|
| 1 | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/AdminRafflePublicationTest.php tests/Feature/Raffles/AdminRaffleCloseTest.php` | 0 | 19 passed, 107 assertions | `sha256:cd45cb025ef929988dbc9318aae3ad60227f7db785adeae71cdd0232f8f8d25f` | 3,411 |
| 2 | `COMPOSE_PROJECT_NAME=raffles bin/test tests/Feature/Raffles/RaffleLifecycleTest.php` | 0 | 28 passed, 77 assertions | `sha256:cb7779c7629da19291875749a6ea804dda0561f6cec83a9c37a2ab9ef7a8b4d3` | 4,557 |
| 3 | `COMPOSE_PROJECT_NAME=raffles bin/test` | 0 | 191 passed, 969 assertions | `sha256:a1bc1b6ed5624fc2cb764abf74f4be43df29baf6ec72a2d770c53f22c187f2f3` | 28,802 |
| 4 | `git diff --check` | 0 | No output | `sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` | 0 |
| 5 | Structural/archive-safety command below | 0 | PASS; 5 deltas, 8 requirements, 32 scenarios, 6 complete `MODIFIED` requirements, 15 retained stable scenarios | `sha256:918527b4d696a134c6b8234a620067594ad430136cea5bc6ca06f3a66127bbd5` | 1,398 |

### Exact Structural/Archive-Safety Command

The exact command preimage is the UTF-8 content inside this block, beginning with `python3` and ending with `PY`. The fence and the final Markdown-separating newline immediately before it are not part of the command.

```text
python3 - <<'PY'
from pathlib import Path
import hashlib,re,subprocess
root=Path('.')
caps=['admin-raffle-publication-management','raffle-lifecycle','admin-raffle-list','raffle-participation-lifecycle','realtime-update-candidate-map']
base='7cc840ba837c353bfd73a8ff64307a701adba18a'
def parse(path):
 text=path.read_text(); lines=text.splitlines(); kind=None; out=[]
 for i,line in enumerate(lines):
  if line.startswith('## '): kind=line[3:]
  m=re.match(r'^### Requirement: (.+)$',line)
  if m:
   end=next((j for j in range(i+1,len(lines)) if lines[j].startswith('### Requirement: ')),len(lines)); block='\n'.join(lines[i:end]); out.append((kind,m.group(1),block,re.findall(r'^#### Scenario: (.+)$',block,re.M)))
 return text,out
delta={c:parse(root/'openspec/changes/raffle-close-verification-blockers/specs'/c/'spec.md') for c in caps}
stable={c:parse(root/'openspec/specs'/c/'spec.md') for c in caps}
reqs=sum(len(v[1]) for v in delta.values()); scenarios=sum(len(r[3]) for v in delta.values() for r in v[1]); assert (reqs,scenarios)==(8,32)
modified=[]; retained=0
for c in caps:
 old={r[1]:r[3] for r in stable[c][1]}
 for kind,name,block,current in delta[c][1]:
  if kind=='MODIFIED Requirements':
   assert ('MUST' in block or 'SHALL' in block) and name in old and not set(old[name])-set(current)
   modified.append((c,name,len(old[name]),len(current))); retained+=len(old[name])
assert len(modified)==6 and retained==15
all_delta='\n'.join(v[0] for v in delta.values())
close=['Published rows expose the confirmed close control','Ineligible rows hide the close control','Confirmed close reports scoped success','Rejected close reports no success','Guest close retains admin authentication behavior','Close a published raffle','Already-closed raffle cannot close again','Coupled closure cannot partially persist','Closure creates conceptual future draw eligibility only','Overall close ends active participation','Prior participation closure audit remains unchanged','Never-opened participation keeps null timestamps','Stale participation submission stores nothing','Participation audit failure rolls back overall closure']
assert all(f'#### Scenario: {s}' in all_delta for s in close)
assert all(x in all_delta for x in ['raffle_closed','authenticated admin identity','registrations MUST NOT be added, removed, or changed','ready_to_draw','drawn'])
rt=delta['realtime-update-candidate-map'][0]
assert all(x in rt for x in ['RaffleClosed` (not implemented)','documentation only','no runtime event, transport, listener, channel, dispatch, or auto-refresh','runtime broadcasting, events, listeners, channels, dispatch, and automatic refresh remain out of scope'])
assert subprocess.run(['git','diff','--quiet',base,'--','openspec/specs']).returncode==0
assert subprocess.check_output(['git','diff','--name-only',base,'--','app','routes','resources','database','lang']).decode().splitlines()==['app/Http/Controllers/Admin/RaffleController.php']
print('STRUCTURAL_ARCHIVE_SAFETY=PASS')
print(f'delta_files=5 requirements={reqs} scenarios={scenarios}')
print(f'modified_requirements={len(modified)} retained_stable_scenarios={retained}')
for c,n,old,new in modified: print(f'MODIFIED {c} :: {n} :: retained={old} delta={new} PASS')
for c in caps: print(f'DELTA {c} sha256={hashlib.sha256(delta[c][0].encode()).hexdigest()}')
print('accepted_close_contract=PASS')
print('realtime_documentation_only=PASS')
print('stable_specs_unchanged=PASS')
print('runtime_scope=app/Http/Controllers/Admin/RaffleController.php PASS')
PY
```

**Command preimage**: 3,525 bytes; `sha256:e65e63da38b17a64567e2a92c11474e0a760f5605aabcf0cfccfc00fc982a47d`

### Exact Structural Output

```text
STRUCTURAL_ARCHIVE_SAFETY=PASS
delta_files=5 requirements=8 scenarios=32
modified_requirements=6 retained_stable_scenarios=15
MODIFIED admin-raffle-publication-management :: Admins publish draft raffles only :: retained=3 delta=5 PASS
MODIFIED raffle-lifecycle :: Publish from draft only :: retained=2 delta=3 PASS
MODIFIED raffle-lifecycle :: Close from published only :: retained=2 delta=5 PASS
MODIFIED admin-raffle-list :: Minimal persisted raffle rows are visible :: retained=2 delta=2 PASS
MODIFIED admin-raffle-list :: Explicit empty state without broader admin restructuring :: retained=2 delta=2 PASS
MODIFIED realtime-update-candidate-map :: Delivered observable changes are mapped :: retained=4 delta=5 PASS
DELTA admin-raffle-publication-management sha256=c5774bee0ba717bb79eff9fe50969ee4e4ba3abf64575a9cf5d43f48ce5d6e90
DELTA raffle-lifecycle sha256=7b0579e273704413790703bde3d2c6cc1ca8ab83f712747c77657602374e4128
DELTA admin-raffle-list sha256=17170566493d76dce5fcc2136a4602771a1ebe81bcf01c765f06950a5d7a9e80
DELTA raffle-participation-lifecycle sha256=935fdad1b81db31b6f0d808eba7353d39ae3e55c57c4cbb5c3088e6ce934acf5
DELTA realtime-update-candidate-map sha256=6f00ba592743a1a4b0d44aa3d184921da878d5df496469233ff50a62023ed993
accepted_close_contract=PASS
realtime_documentation_only=PASS
stable_specs_unchanged=PASS
runtime_scope=app/Http/Controllers/Admin/RaffleController.php PASS
```

The output block above includes its terminating newline in the 1,398-byte output preimage.

## Spec Compliance Matrix

### Admin raffle publication management — 1 requirement / 5 scenarios

| Scenario | Passing evidence | Result |
|---|---|---|
| Admin publishes a draft raffle | `AdminRafflePublicationTest` — authenticated draft publish | ✅ COMPLIANT |
| Guest cannot publish a raffle | `AdminRafflePublicationTest` — unauthenticated publish rejection | ✅ COMPLIANT |
| Non-draft publish submission is rejected | `AdminRafflePublicationTest` — published-state rejection and unchanged status | ✅ COMPLIANT |
| Stale draft-bound publish cannot reopen a closed raffle | `AdminRafflePublicationTest` — stale controller-bound model; existing error, no success, exact closed snapshot, two registrations, public 404 | ✅ COMPLIANT |
| Publish emits deterministic lock-before-update evidence | `AdminRaffleCloseTest` — publish dataset records raffle `FOR UPDATE` before raffle `UPDATE` | ✅ COMPLIANT |

### Raffle lifecycle — 2 requirements / 8 scenarios

| Requirement | Scenario | Passing evidence | Result |
|---|---|---|---|
| Publish from draft only | Publish a draft raffle | Focused publication success plus `RaffleLifecycleTest` draft publish | ✅ COMPLIANT |
| Publish from draft only | Closed raffle cannot be republished | Lifecycle rejection plus stale committed-close/public-404 regression | ✅ COMPLIANT |
| Publish from draft only | Stale draft state does not override committed closure | Stale-bound feature regression preserves the complete business snapshot | ✅ COMPLIANT |
| Close from published only | Close a published raffle | `AdminRaffleCloseTest` three participation-state datasets plus model lifecycle close | ✅ COMPLIANT |
| Close from published only | Draft raffle cannot close directly | Admin rejection dataset and model lifecycle rejection | ✅ COMPLIANT |
| Close from published only | Already-closed raffle cannot close again | Admin closed/duplicate datasets and unchanged snapshot | ✅ COMPLIANT |
| Close from published only | Coupled closure cannot partially persist | `RaffleLifecycleTest` injected save failure preserves all closure fields | ✅ COMPLIANT |
| Close from published only | Closure creates conceptual future draw eligibility only | Model closure test proves one atomic save, preserved registrations, and absence of `ready_to_draw`/`drawn`; structural scope proves no draw/revenue contract expansion | ✅ COMPLIANT |

### Admin raffle list — 3 requirements / 9 scenarios

| Requirement | Scenario | Passing evidence | Result |
|---|---|---|---|
| Confirmed overall close action | Published rows expose the confirmed close control | `AdminRaffleIndexTest` covers active, closed, and never-opened participation rows and confirmation copy | ✅ COMPLIANT |
| Confirmed overall close action | Ineligible rows hide the close control | `AdminRaffleIndexTest` draft/closed visibility boundaries | ✅ COMPLIANT |
| Confirmed overall close action | Confirmed close reports scoped success | Index submit/redirect/render test proves translated success only and no certification field | ✅ COMPLIANT |
| Confirmed overall close action | Rejected close reports no success | Index and close feature tests prove translated rejection, absent success, and unchanged data | ✅ COMPLIANT |
| Confirmed overall close action | Guest close retains admin authentication behavior | Full suite `AdminRaffleCloseRouteTest` HTML/JSON auth datasets | ✅ COMPLIANT |
| Minimal persisted raffle rows are visible | Persisted raffles appear in the index | `AdminRaffleIndexTest` required fields and newest-first rows | ✅ COMPLIANT |
| Minimal persisted raffle rows are visible | Sparse raffle values still render safely | `AdminRaffleIndexTest` nullable availability placeholders | ✅ COMPLIANT |
| Explicit empty state | No raffles exist | `AdminRaffleIndexTest` explicit empty state | ✅ COMPLIANT |
| Explicit empty state | Raffle index stays narrowly scoped | Passing index suite plus structural proof that routes/UI/translations are unchanged and dedicated action contracts remain retained | ✅ COMPLIANT |

### Raffle participation lifecycle — 1 requirement / 5 scenarios

| Scenario | Passing evidence | Result |
|---|---|---|
| Overall close ends active participation | Admin close active-participation dataset and model atomic-save assertions | ✅ COMPLIANT |
| Prior participation closure audit remains unchanged | Admin close already-closed-participation dataset and model audit-preservation test | ✅ COMPLIANT |
| Never-opened participation keeps null timestamps | Admin close never-opened dataset and model null-audit test | ✅ COMPLIANT |
| Stale participation submission stores nothing | `PublicRaffleParticipationEntryTest` stale submission after admin overall close; zero registrations | ✅ COMPLIANT |
| Participation audit failure rolls back overall closure | `RaffleLifecycleTest` injected single-save failure; no partial closure persists | ✅ COMPLIANT |

### Realtime update candidate map — 1 requirement / 5 scenarios

| Scenario | Executed deterministic documentation evidence | Result |
|---|---|---|
| Delivered public visibility change is captured | Structural command verifies the complete retained map and `(not implemented)` publication label | ✅ COMPLIANT |
| Delivered admin close is captured as documentation only | Structural command verifies authenticated-admin close mapping and explicit no-event/transport/listener/channel/dispatch/refresh semantics | ✅ COMPLIANT |
| Delivered count surfaces are captured | Complete retained map includes both delivered count surfaces with no runtime semantics | ✅ COMPLIANT |
| Delivered admin status change is captured as future-only | Complete retained map preserves admin-only status mapping and future label | ✅ COMPLIANT |
| Undelivered workflow is excluded | Structural command verifies complete retained scenarios and explicit non-implementation boundaries | ✅ COMPLIANT |

**Compliance summary**: 32/32 scenarios compliant.

## Correctness and Design Coherence

| Contract | Evidence | Result |
|---|---|---|
| Publish transaction | `RaffleController::publish()` wraps the operation in `DB::transaction()` | ✅ |
| Fresh identity lookup | Route-bound `$raffle` contributes only `$raffle->getKey()` to `findOrFail()` | ✅ |
| Row lock before domain transition | Fresh query applies `lockForUpdate()` and calls `publish()` on `$lockedRaffle`; SQL-order test passes | ✅ |
| Stale committed closed rejection | Locked fresh model reaches existing `InvalidRaffleTransition` feedback | ✅ |
| Rejection side effects | No publish success; status, availability, audit, and registrations unchanged; public detail returns 404 | ✅ |
| Competing mutation order | Publish now joins overall close and participation mutations in the raffle-lock-before-update matrix | ✅ |
| Scope invariants | Only the admin controller changed under runtime paths; model, routes, UI, schema, translations, services, and stable specs are unchanged | ✅ |
| Exception boundary | Only `InvalidRaffleTransition` is translated; unexpected failures remain observable | ✅ |
| Proposal/spec/design/tasks coherence | Runtime, tests, complete deltas, and task evidence match the approved narrow approach; no drift found | ✅ |

The stale-object regression and SQL listener ordering prove a deterministic command protocol and lost-update prevention. They are **not simultaneous-session concurrency stress** and do not prove lock waiting, scheduler interleavings, or multi-session throughput.

## Apply Task Verification

| Task group | Completion evidence | Result |
|---|---|---|
| 1.1–1.3 RED | Changed tests exist; apply evidence records the stale overwrite and missing publish lock before production edit | ✅ Complete |
| 2.1–2.3 GREEN | Minimal controller transaction/fresh-lock change; focused and model commands pass independently | ✅ Complete |
| 3.1–3.2 REFACTOR | Snapshot helper retained explicit assertions; focused regression passes | ✅ Complete |
| 4.1 Full checks | Full suite and `git diff --check` pass independently | ✅ Complete |
| 4.2 Archive safety | Executed structural contract passes without stable-spec edits | ✅ Complete |

All 10 apply tasks are genuinely complete.

## Strict TDD Compliance

| Check | Result | Details |
|---|---|---|
| TDD evidence reported | ✅ | `apply-progress.md` contains a per-task TDD Cycle Evidence table. |
| Test files exist | ✅ | Both changed test files and the stable lifecycle file exist. |
| RED evidence coherent | ✅ | Apply evidence records 17-test baseline, then two intended failures: stale closed state reopened and publish lock absent. |
| GREEN confirmed independently | ✅ | Focused 19/19, lifecycle 28/28, and full 191/191 passed now. |
| Triangulation adequate | ✅ | Draft, published, closed, guest, stale-object, public visibility, participation states, rollback, and SQL-order paths vary behavior and outcomes. |
| Safety net for modified files | ✅ | 17/17 focused baseline is recorded before the two changed test cases and production edit. |

**TDD compliance**: 6/6 checks passed.

### Test Layer Distribution

| Layer | Tests | Files | Tool |
|---|---:|---:|---|
| Unit | 0 | 0 | Pest |
| Integration | 19 | 2 changed files | Pest + Laravel HTTP/database |
| E2E | 0 | 0 | Not configured |
| **Total changed-test executions** | **19** | **2** | |

The separate stable model lifecycle command adds 28 passing model/database integration executions.

### Assertion Quality

**Assertion quality**: ✅ All assertions in the two changed test files exercise production behavior. No tautologies, orphan empty checks, type-only-only checks, ghost loops, smoke-only tests, or mock-heavy tests were found. SQL-order assertions intentionally verify an explicit protocol requirement.

### Changed File Coverage

Coverage analysis skipped — no configured command/tool.

### Quality and Additional Checks

- Build skipped — no configured command/tool.
- Typecheck skipped — no configured command/tool.
- Coverage skipped — no configured command/tool.
- Linter/formatter skipped — no configured command/tool.
- E2E skipped — no configured command/tool.

These skips are informational and are not failures.

## Archive and Delivery Contracts

- Archive is correctly deferred; stable specs were not edited.
- Six complete `MODIFIED` requirements retain all 15 replaced stable scenarios.
- The participation delta remains `ADDED` and carries active-audit, prior-audit, null, stale-submission, registration-preservation, and rollback contracts.
- Realtime close mapping remains documentation-only; no runtime realtime semantics are implied.
- Approved later delivery remains two stacked-to-main PRs: runtime/tests first, archive/stable specs second against updated `main`.
- `size:exception` is approved only for the archive PR.
- The final PR must use `Closes #52`; parent #47 remains open.
- These are verified approved delivery facts only. No archive, commit, stage, push, PR, merge, issue, or branch action was executed.
- Proposal `supersedes: admin-raffle-close-action` is human SDD intent only. The old failed Gentle AI state is not claimed archived, approved, repaired, or mutated.

## Authority and Evidence Identity

| Field | Value |
|---|---|
| Review gate | `allow` |
| Approved lineage | `review-03bdc5fecf10ecbe` |
| Authority revision / evidence revision | `sha256:a9cd3deb5e020ccf577a80daf00311349f5c40264adeeeab8c60466040e4885f` |
| Receipt hash | `sha256:03ff733cfedc5c3b941dcd2e64821fc8cbc5bfb6f12fb16d7dfca4cace448a46` |
| Base/HEAD/main/origin-main | `7cc840ba837c353bfd73a8ff64307a701adba18a` |

No terminal-only receipt, chain bundle, or gate context was required or created. No review, refutation, correction, or Judgment Day was started.

## Strict Routing Envelope Preimage

The exact UTF-8 preimage is the content inside this block, beginning with `schema` and ending with the newline after `build_output_hash`. The fence is not part of the preimage.

```yaml
schema: gentle-ai.verify-result/v1
evidence_revision: sha256:a9cd3deb5e020ccf577a80daf00311349f5c40264adeeeab8c60466040e4885f
verdict: pass
blockers: 0
critical_findings: 0
requirements: 8/8
scenarios: 32/32
test_command: COMPOSE_PROJECT_NAME=raffles bin/test
test_exit_code: 0
test_output_hash: sha256:a1bc1b6ed5624fc2cb764abf74f4be43df29baf6ec72a2d770c53f22c187f2f3
build_command: ""
build_exit_code: 0
build_output_hash: sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
```

**Routing-envelope preimage length**: 496 bytes
**Routing-envelope preimage SHA-256**: `sha256:eed59c9da85476cdab7292204ad8f171f5e25ecf0d11eccdc1e07aa4df5c92d1`

## Canonical Verification-Evidence Preimage

For `complete-final-verification`, the exact canonical evidence bytes are the single JSON line below plus its terminating newline. The fence is not part of the preimage. This binds the authority evidence revision, current full-suite output, exact structural/archive-safety command and output, and exact empty configured-build output.

```json
{"schema":"gentle-ai.verification-evidence/v1","evidence_revision":"sha256:a9cd3deb5e020ccf577a80daf00311349f5c40264adeeeab8c60466040e4885f","verdict":"pass","requirements":"8/8","scenarios":"32/32","test_command":"COMPOSE_PROJECT_NAME=raffles bin/test","test_exit_code":0,"test_output_hash":"sha256:a1bc1b6ed5624fc2cb764abf74f4be43df29baf6ec72a2d770c53f22c187f2f3","documentation_contract_command_hash":"sha256:e65e63da38b17a64567e2a92c11474e0a760f5605aabcf0cfccfc00fc982a47d","documentation_contract_exit_code":0,"documentation_contract_output_hash":"sha256:918527b4d696a134c6b8234a620067594ad430136cea5bc6ca06f3a66127bbd5","build_command":"","build_exit_code":0,"build_output_hash":"sha256:e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"}
```

**Canonical evidence preimage length**: 760 bytes
**Canonical evidence preimage SHA-256**: `sha256:63f14d21b6a70f6bf377a136e258a178db2e870b0b4da246c2e32300f5a0cb05`

## Issues Found

### CRITICAL

None.

### WARNING

None.

### SUGGESTION

None.

## Verdict

**PASS** — 8/8 requirements, 32/32 scenarios, 10/10 tasks, all required sequential runtime checks, whitespace validation, and deterministic archive-safety evidence pass with no critical findings, warnings, or drift.

## Skill Resolution

`paths-injected` — exact `sdd-verify`, Strict TDD, report-format, and cognitive-doc-design skill files were read before task-specific verification.
