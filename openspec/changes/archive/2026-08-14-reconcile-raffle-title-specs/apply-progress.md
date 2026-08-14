# Apply Progress: Reconcile Raffle Title Specifications

## Status

Tasks 1.1–1.4 are complete. The corrected applicability gate admitted this evidence-only work unit as passive documentation after exact path, content, mode, declaration, structural, semantic, behavior-impact, and rollback proof. Strict TDD remains active globally; no behavioral tests were invented or run.

The accepted delivery boundary remains one documentation-only `size:exception` PR. This apply attempt covers only work unit `passive-documentation-evidence-completion`; it does not modify product behavior, canonical specs, historical archives, Git/GitHub state, or the stash. Phase 1 is ready for `sdd-verify`; tasks 2.1 onward remain pending.

## Retry Authority

| Field | Value |
|---|---|
| Reset revision | `sha256:68b89ad64942a9b60fcd5f0c535cd962b8ada2a282e143b4f2bdd630cc22e0d6` |
| Acquire state | `proceed` |
| Failed evidence under remediation | `sha256:b67af8fb95fb2f6d37301c02e7901e7578b5f9c978ede73b0d91df045b5b1fc3` |
| Objective | Structural proof only; runtime RED/GREEN/REFACTOR are not applicable and must not be invented. |
| Completion state | Blocked by the binding Strict TDD evidence contract; tasks 1.1–1.4 remain unchecked. |

## Delta Inventory

| Delta | Complete MODIFIED requirements | Scenarios | RENAMED declarations |
|---|---:|---:|---:|
| `admin-raffle-create` | 1 | 4 | 0 |
| `admin-raffle-edit` | 2 | 6 | 1 |
| `raffle-lifecycle` | 1 | 4 | 0 |
| **Total** | **4** | **14** | **1** |

Each replacement retains the target requirement's existing authentication, availability, redirect, or lifecycle scenarios and adds only delivered title behavior. No recovered statement conflicts with current delivered behavior.

## Rename Metadata

Exact OpenSpec metadata is present:

- `FROM`: `### Requirement: Successful update persists availability only`
- `TO`: `### Requirement: Successful update persists title and availability`

## Recovered Blob Checklist

| Blob | Result | Durable semantic evidence |
|---|---|---|
| `5892dc5b57c455aa422b68c2c48dfb1eb13d1ef8` | Pass | Create delta covers required string title, pre-validation normalization, invalid input and normalized old input, 100-character bound, duplicates, nullable availability, draft persistence, and index redirect. |
| `3708a24839fd4f099618c637f6eaed53c4c46fd2` | Pass | Edit delta covers protected access, escaped editable title and numeric ID, three permitted fields, all current statuses, normalization, atomic rejection with old input, duplicates, redirect, and explicit exclusions. |
| `7277e4e131c4a16a049b0ac496b52e472abb91dc` | Pass | Lifecycle delta covers required bounded titles, draft/published/closed states, duplicates, and editable `Sorteo #<ID>` compatibility before non-null enforcement. |
| `c2457c563ec260bbe9ab76aab0c8bfe38a324faa` | Pass | Design retains nullable add → initial stable-ID backfill → required authoring → defensive null-only ID backfill → non-null sequencing, transition-window rationale, authored-title preservation, validation/persistence boundaries, no default, and rollback boundaries; V2 archive retains escaped title-primary/ID-secondary presentation. |

All four source objects resolve as Git blobs with sizes 1738, 3165, 1615, and 5761 bytes respectively.

## Unrelated Canonical Scenario Identities

The 22 unrelated scenario blocks produce aggregate manifest `sha256:02c4e75d45c61914d945687049cc4e189d1a3d01838c53e2686fb9d67adc5a56`.

| Capability | Scenario | SHA-256 |
|---|---|---|
| create | Authenticated admin opens the create form | `1fe274cbde42029c9f6bbd3e01d7b25ad5c2fd9a0392c193e30c4c73b1ae3951` |
| create | Guest requests the create form | `5c46889cd5ac5c75c8f519cd78d270964e8214b3610bf2bb2cd227c6b2675121` |
| create | Admin submits blank availability values | `49937d0d6378622b0cc5494afeb1c8d05b51a831b44c32b6364991f7171a4659` |
| create | Admin submits an invalid availability value | `3c8eb3df59dc04389974a75bc34d745240936aabe0c7c180249e6946671517b6` |
| edit | Admin submits blank availability values | `00f277a5ca059bed1032c87e284fa258a6164efcc66f96b4804ec46771f45a6f` |
| edit | Admin submits an invalid availability value | `04cca977b8b5e8d9e587ba4ea8faae816b356e68610a63b663109b3e4665ab21` |
| lifecycle | Publish a draft raffle | `fff1264a536f6722b6a6a0f53c87175a197fb9ec6039451aba8fbddb65f36d8d` |
| lifecycle | Closed raffle cannot be republished | `9f463bc74abe72310a0103b7fd2335f21e444856b571aefb7f8e48ae9df59ee3` |
| lifecycle | Stale draft state does not override committed closure | `ee6bca46e203fb5fc6a3b82f7559e8481df260c15c1332f2c8cd72cf7c2a2cf0` |
| lifecycle | Close a published raffle | `57a5c498254a862be6cafe0f3e9f5990f1f7780c7a4141662a79cd7e3abc75f1` |
| lifecycle | Draft raffle cannot close directly | `3fad0bf70490ff6bd3f44b9b50fd20d6bdf6a9bc2de94c31f1d2e1585fa0ce00` |
| lifecycle | Already-closed raffle cannot close again | `b84b9ca5cc07a2124c97f2b2281b2419f60489b60ac3852764453ebd4402f3ff` |
| lifecycle | Coupled closure cannot partially persist | `eb66a1fbd16e79a5f861837a3b8ff83afb8e940edc53f664ab6bc4ae9c2f0df5` |
| lifecycle | Closure creates conceptual future draw eligibility only | `7f6ffff59444884ca5ba14b7e9e6d138c0385acb9f2f05c009ffaba3951e1a1b` |
| lifecycle | Persist explicit availability values | `1d86403046a53738adf4b3cefb2c4d5a1d064d5db875c2958d76787176566d84` |
| lifecycle | Time does not auto-transition lifecycle | `a1d407f821a3426982cc67a0f6ad5d20a1c615f923eac758bfd7961b8f29ff9b` |
| lifecycle | Availability dates do not open participation | `8f1dc2c362f9251598e24e5f6b72a0c75c44c689901bbb4955eac2d932f74d69` |
| lifecycle | Published raffle is visible before participation opens | `a7ca5c44b9c0e5b4dbacd179e5ad779b63a1b0690001440ba25669068b9b0c52` |
| lifecycle | Closed raffle cannot accept participants | `062ef284ed3347a2f5c685a9d31c349a41b10e0f4dd8f1a6c226865447843db2` |
| lifecycle | Published raffle resolves on the public detail route | `0e8116515ff73f685459ceb7e3a940053d8b885a21763e0b5644843b6abd2bbd` |
| lifecycle | Non-published raffle is filtered before rendering | `a9d63d58fc585dc8130021443d570760d4159acafd791770de90cb342c7cbb40` |
| lifecycle | Lifecycle suite runs through repository runner | `1b1a7eb69e0194fc6a97e34d43d25c43b5047eadbc55ab914186919b1425a431` |

## Protected Structural Identities

| Boundary | Identity |
|---|---|
| 25 relevant product/history files | All working blobs equal `HEAD`; manifest `sha256:3d2c93eb0d417f2ab9d263567929a8891a14c30ccac57f1cd87d5ac93aee10a7` |
| `app` / `database` / `resources` | Trees `9bae64d1d771f75192429ac59f6f2841b8d82b7f`, `8742eae8e8f7a744eca4f89e29d4c570899f81a8`, `b22069a1d94d9edc7654e4e60493fa7a3a0e0f33` |
| `lang` / `tests` / `routes` | Trees `051069fdc61c50992bd5ca77909f697d85fbe8da`, `6d9fd01023eb93b83e5ae0c98edb5d78c254faee`, `5f32bf5032556806f26e36268a657a218deae628` |
| Historical archives | Tree `85fed221ad3575b1a81f8ce7e08cd86c1f245fbe`; V2 subtree `217d8fb96987e795868cc95e764218413c180487` |
| Four canonical V2 capabilities | `e0c0e3fcdee43814c2974422adc6669f98f3ef8f`, `64ea44b24b9db8e188e20e4317c647540df3bd05`, `86b7ab4d6d0fac54cc6750f504bf4ed1d5467394`, `935d93d334f401b70a38b13cbdba7bb798b418b9` |
| Four archived V2 deltas | `425075478fb25ffc7d76b6e280355c261d143d36`, `1445e117ee197c7c2ce39e7a1a725a61e4fa5ab4`, `41cb507f416aba127b99313c5f4d14074e8bf25a`, `9b13a9676876f2949c3ef8ae828c4a563195b437` |
| Base and remote | `HEAD` and `origin/main` both `5e3185c12c3bc1cde313ee3885cce4f2b830a56a` |
| Stash | `stash@{0}` and object identity both `25c998200a6ad695de9830831e37680d2ed77aba`; type `commit` |

Protected tracked paths have no working-tree diff. No stash operation was executed.

## Strict TDD Cycle Evidence

| Task | Test file | Layer | Safety net | RED | GREEN | TRIANGULATE | REFACTOR |
|---|---|---|---|---|---|---|---|
| 1.1 | N/A | Structural evidence | N/A — passive docs | N/A by objective; required by binding gate | N/A — not run | N/A | N/A |
| 1.2 | N/A | Structural evidence | N/A — passive docs | N/A by objective; required by binding gate | N/A — not run | N/A | N/A |
| 1.3 | N/A | Read-only Git objects | N/A — passive evidence | N/A by objective; required by binding gate | N/A — not run | N/A | N/A |
| 1.4 | N/A | Read-only identities | N/A — passive evidence | N/A by objective; required by binding gate | N/A — not run | N/A | N/A |

No runtime tests were written or run. Structural evidence is proportional and passing, but the maintainer-authorized N/A disposition cannot satisfy the loaded module's mandatory RED and executed GREEN columns.

## Work Unit Evidence

| Evidence | Result |
|---|---|
| Focused structural commands | Passed on retry: 4 complete MODIFIED requirements, 14 scenarios, 1 rename, exact FROM/TO, 22/22 unrelated scenario SHA-256 matches, four blob resolutions and sizes, 25/25 protected working blobs equal to `HEAD`, protected tree/blob identities, and `git diff --check`. |
| Runtime harness | N/A — this work unit only records passive documentation and read-only Git-object evidence; it has no production/runtime boundary. |
| Rollback boundary | Revert only this retry's `apply-progress.md` updates; `tasks.md` remains unchanged and no unrelated work is removed. |

## Final Post-Override Attempt

| Field | Value |
|---|---|
| Reset revision | `sha256:9334fa3d5228384c6a1036dcb5e528fc581770414ab8f30ff0e15967085d31be` |
| Acquire state | `proceed` |
| Failed evidence under remediation | `sha256:8bcfb262da700aaf94424f7fb18c29178c4f4ed969e2453be77e51f444b1573d` |
| Work unit | `passive-documentation-evidence-completion` |
| Attempt budget | Final attempt; at most 100 changed lines |

### Applicability Classification

`passive_documentation` was not admitted. Exact content inspection proved that the two allowed files are passive Markdown, the only actual changed path is `openspec/changes/reconcile-raffle-title-specs/apply-progress.md`, and all inspected modes are unchanged non-executable `100644`. No product code, tests, migrations, operational configuration, scripts, generated executable artifacts, routing, shell/subprocess behavior, VCS/PR automation, process integration, runtime behavior, or stash mutation is present.

The gate nevertheless fails closed because design and tasks do not both explicitly declare the exact required statement `Runtime harness: N/A`. `tasks.md` provides a Runtime harness column with N/A reasons, while `design.md` provides a no-runtime-interface statement and structural verification strategy, but neither artifact contains that exact declaration. The override forbids inferred admissibility, so no passive-documentation applicability line or Passive Documentation Evidence table is recorded and tasks 1.1–1.4 remain unchecked.

### Reproduced Evidence

- Delta readback independently confirms four complete `MODIFIED` requirements, exactly 14 scenarios, one `RENAMED` declaration, and the exact required `FROM`/`TO` headings.
- All four semantic source objects independently resolve as blobs of 1738, 3165, 1615, and 5761 bytes; content readback satisfies the checklist already preserved above.
- All 22 unrelated canonical scenario digests independently match the identities preserved above.
- All 25 protected product/history working blobs independently equal `HEAD`; the six protected product trees, archive trees, four V2 canonical blobs, and four archived V2 delta blobs match the identities preserved above.
- `HEAD` and `origin/main` remain `5e3185c12c3bc1cde313ee3885cce4f2b830a56a`. `stash@{0}` remains commit `25c998200a6ad695de9830831e37680d2ed77aba` and was not mutated.
- Structural readback passed. `git diff --check` passed with no output. Runtime harness remains N/A in substance because this attempt records passive evidence only and crosses no runtime boundary.
- Actual write and rollback boundary: only this final-attempt section in `openspec/changes/reconcile-raffle-title-specs/apply-progress.md`; reverting it removes no prior evidence or unrelated work. The accepted delivery strategy remains `exception-ok` with `size:exception`.

Blocked terminally before task completion because the exact design-and-tasks declaration prerequisite is absent and design changes are outside the allowed write set. No automatic reset or further apply attempt is recommended; this candidate is not ready for verify.

## Corrected Passive-Documentation Apply Attempt

| Field | Value |
|---|---|
| Reset revision | `sha256:4ec5a7891cb163329bcc4bc75c68dc39a9eaf515a41c4992d0d1b5c204192c0d` |
| Acquire state | `proceed` |
| Failed evidence remediated | `sha256:25a77c852c947d32b41a764959df80869a8a4632c901cbad6422aa41703ccd5b` |
| Work unit | `passive-documentation-evidence-completion` |
| Budget | One attempt; at most 100 changed lines; accepted `exception-ok` / `size:exception` delivery |

### Admission Proof

`TDD Applicability: not_applicable — passive_documentation`

Content inspection proved both allowed and actual paths contain passive Markdown evidence only. No source code, tests, migrations, operational configuration, scripts, generated executable artifacts, routing, shell/subprocess behavior, VCS/PR automation, process/runtime integration, executable mode, mode change, product behavior, or unknown path is present. `design.md` and `tasks.md` both contain the exact `Runtime harness: N/A — passive documentation` declaration, full no-runtime reason, and exact structural verifier.

### Passive Documentation Evidence

| Unit | Allowed Paths | Actual Paths | Old/New Modes | Runtime Harness N/A + Reason | Structural Readback | `git diff --check` | Semantic Checklist | Behavior Impact | Rollback Boundary |
|---|---|---|---|---|---|---|---|---|---|
| 1.1 | `tasks.md`, `apply-progress.md` | Same exact paths | `100644` → `100644` each | Declared in design/tasks; passive evidence has no runtime boundary | Pass: 1/4/0 + 2/6/1 + 1/4/0 = 4 requirements, 14 scenarios, 1 rename | Pass; no output | Pass: complete replacements preserve target authentication, availability, redirect, and lifecycle semantics | Zero | Revert only Phase 1 checkboxes and this corrected-attempt section |
| 1.2 | `tasks.md`, `apply-progress.md` | Same exact paths | `100644` → `100644` each | Same declaration and reason | Pass: exact `FROM`/`TO` metadata read back | Pass; no output | Pass: availability-only → title-and-availability requirement rename | Zero | Same exact two-file boundary |
| 1.3 | `tasks.md`, `apply-progress.md` | Same exact paths | `100644` → `100644` each | Same declaration and reason | Pass: four read-only objects are blobs of 1738, 3165, 1615, and 5761 bytes | Pass; no output | Pass: creation, editing, lifecycle compatibility, staged migration, presentation continuity, no-default, and rollback semantics retained | Zero | Same exact two-file boundary; no stash mutation |
| 1.4 | `tasks.md`, `apply-progress.md` | Same exact paths | `100644` → `100644` each | Same declaration and reason | Pass: 22/22 scenario digests, 25/25 product/history blobs, six product trees, archive trees, and eight V2 blobs match | Pass; no output | Pass: four canonical V2 capabilities and archived deltas retain exact identities | Zero | Same exact two-file boundary; protected files and stash excluded |

### Completion Evidence

- Delta inventory and exact rename metadata match the cumulative evidence above.
- All 22 unrelated scenario SHA-256 identities match the preserved table; aggregate preserved manifest: `sha256:02c4e75d45c61914d945687049cc4e189d1a3d01838c53e2686fb9d67adc5a56`.
- All 25 protected product/history working blobs equal `HEAD`; protected product, archive, canonical V2, and archived V2 object identities match the cumulative evidence above.
- `HEAD` and `origin/main` remain `5e3185c12c3bc1cde313ee3885cce4f2b830a56a`.
- `stash@{0}` remains read-only commit `25c998200a6ad695de9830831e37680d2ed77aba`; it was not applied, popped, dropped, or otherwise mutated.
- Actual writes are exactly `tasks.md` and `apply-progress.md`; reverting only the four checkbox changes and this corrected-attempt section removes this work unit without removing prior evidence.

Phase 1 tasks 1.1–1.4 are complete. Tasks 2.1 onward remain unchecked. Ready for `sdd-verify`.
