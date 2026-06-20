# Proposal: Admin Raffle List Basic

## Intent

Provide a first authenticated admin raffle index so admins can view persisted raffles without using ad-hoc database inspection. This establishes the admin resource pattern without expanding raffle business rules.

## Scope

### In Scope
- Protected admin-host `GET /raffles` index route named for the admin raffle resource.
- Dedicated admin raffle controller action for list orchestration.
- Minimal Blade-first table and empty state using existing raffle fields only.

### Out of Scope
- Create, edit, publish, close, participants, draws, winners, audit, roles, admin CRUD, password recovery, and new validation/business rules.
- Broader admin navigation or dashboard restructuring; this slice may exist as a direct page without introducing a wider nav system.

## Capabilities

### New Capabilities
- `admin-raffle-list`: Protected admin raffle index behavior for viewing persisted raffles on the admin host.

### Modified Capabilities
- None.

## Approach

Use a dedicated controller-backed index: add `admin.raffles.index` on conventional `GET /raffles`, query existing `Raffle` records in deterministic order, and render a simple Blade table or empty state. Reuse existing `auth:admin` protection and keep the page intentionally narrow to fit the current Blade/layout baseline.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/admin.php` | Modified | Add protected admin raffle index route. |
| `app/Http/Controllers/Admin/RaffleController.php` | New | Add `index()` for admin raffle listing. |
| `resources/views/admin/raffles/index.blade.php` | New | Render minimal table and empty state. |
| `app/Models/Raffle.php` | Modified | Reuse/read existing raffle fields and ordering scope only if needed. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Current centered layout compresses table content | Med | Keep columns minimal and markup simple. |
| Sparse raffle fields make the list feel thin | Low | Limit display to status and date/timestamp fields already persisted. |

## Rollback Plan

Remove the admin raffle index route, controller, and view; revert any list-only model/query helper added; keep existing admin auth and home flow unchanged.

## Dependencies

- Existing `auth:admin` boundary and current `raffles` table schema.

## Success Criteria

- [ ] Authenticated admins can open `GET /raffles` on the admin host and see a raffle index page.
- [ ] Guests are still redirected to the admin login route for the protected raffle index.
- [ ] The page shows either a minimal raffle table or an explicit empty state without introducing lifecycle actions.
