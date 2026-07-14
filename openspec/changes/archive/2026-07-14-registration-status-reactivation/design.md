# Design: Registration Status Reactivation

Issue: [#41](https://github.com/ferbal2000/testing-raffles/issues/41)

## Technical Approach

Extend the existing bounded registration-status pattern with one correction path: `flagged -> active`. `RaffleRegistration` owns eligibility and transition behavior; `RaffleController` reuses its parent-scoped `DB::transaction()` plus `lockForUpdate()` helper; an explicit authenticated admin POST route invokes the action; and the Blade list renders a CSRF-protected restore form only for flagged rows. Cancelled registrations remain terminal. No payload-driven status setter, schema change, public behavior, or realtime runtime is introduced.

## Architecture Decisions

| Decision | Choice | Alternative rejected | Rationale |
|---|---|---|---|
| Domain API | Add `canBeRestored(): bool` and `restoreToActive(): void`; restoration accepts only `Flagged` and otherwise throws `InvalidRaffleRegistrationTransition`. | Direct controller assignment or generic `setStatus()`. | Keeps transition rules server-owned and preserves `cancelled` terminal semantics. |
| HTTP contract | Add `POST /raffles/{raffle}/registrations/{registration}/restore`, named `admin.raffles.registrations.restore`, in both existing route branches with numeric constraints on both `{raffle}` and `{registration}`. | PATCH with a status payload. | Exposes only the approved action and follows current explicit route conventions. |
| Mutation consistency | Add `restoreRegistration()` and delegate to the existing `transitionRegistration()` helper. | Separate unlocked update. | Parent relation scoping prevents cross-raffle mutation; transaction and row lock serialize stale concurrent actions. |
| UI language | Show restore/clear-review wording only for `canBeRestored()` rows, with a dedicated success flash and the existing unavailable-action error. | Generic “change status” controls. | Makes the correction intent explicit without implying workflow management. |
| Realtime map | Sync the delta into `openspec/specs/realtime-update-candidate-map/spec.md` during archive only. | Event, broadcast, listener, or transport implementation. | The capability is future-only documentation. |

## Data Flow

```text
Flagged-row form -> authenticated POST route -> parent-scoped locked lookup
  -> restoreToActive() -> save -> redirect with success/error
  -> page reload shows active badge, counts, and active-only actions
```

## File Changes

| File | Action | Description |
|---|---|---|
| `app/Models/RaffleRegistration.php` | Modify | Add flagged-only restore eligibility and transition. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add restore handler using the existing transactional helper. |
| `routes/admin.php` | Modify | Register the numeric, nested admin POST route in both host branches. |
| `resources/views/admin/raffles/registrations.blade.php` | Modify | Render restore action/flash for flagged rows; cancelled rows remain actionless. |
| `lang/es/admin-raffles.php` | Modify | Add bounded action, confirmation, and success copy. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Modify | Add RED feature coverage for rendering, transition, rejection, scoping, and auth. |
| `openspec/specs/realtime-update-candidate-map/spec.md` | Modify at archive | Document restore as a future update candidate without runtime transport. |

## Interfaces / Contracts

- `RaffleRegistration::canBeRestored(): bool` is true only for `Flagged`.
- `RaffleRegistration::restoreToActive(): void` changes only `Flagged` to `Active`.
- Restore redirects to `admin.raffles.registrations.index`; success uses `admin.raffles.registration_status_restore_success`; unavailable transitions use the existing `registration_status` error key.
- Missing or cross-raffle registrations remain 404 through `$raffle->registrations()`; unauthenticated requests retain existing HTML redirect / JSON 401 behavior.

## Testing Strategy

| Layer | Planned RED coverage |
|---|---|
| Feature | Flagged rows show only restore; active rows keep flag/cancel; cancelled rows show no mutation. |
| Feature | Restore changes flagged to active and returns scoped success; active/cancelled restore attempts preserve status and return the existing scoped error. |
| Feature | Cross-raffle restore is 404 and unchanged; guest HTML redirects, guest JSON is 401; GET and nonnumeric `{raffle}` or `{registration}` routes do not match; the rendered form includes a CSRF token and the restore route remains in the `web` middleware group. |
| Security evidence | If missing/invalid CSRF rejection must be proven, use a non-bypassed middleware test or manual/browser verification; normal Laravel feature POSTs do not prove rejection because CSRF is bypassed in the testing environment. |
| Regression | Existing newest-first order, counts, public behavior, and request/redirect rendering remain unchanged. |

## Threat Matrix

| Boundary | Applicability | Safe / failure behavior | Planned RED tests |
|---|---|---|---|
| HTTP admin mutation routing | Applicable | Only authenticated, CSRF-protected, parent-scoped POST requests with numeric `{raffle}` and `{registration}` parameters mutate; stale/ineligible and malformed-parameter requests fail with no change. | Success, active/cancelled rejection, cross-raffle 404, guest HTML/JSON, GET rejection, separate nonnumeric `{raffle}` and `{registration}` cases, rendered CSRF token, and confirmation that the route remains in the `web` middleware group. Prove negative CSRF rejection only with non-bypassed middleware or manual/browser verification. |
| Documentation-like paths | N/A — no file classification or execution boundary. | No executable-path behavior changes. | None. |
| Git repository selection | N/A — no Git invocation. | No repository selection exists. | None. |
| Commit state | N/A — no commit automation. | No index/worktree behavior exists. | None. |
| Push state | N/A — no push automation. | No ref resolution exists. | None. |
| PR commands | N/A — no PR automation. | No command composition exists. | None. |

## Migration / Rollout

No migration or feature flag required. Rollback removes the restore route, handler, model methods, UI/copy, and tests; persisted statuses remain valid.

## Open Questions

None.
