# Proposal: Raffle Lifecycle Basic

## Intent

Create the first raffle domain slice so the codebase can persist a raffle and enforce the normal `draft -> published -> closed` flow without reviving the oversized lifecycle/core attempt.

## Scope

### In Scope
- Add a persisted raffle record with lifecycle status plus `starts_at` and `ends_at` storage.
- Introduce domain-first lifecycle primitives for create, publish, and close.
- Prove lifecycle rules with tests run through `bin/test`, without depending on admin HTTP CRUD.

### Out of Scope
- `drawn`, `cancelled`, reopen/rollback flows, winner selection, legal/audit workflows.
- Published edit policy, automatic time-based transitions, public entry behavior, admin HTTP endpoints.

## Capabilities

### New Capabilities
- `raffle-lifecycle`: First raffle domain capability covering persistence and the basic `draft -> published -> closed` lifecycle.

### Modified Capabilities
- None.

## Approach

Use strict TDD and implement the smallest domain-first slice: `raffles` persistence, a raffle model plus focused lifecycle actions/rules, and tests for allowed transitions and stored schedule fields. Keep transport out of scope; if future admin HTTP is needed, it should call these primitives instead of owning lifecycle rules.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `database/migrations/` | New | Add the first `raffles` table migration. |
| `app/Models/` | New | Add `Raffle` persistence model. |
| `app/Actions/` or `app/Domain/` | New | Add small lifecycle primitives for create/publish/close. |
| `tests/Feature/` and/or `tests/Unit/` | New | Add RED-GREEN lifecycle coverage via `bin/test`. |
| `openspec/changes/raffle-lifecycle-basic/specs/` | New | Define the behavior contract for the new capability. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Scope expands into admin UI or policy edge cases | Med | Reject HTTP, audit, and edit-policy work in this slice. |
| First raffle structure becomes hard to evolve | Med | Keep rules in small lifecycle primitives, not controllers. |
| `starts_at` / `ends_at` grow into scheduling logic | Low | Treat them as stored fields only in this change. |

## Rollback Plan

Revert the raffle migration, model, lifecycle primitives, tests, and the change spec as one slice; no admin/public boundary rollback is needed because those capabilities stay unchanged.

## Dependencies

- Existing admin identity boundary and canonical `bin/test` runner.

## Success Criteria

- [ ] A raffle can be stored with `draft`, `published`, or `closed` status plus `starts_at` / `ends_at` fields.
- [ ] Automated tests prove `draft -> published -> closed` is allowed and out-of-scope states/flows are absent.
