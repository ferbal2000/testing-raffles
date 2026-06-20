## Exploration: admin-raffle-create-basic

### Current State
The admin surface already has authenticated routing on the admin host, a dedicated `App\Http\Controllers\Admin\RaffleController@index`, and a Blade/Tailwind raffle index at `GET /raffles`. The raffle domain persists only `status`, `starts_at`, and `ends_at`, and new `Raffle` records are always normalized to `draft` during creation. The current schema and tests explicitly allow nullable `starts_at` and `ends_at`, and the admin index already renders missing values safely with placeholders.

### Affected Areas
- `routes/admin.php` — would add protected `GET /raffles/create` and `POST /raffles` routes on the existing admin host boundary.
- `app/Http/Controllers/Admin/RaffleController.php` — best place to extend the current admin raffle HTTP flow with `create()` and `store()`.
- `app/Models/Raffle.php` — creation must rely on the existing draft-normalization behavior and current fillable fields only.
- `database/migrations/2026_06_18_160000_create_raffles_table.php` — confirms `starts_at` and `ends_at` are nullable in the persisted schema.
- `resources/views/admin/raffles/index.blade.php` — likely needs the create entry point and optional success feedback after redirect.
- `resources/views/admin/raffles/create.blade.php` — would host the Blade/Tailwind form for the new slice.
- `lang/es/admin-raffles.php` — should extend the existing Spanish admin raffle translations for labels, actions, validation-adjacent copy, and success messaging.
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — existing index behavior constrains redirect expectations and nullable date rendering.
- `tests/Feature/Raffles/*` — this slice would add creation-focused feature coverage without touching lifecycle actions.

### Approaches
1. **Extend the existing `RaffleController` with inline validation** — keep `create()` and `store()` in the current controller and validate via `$request->validate()` inside `store()`.
   - Pros: Matches the current codebase pattern (`AuthenticatedSessionController` already validates inline); smallest file count for a narrow two-field create slice; easy to keep scope focused on draft creation.
   - Cons: Validation logic will likely be duplicated once the next edit slice arrives; controller responsibilities grow faster.
   - Effort: Low

2. **Extend the existing `RaffleController` with a dedicated form request** — keep conventional `create()`/`store()` in `RaffleController`, but move validation into a new admin raffle request class.
   - Pros: Cleaner separation between HTTP orchestration and validation; easier to reuse when the edit slice arrives; keeps controller actions thin while staying within Laravel conventions.
   - Cons: Introduces a new project pattern that does not exist yet; slightly more upfront structure for a form that currently only maps nullable date fields.
   - Effort: Medium

### Recommendation
Use the existing `App\Http\Controllers\Admin\RaffleController` with conventional `create()` and `store()` actions, a dedicated admin Blade/Tailwind form, Spanish translations under `admin-raffles`, and redirect back to `admin.raffles.index` after success. For this slice, keep `starts_at` and `ends_at` optional/nullable because that matches the current migration, model behavior, lifecycle spec, and index rendering; do not introduce new required-date or cross-field rules yet.

Prefer inline validation first unless the proposal intentionally wants to establish the reusable request-object pattern ahead of the edit slice. The codebase currently has no `FormRequest` usage, so inline validation is the most consistent minimal step; a form request is reasonable but not necessary for this basic creation slice.

### Risks
- The create UX is intentionally sparse because the domain currently exposes only `starts_at` and `ends_at`; stakeholders must accept an admin form that creates a draft raffle without title, description, or lifecycle actions.
- If the slice adds success feedback after redirect, the index page or shared layout will need a small flash-message pattern that does not exist yet.
- Date parsing/format expectations for HTML inputs must be defined carefully in proposal/spec work so the form stays aligned with Laravel validation and current immutable datetime casts.
- Adding stricter rules such as `ends_at >= starts_at` or mandatory dates would expand scope beyond current domain behavior and should be deferred unless explicitly promoted.

### Ready for Proposal
Yes — propose a narrow admin-authenticated draft creation flow with `GET /raffles/create`, `POST /raffles`, optional `starts_at` / `ends_at`, redirect back to `admin.raffles.index`, and no lifecycle, edit, or broader admin-navigation expansion.
