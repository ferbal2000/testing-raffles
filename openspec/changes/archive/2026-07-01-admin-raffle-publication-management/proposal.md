# Proposal: Admin Raffle Publication Management

Enable admins to publish draft raffles through a confirmed admin action so the existing `draft -> published` lifecycle becomes operable before participation moderation.

## Intent

Admins can create and edit raffle availability, but cannot publish drafts through the admin UI. This blocks public visibility and the later participation workflow.

## Scope

### In Scope
- Add an admin-only publish action for draft raffles.
- Show the action on the admin raffle index only for draft rows.
- Require confirmation before publishing.
- Redirect to the index with scoped success or rejection feedback.
- Reuse existing domain lifecycle rules; add no extra publish-blocking validations.

### Out of Scope
- Edit-screen publishing; defer it to avoid coupling lifecycle control to the availability form and keep the review slice small.
- Published-to-draft reversal or reversible draft workflow.
- Participation moderation/status, tickets, winners, or draw behavior.
- Date-based automatic publication or participation eligibility changes.

## Capabilities

### New Capabilities
- `admin-raffle-publication-management`: Admin publication action, confirmation, and transition feedback.

### Modified Capabilities
- `admin-raffle-list`: Add draft-only publish entry point and scoped publication feedback on the index.

## Approach

Add a protected admin POST route that calls `Raffle::publish()`. Keep the index as the lifecycle control center: show a minimal draft-only control, require confirmation, and handle stale invalid transitions through existing domain enforcement.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/admin.php` | Modified | Add publish route. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Add publish action. |
| `resources/views/admin/raffles/index.blade.php` | Modified | Add draft-only confirmed control and feedback. |
| `lang/es/admin-raffles.php` | Modified | Add Spanish publish copy. |
| `tests/Feature/Raffles/*` | Modified/New | Cover auth, visibility, success, stale rejection. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Dense index row actions | Medium | Keep control minimal and draft-only. |
| Stale non-draft publish submit | Medium | Revalidate via `Raffle::publish()` and return rejection feedback. |
| Edit-screen expectation | Low | Document follow-up after index workflow is proven. |

## Rollback Plan

Remove the route, controller action, index control, copy, and tests. Domain lifecycle behavior remains intact.

## Dependencies

- Existing `Raffle::publish()` rule and `draft -> published` lifecycle.
- Strict TDD implementation through `bin/test`.

## Success Criteria

- [ ] Admins can publish a draft raffle from the index after confirmation.
- [ ] Published raffles become publicly visible under existing rules.
- [ ] Non-draft raffles do not show the publish action and stale invalid submissions are rejected.
- [ ] No new participation moderation or reversible draft behavior is introduced.
