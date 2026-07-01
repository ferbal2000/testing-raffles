# Design: Admin Raffle Participation List

## Technical Approach

Add a protected admin-host read page inside the existing `Admin\RaffleController` surface and keep the feature Blade-first. The raffle index will gain a registrations link for every row and load `registrations_count` in the same query. The new page will read from `Raffle::registrations()` only, render newest-first rows, and expose a linked-account signal derived from existing registration data without introducing ticket, payment, draw, or management concepts.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Controller surface | New controller vs existing `Admin\\RaffleController` | Keep the action in `Admin\\RaffleController` | Current admin raffle CRUD and participation lifecycle already live there, so the read-only list stays discoverable and follows existing route naming and middleware patterns. |
| Linked-user display | Join full `users` details vs show registration-owned signal only | Show a boolean/text signal from `user_id` presence | The spec asks for an existing linked-user signal, not user profile expansion. Avoiding a `users` join preserves the domain boundary and keeps the list about registrations only. |
| Index count loading | Per-row count queries vs eager aggregate | Use `withCount('registrations')` in the raffle index query | This keeps the optional count cheap and avoids N+1 when the link label or metadata includes counts. |

## Data Flow

1. Authenticated admin requests `GET /raffles` on the admin host.
2. `Admin\RaffleController@index` loads raffles with `registrations_count` and renders the index link for each raffle.
3. Admin follows `GET /raffles/{raffle}/registrations`.
4. `Admin\RaffleController@registrations` resolves the bound raffle, loads `registrations()->latest('id')`, and renders either the table or an explicit empty state.

```text
Admin browser
  └─ GET /raffles
       └─ RaffleController@index
            └─ Raffle::query()->withCount('registrations')->latest('id')

Admin browser
  └─ GET /raffles/{raffle}/registrations
       └─ RaffleController@registrations
            └─ bound Raffle -> load(['registrations' => latest('id')])
                 └─ Blade table / empty state
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `routes/admin.php` | Modify | Add authenticated admin-host GET route named `admin.raffles.registrations.index`. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add `registrations(Raffle $raffle): View`; update `index()` to use `withCount('registrations')`. |
| `resources/views/admin/raffles/index.blade.php` | Modify | Add per-row registrations entry point and optional count label in the existing actions cluster. |
| `resources/views/admin/raffles/registrations.blade.php` | Create | Render raffle context, read-only registrations table, linked-user signal, and empty state. |
| `lang/es/admin-raffles.php` | Modify | Add labels/copy for the new action, table headers, linked-user signal, and empty state. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modify | Cover registrations entry point visibility and persisted count rendering on the index. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Create | Cover auth protection, newest-first rendering, empty state, and sparse `user_id` behavior. |

## Interfaces / Contracts

```php
// routes/admin.php
Route::get('/raffles/{raffle}/registrations', [RaffleController::class, 'registrations'])
    ->name('admin.raffles.registrations.index');

// app/Http/Controllers/Admin/RaffleController.php
public function registrations(Raffle $raffle): View
{
    $raffle->load([
        'registrations' => fn ($query) => $query->latest('id'),
    ]);

    return view('admin.raffles.registrations', ['raffle' => $raffle]);
}
```

View contract: each row shows `name`, `email`, `created_at`, and a derived linked-user label based on `user_id !== null`.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | None planned | Current behavior is route/controller/view composition, so feature coverage gives better signal. |
| Integration | Admin-host auth and page rendering | Pest feature tests asserting guest redirect / JSON 401, newest-first rows, empty state, and no invented management controls. |
| E2E | None | Not available in project tooling. |

## Migration / Rollout

No migration required.

## Open Questions

- [ ] None.
