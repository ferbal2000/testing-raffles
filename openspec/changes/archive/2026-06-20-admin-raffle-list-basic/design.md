# Design: Admin Raffle List Basic

## Technical Approach

Implement a narrow admin resource index slice on the existing admin host by adding a protected `GET /raffles` route, a dedicated `App\Http\Controllers\Admin\RaffleController@index`, and a Blade-first `resources/views/admin/raffles/index.blade.php` view. The controller will read persisted `Raffle` records only, render either a minimal table or an explicit empty state, and reuse the current `auth:admin` boundary and login redirect behavior defined by `routes/admin.php` and `bootstrap/app.php`.

## Architecture Decisions

### Decision: Establish the admin raffle pattern with a dedicated controller

| Option | Tradeoff | Decision |
|-------|----------|----------|
| Route closure with inline query | Fewer files now, but mixes route definition and data loading | Rejected |
| Dedicated `Admin\RaffleController@index` | Slightly more structure, but sets the pattern for later create/edit slices | Chosen |

**Rationale**: The codebase already isolates auth concerns in controllers and keeps admin routing thin. Starting with a dedicated controller makes later admin resource slices additive instead of refactoring work.

### Decision: Keep query logic inside the controller for this first read-only slice

| Option | Tradeoff | Decision |
|-------|----------|----------|
| Add a model scope/repository | Reusable, but introduces abstraction for a single simple query | Rejected for now |
| Query `Raffle::query()` directly in `index()` | Simple and local, but less reusable | Chosen |

**Rationale**: The listing only needs deterministic ordering and a read-only field subset. A direct controller query matches the current small-codebase style and avoids premature domain API growth.

### Decision: Optimize the view for the existing narrow layout

| Option | Tradeoff | Decision |
|-------|----------|----------|
| Expand layout/navigation first | Better long-term screen real estate, but violates slice scope | Rejected |
| Keep a minimal table/empty state within the shared layout | Tighter UI, but respects current boundaries | Chosen |

**Rationale**: `resources/views/components/layouts/app.blade.php` centers content in a `max-w-4xl` shell. The first index should therefore keep columns to `id`, `status`, `starts_at`, `ends_at`, and one timestamp, with safe placeholders for null values.

## Data Flow

    Browser ── GET /raffles ──> routes/admin.php (`auth:admin`)
       │                               │
       │ guest                         └──> redirectGuestsTo() → `admin.login`
       └── authenticated admin ──> Admin\RaffleController@index
                                           │
                                           └──> Raffle query (ordered) ──> Blade view

The controller will load raffles in deterministic newest-first order (`latest('id')` or equivalent), then pass the collection to the view. The Blade template renders a table when records exist; otherwise it renders an explicit empty state. Nullable `starts_at` and `ends_at` stay nullable in the UI and display a neutral placeholder instead of inferred data.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `routes/admin.php` | Modify | Add protected `GET /raffles` named `admin.raffles.index` inside the existing `auth:admin` group(s). |
| `app/Http/Controllers/Admin/RaffleController.php` | Create | Add `index()` to load ordered raffle rows and return the admin index view. |
| `resources/views/admin/raffles/index.blade.php` | Create | Render page heading, minimal raffle table, and explicit empty state. |
| `lang/es/home.php` or `lang/es/admin-raffles.php` | Modify/Create | Add Spanish UI copy for the page title/description/empty state following current translation usage. |
| `tests/Feature/Raffles/AdminRaffleIndexTest.php` | Create | Cover protected access, row rendering, sparse values, and empty state on the admin host. |

## Interfaces / Contracts

```php
// Route contract
Route::get('/raffles', [RaffleController::class, 'index'])->name('admin.raffles.index');

// Controller contract
public function index(): View;
```

View data contract:
- `raffles`: `Illuminate\Database\Eloquent\Collection<int, App\Models\Raffle>`
- Required displayed fields per row: `id`, `status`, `starts_at`, `ends_at`, and `created_at`.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | None | Query/view behavior is too thin to justify isolated unit tests. |
| Integration | Admin host access, redirects, rendered rows, null-safe cells, empty state | Add Pest feature coverage using `RefreshDatabase`, admin host helpers, and persisted `Raffle` records. |
| E2E | N/A | No E2E harness is configured in `openspec/config.yaml`. |

## Migration / Rollout

No migration required.

## Open Questions

- [ ] None.
