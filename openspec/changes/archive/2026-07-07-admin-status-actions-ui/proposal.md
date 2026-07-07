# Proposal: Admin Status Actions UI

Issue: [#39](https://github.com/ferbal2000/testing-raffles/issues/39)

## Intent

Give admins a small way to operate registration exception states. Status exists in storage, but admins cannot see or change it.

## Scope

### In Scope
- Show each registration status on the admin registrations page.
- Add per-row `flag` and `cancel` actions for eligible active registrations.
- Treat `flagged` and `cancelled` as terminal in this slice; no restore/reactivate action.
- Show clear admin feedback: “The registration was marked for review.”, “The registration was cancelled.”, and “This action is no longer available for this registration.”
- Visually separate active vs cancelled totals.

### Out of Scope
- Realtime runtime: Reverb/Echo/channels/listeners/events/JS.
- Emails, notifications, bulk actions, audit history, broad redesign.
- Advanced filters/search unless required by current behavior.
- Restore/reactivate or generic tri-state status editing.

## Capabilities

### New Capabilities
- None.

### Modified Capabilities
- `admin-raffle-participation-list`: add status visibility, row actions, terminal-state handling, and separated totals.
- `raffle-registration-status`: replace the “no admin actions” foundation boundary with this first operational exception-handling slice.
- `realtime-update-candidate-map`: document registration status changes as future candidates only, with no runtime.

## Approach

Use the minimal moderation surface from exploration. Keep the workflow on the existing admin registrations page, protected by the current admin host/auth boundary. Enforce transitions server-side. Only active registrations can be flagged or cancelled; unavailable actions return the agreed clear error. Public participation remains unchanged.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Models/RaffleRegistration.php` | Modified | Status transitions. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Action handling. |
| `routes/admin.php` | Modified | Admin action routes. |
| `resources/views/admin/raffles/registrations.blade.php` | Modified | Badges, actions, totals. |
| `lang/es/admin-raffles.php` | Modified | Labels and flashes. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Modified | Display/action coverage. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Wording implies approval/rejection. | Medium | Use review/cancel language. |
| Terminal states surprise admins. | Medium | Hide unavailable actions and show clear feedback. |
| Scope drifts into workflow tooling. | Medium | Exclude filters, audit, bulk, notifications, restore. |

## Rollback Plan

Revert the implementation PR: remove action routes/handling, restore the read-only view, and leave the existing status enum/foundation intact.

## Dependencies

Approved issue #39, existing exploration, and status values: `active`, `flagged`, `cancelled`.

## Success Criteria

- [ ] Admins can see registration status and separated active/cancelled totals.
- [ ] Eligible active registrations can be flagged or cancelled with clear success messages.
- [ ] Flagged/cancelled registrations cannot be changed again in this slice.
- [ ] Public registration behavior and realtime runtime remain unchanged.
