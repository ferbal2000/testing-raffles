# Archive Report: public-raffle-detail-status

## Outcome

Archived the completed `public-raffle-detail-status` change after verification PASS and synced its spec deltas into the OpenSpec source of truth.

## Verification Gate

- Verdict: PASS
- Critical issues: None
- Tasks complete: 20/20
- Test result: 95 passed, 0 failed, 0 skipped

## Specs Synced

| Domain | Action | Details |
|--------|--------|---------|
| `public-raffle-detail` | Created | Promoted the delta spec as the first source-of-truth spec for the public raffle detail capability. |
| `raffle-lifecycle` | Updated | Replaced the `Published status governs publication only` requirement with the archived delta and preserved all unrelated requirements. |

## Preserved Decisions

- Public raffle URLs are ID-first: `/raffles/{id}` now, future-compatible with `/raffles/{id}/{slug?}`.
- `/raffles/not-a-number` is invalid and returns `404`.
- Only `published` raffles are publicly visible.
- Non-published raffles are excluded at the route/query/binding boundary before rendering.
- Numeric ID remains URL material only and is not shown in the page body.
- Home remains non-discovery only.
- Registration, ticket intent, participant entry, catalog, and slug implementation remain out of scope.
- Admin and public raffle resolution remain separate.

## Artifact Traceability

| Artifact | Location | Observation ID |
|----------|----------|----------------|
| Proposal | `openspec/changes/archive/2026-06-29-public-raffle-detail-status/proposal.md` | `#1168` |
| Spec bundle | `openspec/changes/archive/2026-06-29-public-raffle-detail-status/specs/` | `#1170` |
| Design | `openspec/changes/archive/2026-06-29-public-raffle-detail-status/design.md` | `#1172` |
| Tasks | `openspec/changes/archive/2026-06-29-public-raffle-detail-status/tasks.md` | `#1175` |
| Apply progress | `openspec/changes/archive/2026-06-29-public-raffle-detail-status/apply-progress.md` | `#1177` |
| Verify report | `openspec/changes/archive/2026-06-29-public-raffle-detail-status/verify-report.md` | `#1179` |

## Archive Verification

- [x] Main specs updated correctly
- [x] Change folder moved to `openspec/changes/archive/2026-06-29-public-raffle-detail-status/`
- [x] Archive contains proposal, specs, design, tasks, apply progress, and verify report
- [x] `openspec/changes/` no longer contains `public-raffle-detail-status/`

## Source of Truth Updated

- `openspec/specs/public-raffle-detail/spec.md`
- `openspec/specs/raffle-lifecycle/spec.md`

## Notes

Coverage-driver support is still unavailable in the PHP test runtime, so changed-file coverage remains non-blocking evidence only.
