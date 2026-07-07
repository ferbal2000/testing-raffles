# Proposal: Realtime Update Candidate Map

## Intent

Prevent realtime retrofit debt by documenting, before runtime infrastructure exists, which delivered raffle state changes should eventually refresh admin and public screens without manual browser reloads. This proposal supports issue #35.

## Scope

### In Scope
- Add an architecture/spec artifact that maps delivered raffle triggers to affected admin/public screens.
- Establish a maintenance rule: every future SDD slice that introduces an observable interactive change MUST update this candidate map as part of its scope.
- Include future event-name candidates only as labels that clarify intent, not as implemented events.
- Require a final product pass after broader development to decide whether additional realtime candidates are needed.

### Out of Scope
- Reverb, Echo, channels, JS listeners, broadcasting config, event classes, dispatch wiring, or runtime realtime behavior.
- Application behavior changes or runtime tests.
- Speculating on undeveloped product workflows beyond the maintenance rule.

## Capabilities

### New Capabilities
- `realtime-update-candidate-map`: Documents delivered observable state-change candidates, affected admin/public screens, future event candidate labels, and the maintenance rule for future interactive slices.

### Modified Capabilities
- None.

## Approach

Create a concise delta spec for `realtime-update-candidate-map`. Scope the matrix to delivered observable behavior from existing specs: raffle publication, raffle closure, participation open/close, guest registration creation, and registration count visibility. Defer registration-status realtime candidates until a future slice delivers observable status-change/status-visibility behavior. Treat admin and public Blade screens as future read models, while explicitly preserving the current request/redirect-only runtime.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `openspec/changes/realtime-update-candidates-map/specs/realtime-update-candidate-map/spec.md` | New | Delta spec for the candidate matrix and maintenance rule. |
| `openspec/specs/realtime-update-candidate-map/spec.md` | Future New | Archived capability after this slice completes. |
| `openspec/changes/realtime-update-candidates-map/proposal.md` | New | Proposal artifact for issue #35. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Matrix becomes stale as new interactions ship. | Medium | Add the maintenance rule as a requirement. |
| Readers mistake future event labels for implemented events. | Low | Label them explicitly as future event candidates. |
| Scope grows into runtime broadcasting. | Low | Keep runtime realtime out of scope for this slice. |

## Rollback Plan

Remove this change folder before archive, or revert the archived `realtime-update-candidate-map` spec after archive. No application code or data rollback is needed.

## Dependencies

- Approved issue #35: https://github.com/ferbal2000/testing-raffles/issues/35
- Existing delivered raffle specs under `openspec/specs/`.

## Success Criteria

- [ ] Specs phase has an exact new capability target.
- [ ] Candidate mapping covers delivered admin and public raffle behavior.
- [ ] Maintenance rule is explicit and runtime realtime remains out of scope.
