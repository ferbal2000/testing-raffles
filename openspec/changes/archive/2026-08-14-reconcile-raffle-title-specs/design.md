# Design: Reconcile Raffle Title Specifications

## Technical Approach

Archive exactly the three complete `MODIFIED` delta specs for `admin-raffle-create`, `admin-raffle-edit`, and `raffle-lifecycle` into their matching canonical specifications. Canonical requirements will describe current observable behavior only. This design will carry the completed rollout rationale into the reconciliation archive without changing product code, migrations, tests, presentation specifications, or the historical V2 archive.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|---|---|---|---|
| Authority boundary | Merge only the three named deltas; preserve rollout mechanics here. | Restore the cancelled V1 change; place migration mechanics in normative requirements. | Full requirement replacements preserve current contracts, while separating behavior from historical deployment mechanics. |
| Historical continuity | Record nullable add → initial backfill → required authoring → defensive null-only re-backfill → non-null enforcement. | Mention only the final schema. | The sequence explains why staged delivery remained safe and why the second backfill was necessary. |
| Presentation evidence | Leave `openspec/changes/archive/2026-08-11-admin-raffle-title-v2/` and its four canonical presentation capabilities unchanged. | Duplicate or rewrite V2 presentation requirements. | V2 already preserves escaped title-primary, ID-secondary rendering; this change repairs persistence and authoring authority only. |
| Recovery evidence | Treat stash commit `25c998200a6ad695de9830831e37680d2ed77aba` as read-only evidence. | Restore, apply, or delete the stash during reconciliation. | Durable semantic coverage must precede any separately authorized deletion request. |

## Reconciliation Flow

```text
three complete deltas
        ↓ archive merge
three matching canonical requirements
        +
this rationale retained in the dated reconciliation archive
```

The historical product rollout used:

1. `database/migrations/2026_08_04_000000_add_nullable_title_to_raffles_table.php` to add nullable `varchar(100)` without a default.
2. `database/migrations/2026_08_04_000001_backfill_raffle_titles.php` to assign `Sorteo #<ID>` to null rows in stable ID chunks.
3. Required admin authoring with pre-validation string trimming, `required|string|max:100`, duplicate acceptance, and persistence limited to `title`, `starts_at`, and `ends_at`.
4. `database/migrations/2026_08_04_000002_make_raffle_title_required.php` to repeat a null-only ID-chunked backfill immediately before enforcing non-null.

The second backfill closed the transition window in which legacy creation could still write null after the first backfill snapshot. Its guarded null-only update preserved every authored title. No migration installed a permanent title default.

## File Changes

| File | Archive action |
|---|---|
| `openspec/specs/admin-raffle-create/spec.md` | Replace the matching successful-create requirement from its delta. |
| `openspec/specs/admin-raffle-edit/spec.md` | Replace the form and update requirements and apply the declared rename. |
| `openspec/specs/raffle-lifecycle/spec.md` | Replace the persisted-record requirement from its delta. |
| `openspec/changes/reconcile-raffle-title-specs/` | Move intact to the dated archive after verification. |

No other canonical capability or historical archive may change.

## Interfaces / Contracts

No runtime interface changes. The merged contracts cover normalized required titles, the 100-character bound, duplicate acceptance, atomic validation failure with old input, all-current-status editing, escaped edit values with numeric ID, and editable legacy compatibility titles.

## Verification Strategy

No tests or builds are introduced. Future verification must compare each complete delta replacement with its canonical target, confirm unrelated requirements and scenarios remain byte-for-byte unchanged where possible, and complete the semantic checklist below against the four stash blobs and archived V2 evidence.

Runtime harness: N/A — passive documentation; no application/source code, executable configuration, migrations, scripts, file-mode changes, or product/runtime behavior are changed by this work unit.

Structural verifier: structural readback; complete changed-path/mode evidence; `git diff --check`; requirement/scenario/rename counts; semantic checklist against the four stash blobs; protected identity checks; and rollback boundary.

## Semantic Acceptance Before Stash Deletion

- [ ] Blob `5892dc5b57c455aa422b68c2c48dfb1eb13d1ef8`: creation, normalization, validation/old input, draft redirect, nullable availability, and duplicate-title semantics are canonical.
- [ ] Blob `3708a24839fd4f099618c637f6eaed53c4c46fd2`: protected escaped title/ID editing, permitted fields and statuses, normalization, atomic rejection, duplicates, redirects, and exclusions are canonical.
- [ ] Blob `7277e4e131c4a16a049b0ac496b52e472abb91dc`: required bounded persistence, lifecycle states, duplicates, and editable `Sorteo #<ID>` compatibility before non-null enforcement are canonical.
- [ ] Blob `c2457c563ec260bbe9ab76aab0c8bfe38a324faa`: staged sequence, null-only chunked backfills, transition-window rationale, authored-title preservation, validation/persistence boundaries, escaped presentation continuity, no default, and rollback boundaries are retained across this archive and the V2 archive.
- [ ] Exactly three canonical specs changed; four V2 presentation capabilities, product files, migrations, tests, and historical archives did not.
- [ ] Reconciliation was verified, archived, committed, merged; local `main` was updated; the stash object still resolves unchanged.
- [ ] A separate explicit confirmation authorized stash deletion.

## Threat Matrix

N/A — no routing, shell, subprocess, VCS/PR automation, executable-file classification, or process-integration boundary is introduced. The human-gated future stash decision is not automation.

## Rollback

Revert only the reconciliation archive/commit to restore prior canonical text. Historical product rollback remains bounded: relax non-null before reverting authoring; retain both backfills; remove the nullable column only with coordinated model/factory rollback. No rollback deletes raffle data or mutates the stash.

## Open Questions

None.
