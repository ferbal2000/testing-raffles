## Exploration: admin raffle list basic

### Current State
The admin surface is now a real authenticated host, not just boundary plumbing. `bootstrap/app.php` redirects unauthenticated admin HTML requests to `route('admin.login')`, `routes/admin.php` already protects admin pages with `auth:admin`, and `AuthenticatedSessionController` provides working login/logout flow proven by `tests/Feature/Auth/AdminSessionAuthenticationTest.php`. The raffle domain already persists sparse records through `App\Models\Raffle` with `id`, `status`, `starts_at`, `ends_at`, and timestamps, but there is still no admin raffle HTTP controller, no admin navigation pattern, and no existing list/table view.

### Affected Areas
- `routes/admin.php` — the protected admin raffle index route should be added here alongside the existing login/logout and home routes.
- `app/Http/Controllers/Admin/Auth/AuthenticatedSessionController.php` — confirms the admin host already has a working redirect target and session flow that the new list page can rely on.
- `app/Models/Raffle.php` — the list will read the existing persisted raffle fields and must not invent new lifecycle rules.
- `database/migrations/2026_06_18_160000_create_raffles_table.php` — shows the currently available columns for a minimal index table: `id`, `status`, `starts_at`, `ends_at`, and timestamps.
- `resources/views/admin/home.blade.php` — demonstrates the current Blade-first admin UI style and minimal authenticated surface.
- `resources/views/components/layouts/app.blade.php` — the shared layout is narrow and centered, so the first table should stay intentionally simple.
- `tests/Feature/Auth/AdminSessionAuthenticationTest.php` — existing admin-host protection behavior should remain intact when the list route is introduced.

### Approaches
1. **Protected route closure/view** — add a single `auth:admin` route that queries raffles inline in `routes/admin.php` and renders a Blade view.
   - Pros: Smallest possible slice; minimal upfront files; fine for a one-page prototype.
   - Cons: Starts the admin raffle area with route-level data access; weak pattern for the next create/edit slices; harder to grow cleanly under strict TDD.
   - Effort: Low

2. **Dedicated controller-backed index** — add a protected `admin.raffles.index` route backed by a dedicated admin raffle controller action that loads raffles and renders a Blade table/empty state.
   - Pros: Establishes the HTTP pattern the later create/edit slices can extend; keeps routing, querying, and view orchestration separate; gives stable naming for future links and tests.
   - Cons: Slightly more structure than the smallest possible list page.
   - Effort: Medium

### Recommendation
Use the **dedicated controller-backed index** now. Even for a list-only slice, this is the right foundation: add a protected admin-host route such as `/raffles`, keep the page Blade-first, render a minimal table plus empty state from the existing raffle fields, and leave all lifecycle mutations out of scope. Starting with a dedicated admin raffle controller now creates the cleanest path for later `create` and `edit` actions without baking query logic into `routes/admin.php`.

The list slice should stay deliberately small: read-only index behavior, existing admin auth only, and no new business rules beyond choosing a deterministic display order for persisted raffles.

### Risks
- The current admin layout is optimized for centered placeholder content, so a table-heavy screen may need careful minimal markup to avoid awkward width/compression.
- Raffle records are intentionally sparse today, so the first admin list may feel thin unless the team accepts an index built around status and date fields only.
- There is no existing admin navigation pattern yet, so the proposal should state whether the list page simply lives at `/raffles` without introducing broader dashboard/navigation scope.

### Ready for Proposal
Yes — propose a first slice limited to a protected admin raffle index page on the existing admin host, with a dedicated controller and Blade table/empty state, explicitly excluding create/edit/actions and any new raffle business rules.
