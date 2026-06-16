# Proposal: Raffles Platform Foundation Slice

## Intent

Establish the first end-to-end raffle flow with auditability from day one. This slice proves the core product loop—create a raffle, accept entries, close it, draw one winner once—while adding the minimum test scaffold needed to make later TDD enforceable.

## Scope

### In Scope
- Scaffold an application test runner and a small project structure for RED-GREEN-REFACTOR work.
- Support one free raffle lifecycle: draft, published, closed, drawn.
- Accept participant entries with idempotency checks, execute a single authorized draw, and persist audit events.

### Out of Scope
- Paid entries, notifications, public fairness verification, and advanced eligibility rules.
- Multi-tenant admin, background jobs, analytics, and service decomposition.

## Capabilities

### New Capabilities
- `raffle-catalog`: Define and transition a raffle through its initial lifecycle states.
- `raffle-entries`: Submit and store participant entries safely for a published raffle.
- `draw-control`: Close a raffle and execute an exactly-once winner draw with guardrails.
- `audit-log`: Record immutable audit events for raffle and draw actions.

### Modified Capabilities
None.

## Approach

Start with a modular monolith and relational persistence boundaries, even if the first implementation remains simple. Build tests first where the runner exists; if the repo lacks one, begin by scaffolding the smallest runner setup and seed tests for domain rules before application code.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `openspec/config.yaml` | Modified | TDD/test command can be updated after runner selection. |
| `src/raffles/` | New | Raffle lifecycle module boundary. |
| `src/entries/` | New | Entry submission and idempotency rules. |
| `src/draws/` | New | Draw orchestration and exactly-once controls. |
| `src/audit/` | New | Append-only audit event recording. |
| `tests/` | New | Test harness and first domain/application tests. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Test runner choice delays delivery | Med | Keep runner setup minimal and tied to first failing tests only. |
| Draw trust rules are underspecified | Med | Limit scope to internal controlled draw and document assumptions in specs. |

## Rollback Plan

Remove the new module scaffolding, test harness, and first-slice flows; restore `openspec/config.yaml` test settings if runner adoption is reverted.

## Dependencies

- Framework/runtime selection and a minimal test runner.
- Relational persistence choice for raffle, entry, draw, and audit records.

## Success Criteria

- [ ] A single raffle can move from draft to published to closed to drawn with one persisted winner.
- [ ] Entry submission, draw guardrails, and audit records are covered by runnable automated tests.
