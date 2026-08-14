# Proposal: Reconcile Raffle Title Specifications

## Intent

Reconcile three stale canonical specifications with raffle-title behavior delivered by PRs #74–#76. This documentation-only change restores product authority for required title persistence and admin authoring. Product authority is approved issue [#77](https://github.com/ferbal2000/testing-raffles/issues/77).

## Scope

### In Scope

- Define observable title authoring, persistence, validation, duplicate acceptance, and legacy compatibility.
- Preserve every valid authentication, availability, redirect, and lifecycle scenario in complete requirement replacements.
- Record rollout sequencing and defensive null-only re-backfill rationale in the design and eventual archive.

### Out of Scope

- Application code, tests, migrations, canonical presentation specs, and archived historical changes.
- New capabilities or product behavior.
- Git, GitHub, or stash mutation, including `stash@{0}` and object `25c998200a6ad695de9830831e37680d2ed77aba`.

## Capabilities

### New Capabilities

None.

### Modified Capabilities

- `admin-raffle-create`: Require and persist a trimmed title, enforce its 100-character limit, reject invalid values with old input, and permit duplicates.
- `admin-raffle-edit`: Show escaped title and numeric ID; allow normalized title and availability updates in every current status; preserve atomic validation failure and duplicate acceptance.
- `raffle-lifecycle`: Define required bounded title persistence and editable `Sorteo #<ID>` compatibility values for legacy titleless records before non-null enforcement.

## Approach

Create complete `MODIFIED` delta requirements for the three capabilities. Normative specs will describe only observable behavior. The design will preserve nullable add → initial backfill → required authoring → defensive null-only re-backfill → non-null enforcement, preservation of authored titles, and no permanent default. Any recovered-requirement conflict with delivered behavior will block for maintainer decision.

## Affected Areas

| Area | Impact | Description |
|---|---|---|
| `openspec/specs/admin-raffle-create/spec.md` | Modified | Reconcile title creation contracts. |
| `openspec/specs/admin-raffle-edit/spec.md` | Modified | Reconcile title editing contracts. |
| `openspec/specs/raffle-lifecycle/spec.md` | Modified | Reconcile persistence and compatibility contracts. |

## Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| Full-block replacement drops valid scenarios | Medium | Inventory and preserve every existing scenario. |
| Historical mechanics leak into normative behavior | Low | Confine rollout rationale to design and archive. |
| Recovered evidence conflicts with the product | Low | Fail closed for maintainer resolution. |

## Rollback Plan

Revert only the reconciliation artifacts or commit; never rewrite historical archives or mutate the stash.

## Dependencies

- Completed exploration and Engram observation #2076.
- Approved issue [#77](https://github.com/ferbal2000/testing-raffles/issues/77).

## Success Criteria

- [ ] All three capabilities describe delivered observable title behavior while preserving existing valid scenarios.
- [ ] Issue [#77](https://github.com/ferbal2000/testing-raffles/issues/77) remains traceable through proposal, reconciliation, verification, and archive.
- [ ] Migration rationale is preserved outside normative requirements, and all excluded files and stash objects remain unchanged.
