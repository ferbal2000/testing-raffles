# Tasks: Reconcile Raffle Title Specifications

## Review Workload Forecast

| Field | Value |
|---|---|
| Estimated changed lines | 650–800 authored additions + deletions |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | One `size:exception` PR; three reviewable units: evidence baseline → delta/verification → canonical merge/archive |
| Delivery strategy | exception-ok |
| Chain strategy | size-exception |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: size-exception
400-line budget risk: High

Runtime harness: N/A — passive documentation; no application/source code, executable configuration, migrations, scripts, file-mode changes, or product/runtime behavior are changed by the apply work unit.

Structural verifier: structural readback; complete changed-path/mode evidence; `git diff --check`; requirement/scenario/rename counts; semantic checklist against the four stash blobs; protected identity checks; and rollback boundary.

Apply gate: maintainer-approved `size:exception`; one documentation-only PR.

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|---|---|---|---|---|---|
| 1 | Preserve planning authority | Single exception PR — unit 1 | `git diff --check -- openspec/changes/reconcile-raffle-title-specs` | N/A — documentation contracts have no runtime boundary | Revert planning documents |
| 2 | Prove archive readiness | Single exception PR — unit 2 | Structural readback/counts and `git diff --check` | N/A — no product behavior changes | Revert unit 2 artifacts |
| 3 | Merge canonical specs and archive | Single exception PR — unit 3 | Canonical counts and identity checks | N/A — archive changes normative text only | Revert canonical specs and archive |

## Phase 1: Apply — Evidence Preparation

- [x] 1.1 Inventory three deltas: four complete `MODIFIED` requirements, exactly 14 scenarios, and one `RENAMED` declaration.
- [x] 1.2 Validate exact OpenSpec `FROM`/`TO` headings: `Successful update persists availability only` to `Successful update persists title and availability`.
- [x] 1.3 Complete the semantic checklist for blobs `5892dc5b57c455aa422b68c2c48dfb1eb13d1ef8`, `3708a24839fd4f099618c637f6eaed53c4c46fd2`, `7277e4e131c4a16a049b0ac496b52e472abb91dc`, and `c2457c563ec260bbe9ab76aab0c8bfe38a324faa` without mutating the stash.
- [x] 1.4 Capture structural identities for 22 unrelated canonical scenarios; the four V2 capabilities `admin-raffle-list`, `admin-raffle-participation-list`, `public-raffle-catalog`, and `public-raffle-detail`; product/history files; and stash object `25c998200a6ad695de9830831e37680d2ed77aba`.

## Post-Apply Phase Gates

These phase-owned gates are not apply tasks and MUST remain incomplete until their owning phases run.

### Verify — Archive Readiness

**Gate 2.1:** Verify writes `verify-report.md` proving replacements preserve unrelated requirements/scenarios and satisfy all four blob checklists.

**Gate 2.2:** Verify records structural readback, diff, counts, and scope identities; tests, builds, and runtime harness remain N/A for specification-only changes.

### Archive — Canonical Reconciliation

**Gate 3.1:** Archive merges exactly three deltas into matching canonical specs, applies the rename, and archives the intact change.

**Gate 3.2:** Archive proves exactly three canonical specs changed; 22 unrelated scenarios, four V2 presentation capabilities, product files, migrations, tests, and prior archives remain unchanged.

### Delivery — Traceability

**Gate 4.1:** After archive, delivery commits the three reviewable work units, pushes the feature branch, and opens the maintainer-approved `size:exception` PR with `Closes #77` and exactly one `type:*` label.

Stash deletion is excluded: never apply, pop, drop, or mutate `stash@{0}`. Deletion requires the merged exception PR, updated local `main`, unchanged identity proof, and separate explicit maintainer confirmation.
