# Design: Realtime Update Candidate Map

## Technical Approach

This change adds a documentation-only architecture/spec trail for issue #35: https://github.com/ferbal2000/testing-raffles/issues/35. The design follows the proposal and delta spec by treating current admin and public Blade screens as future realtime read-model candidates while preserving the existing request/redirect/page-render runtime.

The source of truth remains the OpenSpec capability. No Laravel code, routes, views, broadcasting configuration, event classes, channels, JS listeners, migrations, or runtime tests will be changed in this slice.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|---|---|---|---|
| Artifact boundary | Keep the candidate map in OpenSpec and mirror it to Engram. | Add README notes or inline code comments. | OpenSpec is already the SDD source of truth, and Engram keeps hybrid retrieval available for later phases. |
| Runtime scope | Documentation-only; no application code changes. | Add Laravel events, Reverb/Echo setup, channels, or listener stubs. | The current product is still Blade-first and request-response driven; runtime realtime design belongs to a later explicit slice. |
| Candidate labels | Use future event candidate labels with `(not implemented)` wording. | Treat labels as event contracts or omit labels entirely. | Labels help later planning, but the guardrail prevents readers from assuming executable events exist. |
| Maintenance model | Future interactive SDD slices MUST update this map during their own spec/design phase. | Defer all updates to a final realtime planning pass. | Updating while context is fresh prevents retrofit debt; the final pass remains a completeness check, not the primary maintenance mechanism. |

## Data Flow

Current runtime flow stays unchanged:

```text
Admin/Public HTTP request
  -> Laravel controller/model state change or query
  -> Blade render or redirect
  -> Browser refreshes only after navigation/submission
```

Documentation flow for this slice:

```text
Existing delivered specs + code surfaces
  -> delta spec candidate matrix
  -> design/tasks/verify/archive artifacts
  -> archived capability: openspec/specs/realtime-update-candidate-map/spec.md
```

## File Changes

| File | Action | Description |
|---|---|---|
| `openspec/changes/realtime-update-candidates-map/design.md` | Create | This design; records implementation structure, guardrails, and verification strategy for issue #35. |
| `openspec/changes/realtime-update-candidates-map/proposal.md` | Existing | Proposal remains the scope boundary and issue traceability source. |
| `openspec/changes/realtime-update-candidates-map/specs/realtime-update-candidate-map/spec.md` | Existing | Delta spec remains the candidate matrix and maintenance-rule source. |
| `openspec/specs/realtime-update-candidate-map/spec.md` | Future create during archive | Archive target after the delta spec is accepted. |
| `openspec/changes/realtime-update-candidates-map/tasks.md` | Future create | Later tasks artifact for documentation-only implementation steps. |
| `openspec/changes/realtime-update-candidates-map/verify-report.md` | Future create | Later verification artifact proving scope guard and artifact consistency. |

## Interfaces / Contracts

No runtime interfaces are introduced.

Documentation contract:

- The candidate map MUST include only delivered observable behavior.
- Future event names MUST be labeled as planning vocabulary and `(not implemented)`.
- Future interactive SDD slices that add observable admin/public state changes MUST update the map during their spec/design phase.
- Any later runtime realtime slice MUST create its own proposal/spec/design before adding events, channels, listeners, transport, or JS behavior.

## Testing Strategy

| Layer | What to Verify | Approach |
|---|---|---|
| Artifact inspection | Design, proposal, and spec agree on documentation-only scope and issue #35 traceability. | Read artifacts and compare stated scope. |
| Scope guard | No application code, tests, routes, views, broadcasting config, events, channels, migrations, or JS files changed. | Git diff/file-path inspection. |
| Runtime tests | None. | Not required because this slice changes documentation only. |

## Migration / Rollout

No migration required. Rollout is the archive step that promotes the delta spec to `openspec/specs/realtime-update-candidate-map/spec.md` and preserves the active change under the dated archive folder.

## Open Questions

None.
