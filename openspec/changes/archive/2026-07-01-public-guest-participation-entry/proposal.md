# Proposal: Public Guest Participation Entry

## Intent

Allow real guest registration from the public raffle detail page when `Raffle::canAcceptParticipants()` is true, without conflating this slice with future ticket/number ownership or public auth.

## Scope

### In Scope
- Add a minimal public submission flow from `/raffles/{id}` for guest `name` + normalized `email`.
- Persist one registration/contact per raffle per normalized email in a dedicated entry record.
- Keep a future-safe nullable `user_id` (or equivalent link point) without building public login.

### Out of Scope
- Public authentication, account linking UX, or profile management.
- Draw numbers, tickets, payments, quantities, notifications, funding/capacity logic, draw execution, or lifecycle redesign.

## Capabilities

### New Capabilities
- `public-raffle-participation-entry`: Guest registration submission, validation, persistence, duplicate handling, and confirmation from the public raffle detail page.

### Modified Capabilities
- `public-raffle-detail`: Replace read-only participation messaging with conditional entry form/feedback when participation is open.
- `raffle-participation-lifecycle`: Require submission paths to re-check `canAcceptParticipants()` and reject closed/ineligible entries.

## Approach

Use a dedicated participation-entry aggregate keyed to raffle. Treat the record as registration/contact only — not a ticket, chance, or number. Gate UI and server acceptance through `canAcceptParticipants()`, and enforce one entry per raffle per normalized email.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/web.php` | Modified | Add public POST entry route beside detail route. |
| `app/Http/Controllers/Public/RaffleController.php` | Modified | Handle form display, submit, and feedback state. |
| `resources/views/public/raffles/show.blade.php` | Modified | Render conditional form or closed-state messaging. |
| `app/Models/Raffle.php` | Modified | Add relation/access helpers as needed; keep eligibility canonical. |
| `database/migrations/*` | New | Create dedicated participation entry storage. |
| `tests/Feature/Raffles/*` | Modified/New | Cover open entry, duplicates, validation, and closed rejection. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Entry model drifts into ticket semantics | Med | Spec registration as contact-only, separate from future number allocation. |
| Duplicate identity ambiguity | Med | Normalize email and enforce per-raffle uniqueness. |
| Stale page submits after close | Low | Re-check `canAcceptParticipants()` server-side. |

## Rollback Plan

Remove the POST route, entry UI, and participation-entry persistence artifacts; keep existing read-only detail behavior and existing lifecycle rules intact.

## Dependencies

- Existing published-detail route and `Raffle::canAcceptParticipants()` lifecycle contract.

## Success Criteria

- [ ] A public visitor can submit `name` + `email` from `/raffles/{id}` only while participation is open.
- [ ] A raffle cannot store more than one registration for the same normalized email.
- [ ] Closed/ineligible raffles reject submission without introducing public auth or ticket semantics.
