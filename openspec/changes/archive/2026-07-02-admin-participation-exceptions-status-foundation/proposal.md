# Proposal: Admin Participation Exceptions Status Foundation

## Intent

Persist a minimal per-registration status foundation so participation remains automatically valid by default while future admin exception handling can distinguish normal entries from flagged or cancelled entries.

## Proposal Question Round

Interactive shaping is treated as answered by the orchestrator-provided assumptions: default flow is automated, admin intervention is exceptional, vocabulary is `active`/`flagged`/`cancelled`, and this slice is persistence-only.

## Scope

### In Scope
- Add a persisted `status` axis to `raffle_registrations` or its current equivalent.
- Default all newly created registrations to `active`.
- Represent the allowed vocabulary as `active`, `flagged`, and `cancelled` in model/factory support.

### Out of Scope
- Admin UI controls, admin mutation actions, badges, filters, or audit trail.
- Automated analysis, ads, credits, tickets, draw logic, payments, or approval/rejection workflows.
- Changing public participation eligibility or per-raffle email uniqueness.

## Capabilities

### New Capabilities
- `raffle-registration-status`: Defines persisted registration status vocabulary and default-active behavior.

### Modified Capabilities
- None.

## Approach

Use a small schema/model foundation: add a `status` column with default `active`, make the model/factory aware of it, and keep public registration creation behavior default-valid without adding UI or workflow semantics.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `database/migrations/*_add_status_to_raffle_registrations_table.php` | Created | Add persisted status with default `active` through an additive migration. |
| `app/Models/RaffleRegistration.php` | Modified | Expose status as fillable/constant-backed domain vocabulary. |
| `database/factories/RaffleRegistrationFactory.php` | Modified | Create registrations with default `active` status. |
| `app/Http/Controllers/Public/RaffleController.php` | Unchanged/Verified | Existing creates should continue to produce default-active registrations. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Status is mistaken for an approval workflow | Medium | Use neutral exception vocabulary and exclude UI/actions. |
| Existing rows need a safe status value | Low | Backfill/default to `active`. |
| Future admin actions need audit metadata | Medium | Defer intentionally; add audit fields with the action slice. |

## Rollback Plan

Remove the status model/factory references and drop the `status` column/default in the matching migration rollback path before any follow-up slice depends on it.

## Dependencies

- Existing `raffle_registrations` persistence model.

## Success Criteria

- [x] New and existing registrations have `status = active` unless explicitly set otherwise.
- [x] The only accepted status vocabulary is `active`, `flagged`, and `cancelled`.
- [x] No admin UI, workflow, credit, ticket, draw, payment, or approval behavior is introduced.

Archived and verified on 2026-07-02; see `verify-report.md` and `archive-report.md` in this archived change folder for traceability.
