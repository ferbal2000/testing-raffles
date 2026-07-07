# Design: Admin Status Actions UI

Issue: [#39](https://github.com/ferbal2000/testing-raffles/issues/39)

## Technical Approach

Extend the existing Blade-first admin registration list without introducing a new workflow surface. Status remains a `RaffleRegistrationStatus` enum cast on `RaffleRegistration`; the model gains bounded transition methods for `active -> flagged` and `active -> cancelled`. The admin controller adds two POST handlers under the current `auth:admin` host boundary, then wraps the parent-scoped locked read, transition, and save in one `DB::transaction()` before redirecting back with scoped success/error feedback.

Public registration and realtime runtime stay untouched. The realtime candidate map is documentation-only and already covered by the delta spec.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|---|---|---|---|
| Transition boundary | Add explicit `markForReview()` / `cancel()` plus `canBeFlagged()` / `canBeCancelled()` to `app/Models/RaffleRegistration.php`. | Generic `setStatus()` endpoint or direct controller `update()`. | Keeps domain vocabulary small, rejects invalid transitions server-side, and avoids a generic workflow API. |
| Invalid transition handling | Add a registration-specific domain exception and translate it to one admin-facing error message in the controller. | Reuse `InvalidRaffleTransition` or expose exception text. | Existing exception says “raffle”; user feedback must be clear and product-approved. |
| Routes | Add two explicit POST routes: `/raffles/{raffle}/registrations/{registration}/flag` and `/cancel`. | PATCH with arbitrary status payload. | The route shape documents the only delivered actions and avoids accepting unsupported statuses. |
| Totals | Show active, flagged, cancelled, and total persisted count cards using constrained `withCount()` aliases plus the existing total. | Replace summary with one total, omit flagged rows, or count all non-cancelled together. | Every persisted row remains represented while terminal and review states stay visually separate. |

## Data Flow

```text
Admin row action form
  -> POST admin route
  -> DB::transaction() resolves registration under raffle with lockForUpdate()
  -> RaffleRegistration transition method
  -> save inside the same transaction
  -> redirect back with translated flash/error
  -> registrations page reloads badges, totals, and eligible actions
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Models/RaffleRegistration.php` | Modify | Add status transition helpers and terminal-state guards. |
| `app/Exceptions/InvalidRaffleRegistrationTransition.php` | Create | Domain exception for unavailable registration status mutations. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Select `status`, load constrained active/flagged/cancelled counts plus total persisted count, add transactional flag/cancel handlers and resolver. |
| `routes/admin.php` | Modify | Register matching authenticated admin-host routes in both configured-host branches. |
| `resources/views/admin/raffles/registrations.blade.php` | Modify | Add status column, badges, per-row POST forms for active rows, terminal no-action rendering, active/flagged/cancelled/total summaries, scoped feedback. |
| `lang/es/admin-raffles.php` | Modify | Add Spanish admin labels, action text, confirmations, flashes, status names, and unavailable-action error. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Modify | Cover status display, newest-first preservation, separated counts, successful flag/cancel, terminal rejection, nested raffle guard, and auth behavior. |

## Interfaces / Contracts

- `RaffleRegistration::markForReview(): void` changes only `active` to `flagged`.
- `RaffleRegistration::cancel(): void` changes only `active` to `cancelled`.
- `RaffleRegistration::canBeFlagged()` and `canBeCancelled()` return true only for `active`.
- Controller feedback keys live under `admin.raffles.registration_status_*` and errors use `registration_status`.
- Forms use CSRF-protected POST only; no public route, API endpoint, or payload-driven status setter is introduced.
- Each mutation wraps the `lockForUpdate()` registration lookup, transition method, and save in a single `DB::transaction()`.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Feature | Admin list shows status, eligible actions, terminal no-action rows, active/flagged/cancelled/total summaries. | Extend `AdminRaffleRegistrationsTest` with persisted registrations in each status. |
| Feature | Flag/cancel mutate active registrations and redirect with scoped success. | POST admin routes and assert database status. |
| Feature | Flagged/cancelled rows reject further actions and preserve status. | POST unavailable actions and assert session error plus unchanged row. |
| Feature | Public participation remains unchanged. | Keep existing public tests untouched; no new public assertions unless regression appears during TDD. |

## Migration / Rollout

No migration required; `status` already exists with `active`, `flagged`, and `cancelled` values.

## Open Questions

None.
