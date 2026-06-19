# Archive Report: raffle-lifecycle-basic

## Summary

- Change: `raffle-lifecycle-basic`
- Archive mode: `hybrid`
- Archived scope: `Full raffle lifecycle basic slice`
- Archive date: `2026-06-18`
- Verification verdict: `PASS`

## Archived Outcome

This archive closes the verified raffle lifecycle slice:

- A persisted `raffles` record exists with `draft`, `published`, and `closed` lifecycle states.
- New raffles default to `draft`.
- `publish()` allows only `draft -> published`.
- `close()` allows only `published -> closed`.
- `starts_at` and `ends_at` are stored lifecycle data only and do not auto-transition status.
- Verification runs through the canonical `bin/test` runner, including the PostgreSQL testing database isolation remediation.

## Spec Sync Decision

Only verified raffle lifecycle behavior was promoted into the source-of-truth specs.

### Synced to Main Specs

- `openspec/specs/raffle-lifecycle/spec.md`

### Intentionally NOT Promoted

- Admin HTTP CRUD flows
- Reopen or rollback transitions
- `drawn` and `cancelled` lifecycle states
- Winner selection or draw execution behavior
- Automatic time-based scheduling transitions

## Traceability Sources

| Artifact | OpenSpec/File Source | Engram Topic Key | Observation ID |
|----------|----------------------|------------------|----------------|
| Proposal | `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/proposal.md` | `sdd/raffle-lifecycle-basic/proposal` | `946` |
| Delta Spec | `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/specs/raffle-lifecycle/spec.md` | `sdd/raffle-lifecycle-basic/spec` | `947` |
| Design | `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/design.md` | `sdd/raffle-lifecycle-basic/design` | `950` |
| Tasks | `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/tasks.md` | `sdd/raffle-lifecycle-basic/tasks` | `953` |
| Apply Progress | `sdd/raffle-lifecycle-basic/apply-progress` | `sdd/raffle-lifecycle-basic/apply-progress` | `956` |
| Verify Report | `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/verify-report.md` | `sdd/raffle-lifecycle-basic/verify-report` | `959` |

## Verification Snapshot

- Focused command: `./bin/test tests/Feature/Tooling/ContainerRuntimeTest.php`
- Focused result: `PASS — 6 passed, 38 assertions`
- Lifecycle command: `./bin/test --filter=RaffleLifecycleTest`
- Lifecycle result: `PASS — 11 passed, 15 assertions`
- Guard command: `DB_TEST_DATABASE=raffles ./bin/test`
- Guard result: `PASS — refused development database with exit code 1`
- Guard command: `DB_TEST_DATABASE=postgres ./bin/test`
- Guard result: `PASS — refused reserved database name with exit code 1`
- Full suite command: `./bin/test`
- Full suite result: `PASS — 32 passed, 136 assertions`
- Formatting: `./vendor/bin/pint --test` passed on changed PHP files
- Critical issues: `None`
- Warnings: `None`
- Final readiness: `READY`

## Archive Verification Checklist

- [x] No critical verification issues block archive.
- [x] Source of truth updated only for verified implemented behavior.
- [x] `openspec/specs/raffle-lifecycle/spec.md` created from the completed delta.
- [x] Archive contains proposal, exploration, specs, design, tasks, verify report, and archive report artifacts.
- [x] Traceability includes all required Engram observation IDs.
- [x] Active changes directory no longer contains `raffle-lifecycle-basic` after archive move.
