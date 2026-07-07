# Design: Live Registration Count Visibility Foundation

Persisted registration counts will be exposed through the existing Laravel request/response Blade flow only. The change adds narrow count loading where each page already resolves its raffle data, then renders neutral copy in existing views without changing list behavior, registration mutations, or realtime runtime.

## Technical Approach

Use Eloquent `withCount('registrations')`/`loadCount('registrations')` at the affected page boundaries. Public detail gets a persisted count on the resolved raffle for display only when `Raffle::canAcceptParticipants()` is true, including a neutral zero-count state that does not overstate participation. Admin registration list keeps loading the same newest-first registration collection and adds a separate persisted count summary for the current raffle.

No broadcaster, Echo/Reverb, event class, listener, channel, polling, websocket, or auto-refresh behavior is introduced. Counts update only after a new HTTP request/redirect renders the page again.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|---|---|---|---|
| Public count loading | Add `withCount('registrations')` inside `resolvePublicRaffle()` or an equivalent detail-only query path. | Add counts to `catalogRaffles()` or define a broad global scope. | Keeps public catalog unchanged and avoids broad query behavior changes. |
| Admin list summary | Add `loadCount('registrations')` in `Admin\RaffleController::registrations()` while preserving the existing eager-loaded `registrations` relation order/select. | Use `$raffle->registrations->count()` only, or alter the registration list query. | A persisted count attribute is explicit for the summary; the list behavior remains newest-first and read-only. |
| Copy boundary | Store copy in existing Spanish translation files and render via Blade translation helpers, with explicit zero-count copy that stays friendly without implying odds, capacity, eligibility, ranking, ticket quantity, or guaranteed benefit. | Inline Blade copy or hide the count when zero. | Matches current localization pattern, keeps tests stable around visible strings, and avoids misleading social-proof copy while participation is open. |
| Realtime map | Keep the active delta under this change until archive; during archive, merge it into `openspec/specs/realtime-update-candidate-map/spec.md` as documentation-only candidate rows. | Edit the stable source-of-truth spec during apply, add runtime events, or omit the map update. | The surfaces are delivered observable behavior, but event labels remain “not implemented”; stable specs are updated by archive, not prematurely during apply. |

## Data Flow

```text
Public GET /raffles/{raffle}
  -> Public\RaffleController::show()
  -> resolve published raffle with registrations_count
  -> Blade renders count only if canAcceptParticipants()

Admin GET /raffles/{raffle}/registrations
  -> Admin\RaffleController::registrations()
  -> load newest-first registrations + registrations_count
  -> Blade renders summary + unchanged table/empty state
```

## File Changes

| File | Action | Description |
|---|---|---|
| `app/Http/Controllers/Public/RaffleController.php` | Modify | Load `registrations_count` for public detail only; do not touch catalog queries. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add count loading for the registration-list page without changing registration eager-load select/order. |
| `resources/views/public/raffles/show.blade.php` | Modify | Render friendly social-proof count inside the open participation section only. |
| `resources/views/admin/raffles/registrations.blade.php` | Modify | Render read-only current raffle summary before table/empty state. |
| `resources/views/admin/raffles/index.blade.php` | Preserve | No intended changes; existing count and entry point remain. |
| `lang/es/public-raffles.php` | Modify | Add public count copy. |
| `lang/es/admin-raffles.php` | Modify | Add admin summary copy. |
| `openspec/changes/2026-07-07-live-registration-count-visibility-foundation/specs/realtime-update-candidate-map/spec.md` | Preserve through apply | Keep the documentation-only candidate map delta active for this change; do not edit the stable source-of-truth spec during apply. |
| `openspec/specs/realtime-update-candidate-map/spec.md` | Modify during archive only | Archive merges the candidate map delta into the stable source-of-truth spec; keep labels marked not implemented. |

## Interfaces / Contracts

- Public detail boundary: `GET /raffles/{raffle}` returns HTML. Response may include `registrations_count` copy only when the raffle is public and `canAcceptParticipants()` is true.
- Admin boundary: `GET /raffles/{raffle}/registrations` returns authenticated admin HTML. Response includes a read-only summary count and the same newest-first registration list/empty state.
- Runtime contract: no new HTTP endpoints, JSON contracts, events, channels, listeners, jobs, JS refresh loops, or mutation controls.
- Copy guidance: acceptable copy says people are already registered/participating when the count is above zero, and uses neutral zero-count wording when no one is registered yet. It must avoid capacity, odds, eligibility, ranking, ticket quantity, payment, draw status, or guaranteed benefit.

## Testing Strategy

| Layer | What to Test | Approach |
|---|---|---|
| Feature: public detail | Open participation shows persisted non-zero and zero counts; closed participation hides it; forbidden copy absent. | Add failing Pest tests in `PublicRaffleDetailTest.php` using existing public host helpers and persisted registrations. |
| Feature: admin registrations | Summary shows non-zero and zero counts while table order and empty state remain unchanged. | Extend `AdminRaffleRegistrationsTest.php`; assert newest-first list and forbidden management/export/ticket copy remain absent. |
| Regression: admin index | Existing index count still appears. | Keep/extend `AdminRaffleIndexTest.php` only if implementation touches index. |
| Realtime boundary | No runtime artifacts are introduced. | Add assertions by absence in relevant feature responses; during apply/verify review, run an explicit static/file-diff or architecture-level check that no broadcaster, Echo/Reverb, event class, listener, channel, polling, websocket, auto-refresh, or related runtime artifact was added. |

Strict TDD apply order: write failing feature assertions first, implement minimal controller/view/translation changes, then refactor only after green.

## Migration / Rollout

No migration required. Rollback removes the count loading, Blade summary/social-proof blocks, translation keys, and candidate-map delta. Persisted registrations and existing admin index counts remain unaffected.

## Open Questions

None.
