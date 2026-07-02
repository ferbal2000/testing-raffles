# Design: Admin Raffle Publication Management

## Technical Approach

Add an authenticated admin POST action that publishes a draft raffle from the admin index and redirects back with scoped feedback. The action delegates the transition to `Raffle::publish()` so the model remains the lifecycle authority. The index becomes the only publication entry point for this slice; edit-screen publishing, reversals, participation opening, moderation, and extra publication validations remain out of scope.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|----------|--------|-------------------------|-----------|
| Publish endpoint | Add `POST /raffles/{raffle}/publish` named `admin.raffles.publish` inside the existing `auth:admin` admin route groups. | PATCH status update; edit form submit. | Existing lifecycle actions use explicit POST routes (`participation/open`, `participation/close`), and this keeps publication separate from availability editing. |
| Lifecycle authority | Controller calls `$raffle->publish()` and catches `InvalidRaffleTransition`. | Duplicate status checks in controller; add new validations. | Specs require reusing the existing domain transition with no extra publication-blocking validations. |
| Stale rejection feedback | Redirect to `admin.raffles.index` with `withErrors(['publish' => $exception->getMessage()])`. | Let exception bubble; use generic session flash. | Matches current participation rejection handling while keeping publish errors scoped away from create/update/participation success flashes. |
| Publish UI condition | Add a small `Raffle::canPublish(): bool` helper and use it in `publish()` and the Blade row guard. | Compare `$raffle->status` directly in Blade; skip helper. | Existing model already exposes `canOpenParticipation()` and `canCloseParticipation()` for row actions. A helper keeps view logic declarative without adding new lifecycle states or validations. |
| Copy | Add Spanish action, confirmation, and success copy under `lang/es/admin-raffles.php`. | Inline Blade strings. | Existing admin raffle UI resolves labels through the language file. |

## Data Flow

```text
Admin index row ── POST /raffles/{raffle}/publish ── RaffleController::publish()
       │                                                     │
       │                                                     ├─ $raffle->publish()
       │                                                     ├─ success flash: admin.raffles.publish_success
       │                                                     └─ publish error bag on invalid transition
       └──────────────────── redirect back to admin index ────────────────────
```

Successful publication changes only `raffles.status` from `draft` to `published`. Participation timestamps, registrations, winners, moderation state, and reversal behavior are untouched.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `routes/admin.php` | Modify | Register the publish POST route in both admin host branches, near existing raffle lifecycle routes. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add `publish(Raffle $raffle): RedirectResponse`, catch `InvalidRaffleTransition`, and return scoped success/error feedback. |
| `app/Models/Raffle.php` | Modify | Add `canPublish(): bool` and have `publish()` use it before force-filling `published`. |
| `resources/views/admin/raffles/index.blade.php` | Modify | Render publish error feedback and a CSRF-protected confirmed publish form only when `$raffle->canPublish()`. |
| `lang/es/admin-raffles.php` | Modify | Add publish action, confirmation, and success copy. |
| `tests/Feature/Raffles/AdminRafflePublicationTest.php` | Create | Cover protected route, success, stale rejection, public visibility, and participation invariants. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modify | Cover draft-only publish control and scoped publish feedback on the index. |
| `tests/Feature/Raffles/RaffleLifecycleTest.php` | Modify | Cover `canPublish()` if the helper is introduced. |

## Interfaces / Contracts

- Route: `POST /raffles/{raffle}/publish`, admin host only, `auth:admin`, route model binding.
- Success flash key: `admin.raffles.publish_success`.
- Error key: `publish` in the Laravel validation error bag.
- Model helper: `Raffle::canPublish(): bool` returns true only for persisted draft-state checks needed by the UI/domain guard; `publish()` still calls `ensureIsPersisted()` before checking it.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Model | `canPublish()` mirrors draft-only lifecycle eligibility. | Extend `RaffleLifecycleTest`; keep current `publish()` transition tests. |
| Feature route/controller | Admin can publish draft; guest cannot; non-draft/stale submissions reject without mutation; participation timestamps remain unchanged; published raffle becomes publicly resolvable. | New focused Pest feature file using existing admin host helpers and `bin/test`. |
| Feature view | Index shows publish form only for drafts, includes confirmation, and renders scoped publish success/error without unrelated flashes. | Extend `AdminRaffleIndexTest`. |
| E2E | Not applicable. | No browser E2E harness exists. |

Strict TDD order: write failing model/helper test first, then route/controller tests, then index rendering tests, implementing the minimum production change after each red test. Run `bin/test` sequentially; prior project memory notes parallel `bin/test` can race database migrations.

## Migration / Rollout

No migration required. Roll out as one small admin-only slice. Rollback removes the route, controller action, Blade form/error block, language keys, helper, and tests; existing domain lifecycle remains intact.

## Risks

- Action density on the index: keep the button compact and draft-only.
- Stale submissions: rely on `Raffle::publish()` plus scoped error feedback.
- Helper misuse: keep `canPublish()` as a query-style helper only; do not add new statuses, date gates, or participation coupling.

## Open Questions

None.
