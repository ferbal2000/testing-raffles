# Proposal: Admin Raffle Participation List

## Intent

Give admins a safe way to inspect existing guest participation registrations for one raffle without expanding into operational workflows the current data model cannot support.

## Scope

### In Scope
- Add an admin-only per-raffle registrations page.
- Show a newest-first read-only list of stored registration fields: name, normalized email, created timestamp, and optional linked-user signal if already present.
- Add a minimal entry point from the admin raffle index, with empty-state copy and protected-route coverage.

### Out of Scope
- Ticket, number, payment, draw, winner, export, notification, capacity, funding, or mutation behavior.
- Filters, bulk actions, notes, statuses, deletion, or audit workflows.

## Capabilities

### New Capabilities
- `admin-raffle-participation-list`: Admin-host read-only visibility into stored raffle registrations for a single raffle.

### Modified Capabilities
- `admin-raffle-list`: Add a per-row entry point to the registrations screen, optionally with a simple registration count.

## Approach

Use the existing admin raffle controller surface and add a protected `GET /raffles/{raffle}/registrations` page. Build a narrow read model from `Raffle::registrations()` and keep the index integration lightweight so the list page owns the registration detail.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/admin.php` | Modified | Add protected registrations route. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Add read-only participation-list action. |
| `app/Models/Raffle.php` | Modified | Support ordered registration read model / count loading. |
| `resources/views/admin/raffles/index.blade.php` | Modified | Add registrations entry point. |
| `resources/views/admin/raffles/` | New | Add per-raffle registrations page. |
| `lang/es/admin-raffles.php` | Modified | Add labels and empty-state copy. |
| `tests/Feature/Raffles/*` | Modified | Cover protected access, list rendering, and index entry point. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| “Manage” scope expands beyond read-only visibility | Med | Keep specs explicit about no mutation/export semantics. |
| Table layout becomes cramped in shared admin UI | Low | Keep columns minimal and reuse existing overflow patterns. |

## Rollback Plan

Remove the admin route, controller action, index link, page, and related copy/tests; existing public registration storage remains unchanged.

## Dependencies

- Existing `public-raffle-participation-entry` data and admin authentication boundary.

## Success Criteria

- [ ] Authenticated admins can open a per-raffle registrations page from the admin raffle index.
- [ ] The page shows existing registrations newest-first with explicit empty state and no mutation/export actions.
