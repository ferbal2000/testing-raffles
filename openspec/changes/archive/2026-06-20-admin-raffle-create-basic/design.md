# Design: Admin Raffle Create Basic

## Technical Approach

Extend the existing admin raffle HTTP slice instead of introducing a new module boundary. `App\Http\Controllers\Admin\RaffleController` will gain conventional `create()` and `store()` actions, `routes/admin.php` will register protected `GET /raffles/create` and `POST /raffles`, and Blade views will keep the existing Tailwind-first admin UI. The create form will only handle nullable `starts_at` and `ends_at`, persist through `Raffle::query()->create(...)`, and rely on the model boot hook to normalize status to `draft`. This matches the proposal and the `admin-raffle-create` / `admin-raffle-list` delta specs.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Validation location | Inline controller validation; new FormRequest | Inline `$request->validate()` in `store()` | The codebase already validates inline in `AuthenticatedSessionController` and has no `app/Http/Requests` pattern yet. This keeps the slice small and consistent. |
| Availability input contract | Free-text dates; `date`; `datetime-local` | HTML `datetime-local` inputs posting `Y-m-d\TH:i` strings | It gives a clear admin input format without changing the existing datetime storage/casts. Tests can lock one explicit contract while leaving later date-only work out of scope. |
| Success feedback scope | Global layout flash; index-local flash key | A dedicated create-success flash rendered only in `admin.raffles.index` | The spec asks for minimal scoped feedback and explicitly avoids broader admin navigation/layout changes. |

## Data Flow

Admin index → create CTA → `GET /raffles/create` → form submit → `POST /raffles`
→ `RaffleController@store` validation
→ `Raffle::query()->create(['starts_at' => ?, 'ends_at' => ?])`
→ `Raffle::booted()` forces `draft`
→ redirect to `admin.raffles.index` with create-success flash.

On validation failure, Laravel redirects back to `/raffles/create` with errors and old input; blank strings are normalized to `null` before persistence.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `routes/admin.php` | Modify | Add authenticated create/store raffle routes next to the existing index route. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add `create(): View` and `store(Request): RedirectResponse`, inline validation, null normalization, persistence, and redirect flash. |
| `resources/views/admin/raffles/create.blade.php` | Create | Render the two-field admin form, field errors, old input, and submit/cancel actions with existing inline utility styling. |
| `resources/views/admin/raffles/index.blade.php` | Modify | Add a create CTA and render the scoped post-create flash without changing broader page structure. |
| `lang/es/admin-raffles.php` | Modify | Add Spanish copy for create labels, helper text, actions, and success feedback. |
| `tests/Feature/Raffles/AdminRaffleCreateTest.php` | Create | Cover auth protection, form render, nullable submit, invalid submit, and successful redirect/create behavior. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modify | Assert the create CTA and scoped success flash behavior on the index. |

## Interfaces / Contracts

```php
$validated = $request->validate([
    'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
    'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
]);

$payload = collect($validated)
    ->map(fn (mixed $value) => $value === '' ? null : $value)
    ->all();
```

Form contract:
- Fields: `starts_at`, `ends_at`
- Input type: `datetime-local`
- Submitted format under test: `Y-m-d\TH:i`
- Persistence: blank → `null`; valid values → stored through Eloquent datetime casting; status is never accepted from the request.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | No new unit slice | Reuse existing lifecycle/model coverage for draft normalization. |
| Integration | Admin create request/response flow | Add Pest feature tests for guest redirects/401 JSON, form rendering, validation errors with old input, null persistence, and successful draft creation redirect. |
| E2E | None | No browser/E2E harness exists in this repo. |

## Migration / Rollout

No migration required. This slice uses the existing nullable `starts_at` / `ends_at` columns and current raffle status behavior.

## Open Questions

- [ ] None blocking. Future date-only / Argentina display semantics remain intentionally deferred to a separate change.
