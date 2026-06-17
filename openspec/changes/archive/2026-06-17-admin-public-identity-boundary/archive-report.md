# Archive Report: admin-public-identity-boundary

## Summary

- Change: `admin-public-identity-boundary`
- Archive mode: `openspec`
- Archived scope: `Full change after Work Unit 1 + Work Unit 2 completion`
- Archive date: `2026-06-17`
- Verification verdict: `PASS`

## Archived Outcome

This archive closes the verified identity-boundary slice:

- `App\Models\User` and `users` remain the public-site identity contract.
- `App\Models\Admin` and `admins` define the separate admin identity contract.
- Admin guard/provider/password-broker wiring is explicit and isolated from public auth.
- Boundary middleware selects host-aware session cookies before session startup.
- Public/admin session cookies and remember-me state remain isolated across hosts.
- Minimal `_test/auth/*` probe routes prove the boundary with real HTTP requests.

## Spec Sync Decision

Only verified identity-boundary behavior was promoted into source-of-truth specs.

### Synced to Main Specs

- `openspec/specs/admin-identity-boundary/spec.md`
- `openspec/specs/platform-foundation/spec.md`

### Intentionally NOT Promoted

- Raffle lifecycle behavior
- Entry submission behavior
- Draw execution behavior
- Audit log behavior
- Real-time transport/runtime implementation

Real-time delivery remains future cross-cutting context from exploration/design only; this archive does **not** mark any broadcasting, Reverb, Echo, SSE, or polling runtime as implemented.

## Traceability Sources

| Artifact | Source |
|----------|--------|
| Proposal | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/proposal.md` |
| Exploration | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/exploration.md` |
| Design | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/design.md` |
| Tasks | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/tasks.md` |
| Apply Progress | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/apply-progress.md` |
| Verify Report | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/verify-report.md` |
| Delta Spec | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/specs/admin-identity-boundary/spec.md` |
| Delta Spec | `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/specs/platform-foundation/spec.md` |

## Verification Snapshot

- Focused command: `./bin/test tests/Feature/Auth tests/Feature/Routing/HostSeparationTest.php`
- Focused result: `12 passed / 74 assertions`
- Full suite command: `./bin/test`
- Full suite result: `18 passed / 104 assertions`
- Formatting: `./vendor/bin/pint --test` passed on changed PHP files
- Critical issues: `None`
- Warnings: `None`
- Final readiness: `READY`

## Archive Verification Checklist

- [x] No critical verification issues block archive.
- [x] Source of truth updated only for verified implemented behavior.
- [x] Admin identity boundary main spec created from the completed delta.
- [x] Platform foundation main spec updated without promoting future raffle or realtime scope.
- [x] Archive contains proposal, exploration, specs, design, tasks, apply progress, verify report, and archive report artifacts.
- [x] Active changes directory no longer contains `admin-public-identity-boundary`.
