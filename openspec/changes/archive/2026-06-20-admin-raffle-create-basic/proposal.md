# Proposal: Admin Raffle Create Basic

## Intent

Add the first admin raffle creation flow so authenticated admins can create draft raffles from the existing admin surface without expanding into edit, publish, close, or scheduling rules.

## Scope

### In Scope
- Add protected `GET /raffles/create` and `POST /raffles` routes on the existing admin host.
- Add a minimal admin form for optional `starts_at` and `ends_at` values.
- Persist new raffles through current domain behavior, then redirect to `admin.raffles.index` with scoped success feedback.

### Out of Scope
- Edit, publish, close, delete, or broader raffle lifecycle management.
- Required dates, `ends_at >= starts_at`, or automatic scheduling rules.

## Capabilities

### New Capabilities
- `admin-raffle-create`: Admin-authenticated raffle creation form and submit flow for creating draft raffles with optional availability dates.

### Modified Capabilities
- `admin-raffle-list`: Add a create entry point and post-create success feedback on the existing index.

## Approach

Extend `App\Http\Controllers\Admin\RaffleController` with conventional `create()` and `store()` actions. Prefer inline validation to match current project patterns. Add a Blade/Tailwind form, keep Spanish translations in `lang/es/admin-raffles.php`, rely on the model/domain to normalize status to `draft`, and keep `starts_at` / `ends_at` nullable.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/admin.php` | Modified | Add create/store routes behind existing admin auth. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Add `create` and `store` flow. |
| `resources/views/admin/raffles/create.blade.php` | New | Render minimal create form. |
| `resources/views/admin/raffles/index.blade.php` | Modified | Add create CTA and success flash rendering. |
| `lang/es/admin-raffles.php` | Modified | Add labels, button, and success copy. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Sparse form feels incomplete | Med | Document that lifecycle and richer fields are deferred. |
| Date input/validation mismatch | Med | Keep accepted formats explicit in specs/tests. |
| Flash UI widens index scope | Low | Limit feedback to a small success message only. |

## Rollback Plan

Remove the new routes, controller actions, create view, index CTA/flash, and related translations; the existing read-only index remains intact.

## Dependencies

- Existing `admin-raffle-list`, `admin-session-authentication`, and `raffle-lifecycle` specs.

## Success Criteria

- [ ] Authenticated admins can open `GET /raffles/create` and submit `POST /raffles` on the admin host.
- [ ] A successful submit creates a raffle in `draft`, preserves nullable dates, and redirects to `admin.raffles.index` with scoped success feedback.
