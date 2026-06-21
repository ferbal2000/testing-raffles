# Design: Admin Raffle Edit Basic

## Technical Approach

Implement the edit/update slice by extending the existing admin raffle HTTP surface instead of introducing new abstractions. `App\Http\Controllers\Admin\RaffleController` will gain conventional `edit()` and `update()` actions, `routes/admin.php` will register protected `GET /raffles/{raffle}/edit` and `PATCH /raffles/{raffle}` routes, and Blade views will keep the current Tailwind-first admin style plus Spanish translation keys. The update flow will preserve the current `datetime-local` + `Y-m-d\TH:i` contract, allow blank values to persist as `null`, redirect to `admin.raffles.index`, and set a dedicated update-success flash. This matches the proposal and the `admin-raffle-edit` / `admin-raffle-list` delta specs.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Controller shape | New edit controller/request classes; extend `Admin\RaffleController` | Extend `Admin\RaffleController` | The admin raffle slice already lives in one controller with conventional resource-style routes. Adding `edit`/`update` keeps the surface predictable and minimizes review size. |
| Validation contract | New `FormRequest`; inline validation matching create | Inline `$request->validate()` matching create | The codebase has no `app/Http/Requests` pattern yet, and this slice reuses the exact two-field contract already proven by `store()`. A new request class would add structure without enough complexity to justify it. |
| Form reuse | Extract shared partial now; duplicate create markup for edit | Duplicate the two-field markup in `edit.blade.php` for now | The form is only two fields plus actions. Extracting a partial would also force refactoring `create.blade.php`, increasing scope for a narrow edit slice. Accepting small duplication is the lower-risk choice until broader raffle CRUD exists. |
| Status mutability | Block `published`/`closed`; allow all persisted statuses | Allow `draft`, `published`, and `closed` | `Raffle` lifecycle rules only govern `publish()`/`close()` and do not couple status to availability edits. Blocking non-draft rows here would invent a new business rule outside the approved scope. |

## Data Flow

    Index row edit link ──> GET /raffles/{raffle}/edit ──> edit Blade form
             │                                               │
             └────────────── PATCH /raffles/{raffle} <───────┘
                                      │
                                      ├── validate `starts_at` / `ends_at`
                                      ├── blank input => null
                                      ├── `$raffle->update([...])`
                                      └── redirect to index with update flash

On validation failure, Laravel redirects back to the edit URL with errors and old input. On success, only `starts_at` and `ends_at` are updated; `status` and all other fields remain untouched.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `routes/admin.php` | Modify | Add protected edit/update routes in both host-aware route branches next to the current index/create/store routes. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add `edit(Raffle): View` and `update(Request, Raffle): RedirectResponse` using the existing validation and redirect style. |
| `resources/views/admin/raffles/index.blade.php` | Modify | Add a per-row edit action and render a scoped update-success flash alongside the existing create-success flash logic. |
| `resources/views/admin/raffles/edit.blade.php` | Create | Render the two-field edit form with existing values, old input precedence, inline errors, and cancel/save actions. |
| `lang/es/admin-raffles.php` | Modify | Add edit page labels, edit action copy, and update success flash text following the current Spanish translation structure. |
| `tests/Feature/Raffles/AdminRaffleEditTest.php` | Create | Cover auth protection, form rendering, validation failure, nullable update behavior, and allowed status updates. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Modify | Assert row-level edit links and scoped update flash rendering. |

## Interfaces / Contracts

```php
Route::get('/raffles/{raffle}/edit', [RaffleController::class, 'edit'])->name('admin.raffles.edit');
Route::patch('/raffles/{raffle}', [RaffleController::class, 'update'])->name('admin.raffles.update');

$validated = $request->validate([
    'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
    'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
]);

$raffle->update([
    'starts_at' => $validated['starts_at'] ?? null,
    'ends_at' => $validated['ends_at'] ?? null,
]);
```

Form contract:
- Fields: `starts_at`, `ends_at`
- Input type: `datetime-local`
- Value source: `old()` first, otherwise `$raffle->starts_at?->format('Y-m-d\TH:i')` / `ends_at`
- Success flash key: `admin.raffles.update_success`

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | None | The change is request/view orchestration over existing model behavior. |
| Integration | Admin edit/update flow | Add Pest feature tests for guest redirect/401 JSON, edit form rendering, invalid input redirect with errors + old input, blank-to-null persistence, successful updates for `draft`/`published`/`closed`, and index flash/link rendering. |
| E2E | None | No browser/E2E harness exists; verification stays in Laravel feature tests via `bin/test` later. |

## Migration / Rollout

No migration required. This slice reuses existing nullable `starts_at` / `ends_at` columns and current raffle lifecycle behavior.

## Open Questions

- [ ] None blocking. A future change may revisit whether published/closed raffles should become immutable.
