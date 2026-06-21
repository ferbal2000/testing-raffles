# Proposal: Admin Raffle Edit Basic

## Intent

Enable admins to update an existing raffle's availability window without expanding lifecycle policy or broader CRUD scope.

## Scope

### In Scope
- Add protected admin edit/update routes for existing raffles.
- Allow editing only `starts_at` and `ends_at` with the current nullable `datetime-local` `Y-m-d\TH:i` contract.
- Add an index edit entry point, edit form copy/translations, and scoped update success feedback.

### Out of Scope
- Lifecycle actions, status restrictions, or new business rules such as draft-only editing.
- Cross-field validation (`ends_at >= starts_at`), required dates, date-only display/schema changes, audit, roles, and broader admin UX restructuring.

## Capabilities

### New Capabilities
- `admin-raffle-edit`: Admin-host edit form and update flow for persisted raffle availability fields, explicitly allowed for `draft`, `published`, and `closed` raffles.

### Modified Capabilities
- `admin-raffle-list`: Add a per-row edit action and render update-success feedback after a successful edit redirect.

## Approach

Extend `App\Http\Controllers\Admin\RaffleController` with `edit` and `update`, keep routes conventional (`GET /raffles/{raffle}/edit`, `PATCH /raffles/{raffle}`), reuse the create slice's validation/input contract, and add a dedicated Blade edit view following existing Tailwind v4 utility and Spanish translation conventions.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/admin.php` | Modified | Add protected edit/update routes |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Add `edit` and `update` actions |
| `resources/views/admin/raffles/index.blade.php` | Modified | Add edit action and update flash surface |
| `resources/views/admin/raffles/edit.blade.php` | New | Add edit form for `starts_at` and `ends_at` |
| `lang/es/admin-raffles.php` | Modified | Add edit/update labels and flash copy |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Future stakeholders may want published/closed raffles immutable | Med | Document current behavior as minimal and revisitable |
| Create/edit markup duplication may increase maintenance | Low | Keep scope small now; refactor later if CRUD surface grows |

## Rollback Plan

Remove the new routes, controller actions, edit view, list edit action, and update flash/translation wiring to restore the current read/create-only admin raffle surface.

## Dependencies

- Existing admin authentication behavior and current `admin-raffle-list` / `admin-raffle-create` specs.

## Success Criteria

- [ ] Authenticated admins can open an edit form for an existing raffle from the index.
- [ ] Valid nullable `starts_at` / `ends_at` updates persist for `draft`, `published`, and `closed` raffles and redirect with scoped success feedback.
- [ ] Invalid datetime-local input returns validation errors without adding new date business rules.
