# Archive Report: raffles-platform

## Summary

- Change: `raffles-platform`
- Archive mode: `hybrid`
- Archived scope: `PR 1 / Work Unit 1 foundation/bootstrap slice only`
- Archive date: `2026-06-16`
- Verification verdict: `PASS WITH ACCEPTED WARNINGS`

## Archived Boundary

This archive closes only the completed foundation/bootstrap slice:

- Laravel foundation scaffold
- Docker Compose local runtime and repository wrapper commands
- Pest/PHPUnit operational through `./bin/test`
- Host-separated public/admin route smoke coverage
- Spanish placeholder copy behind translation keys/files
- Explicit clarification that default Laravel `User` / `users` is public-site-only in this slice
- README migration/setup and local `.test` domain guidance

The full raffles platform feature is NOT complete in this archive.

## Traceability Sources

Artifacts were retrieved from OpenSpec files and backfilled to Engram for hybrid traceability before archive finalization.

| Artifact | OpenSpec/File Source | Engram Topic Key | Observation ID |
|----------|----------------------|------------------|----------------|
| Proposal | `openspec/changes/raffles-platform/proposal.md` | `sdd/raffles-platform/proposal` | `847` |
| Spec | `openspec/changes/raffles-platform/specs/*/spec.md` | `sdd/raffles-platform/spec` | `848` |
| Design | `openspec/changes/raffles-platform/design.md` | `sdd/raffles-platform/design` | `849` |
| Tasks | `openspec/changes/raffles-platform/tasks.md` | `sdd/raffles-platform/tasks` | `850` |
| Apply Progress | `openspec/changes/raffles-platform/apply-progress.md` | `sdd/raffles-platform/apply-progress` | `851` |
| Verify Report | `openspec/changes/raffles-platform/verify-report.md` | `sdd/raffles-platform/verify-report` | `852` |

## Spec Sync Decision

The original delta specs under `openspec/changes/raffles-platform/specs/` describe future raffle lifecycle, entries, draw control, and audit-log behavior that remains out of scope for PR 1 / Work Unit 1.

To avoid falsely promoting unimplemented behavior into the source of truth, this archive syncs only the verified foundation behavior into:

- `openspec/specs/platform-foundation/spec.md`

The following future domains were intentionally NOT synced to main specs in this archive:

- `raffle-catalog`
- `raffle-entries`
- `draw-control`
- `audit-log`

Those domain deltas remain preserved inside the archived change folder as planning/audit context, not as implemented source of truth.

## Verification Snapshot

- Final command: `./bin/test`
- Result: `11 passed / 44 assertions`
- Accepted warning 1: PR 1 uses SQLite in-memory tests and does not yet prove PostgreSQL-backed application behavior.
- Accepted warning 2: Initial scaffold tasks retain historical TDD traceability limitations from before the canonical runner existed.
- Critical issues: `None`

## Remaining Work Preserved

The archive preserves unfinished follow-up guidance for later slices:

- Phase 2: Separate admin/public identities and guard isolation
- Phase 3: Raffle lifecycle, entry acceptance, and audit success events
- Phase 4: Exactly-once draw control and rejected-draw audit path
- Follow-up infrastructure note: GitHub Actions wrapper alignment may be handled later

## Next Slice Guidance

Recommended next change starts from the archived Phase 2 tasks:

1. Write RED tests for admin/public guard isolation.
2. Superseding wording for the preserved Phase 2 plan: introduce `admins` / `Admin` for admin identity, while keeping Laravel `users` / `User` as the public identity boundary.
3. Configure guard, session, and middleware isolation.
4. Add at least one PostgreSQL-touching assertion once database-backed behavior lands.

## Archive Verification Checklist

- [x] No critical verification issues block archive.
- [x] Source of truth updated only for verified implemented behavior.
- [x] Remaining unfinished scope explicitly preserved for future slices.
- [x] Change folder prepared to move into dated archive.
