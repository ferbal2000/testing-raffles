## Exploration: admin raffle edit basic

### Current State
The admin raffle surface already uses a dedicated `App\Http\Controllers\Admin\RaffleController` with protected `index`, `create`, and `store` routes in `routes/admin.php`. The current admin UI is Blade-first with Tailwind utility classes, Spanish translations from `lang/es/admin-raffles.php`, and feature coverage for auth protection, empty/list states, create validation, and create success flash behavior. `App\Models\Raffle` persists `starts_at` and `ends_at` as nullable immutable datetimes while lifecycle status remains independent: new records normalize to `draft`, `publish()` allows only `draft -> published`, and `close()` allows only `published -> closed`.

### Affected Areas
- `routes/admin.php` — add protected edit/update routes such as `GET /raffles/{raffle}/edit` and `PATCH /raffles/{raffle}`.
- `app/Http/Controllers/Admin/RaffleController.php` — extend the existing admin raffle controller with `edit` and `update` actions that mirror the current `datetime-local` validation contract.
- `resources/views/admin/raffles/index.blade.php` — add a per-row edit entry point from the existing raffle list.
- `resources/views/admin/raffles/create.blade.php` — useful reference for the current admin form structure, field naming, and Tailwind conventions.
- `resources/views/admin/raffles/edit.blade.php` — likely new Blade form for editing the same nullable availability fields.
- `lang/es/admin-raffles.php` — expand the existing translation file with edit labels, submit/cancel copy, and an update success flash.
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — extend index coverage to prove the edit link is visible from the admin list.
- `tests/Feature/Raffles/AdminRaffleEditTest.php` — likely new feature coverage for auth protection, form rendering, validation errors, nullable updates, and success redirect/flash.
- `database/factories/RaffleFactory.php` — keep using `published()` and `closed()` states in tests instead of direct status assignment.

### Approaches
1. **Extend the existing admin raffle controller** — keep the current controller/resource shape and add `edit`/`update` with a dedicated edit form.
   - Pros: Matches the current list/create slice structure; keeps routes conventional; smallest addition for the next independent slice; easy to cover with feature tests.
   - Cons: Validation may stay inline unless a later slice extracts shared request rules; create/edit form markup may duplicate a little.
   - Effort: Low

2. **Split edit/update into separate HTTP abstractions now** — add extra controller/request/view composition to share create/edit behavior more aggressively from the start.
   - Pros: Reduces future duplication if the admin raffle surface grows quickly; can centralize validation earlier.
   - Cons: Adds structure before the app has enough CRUD breadth to justify it; heavier review surface for a narrow slice.
   - Effort: Medium

### Recommendation
Use **Approach 1**: extend the existing `Admin\RaffleController` with conventional `edit` and `update` actions, add a protected edit form that keeps the current `datetime-local` `Y-m-d\TH:i` contract, and place a small edit action on each index row. Redirect back to `admin.raffles.index` with a scoped update flash such as `admin.raffles.update_success`.

For status scope, the safest minimal behavior is to allow editing `starts_at` and `ends_at` for all persisted statuses (`draft`, `published`, `closed`) in this slice. Today the domain explicitly treats availability fields as basic persisted data and does not couple them to lifecycle transitions, so restricting editability to `draft` would invent a new business rule not present in the model or specs. That recommendation should be made explicit in the proposal/spec instead of being implied silently.

### Risks
- Restricting edits to `draft` would add a business rule that does not exist today and would expand scope into lifecycle policy.
- Allowing edits for `published` and `closed` is technically consistent with the current domain, but stakeholders may later decide those states should be immutable.
- Reusing the create validation contract avoids scope creep, but it also intentionally defers cross-field rules such as `ends_at >= starts_at`.
- If create/edit markup is copied instead of partially shared, the next slice may need a small cleanup pass to avoid duplicated form maintenance.

### Ready for Proposal
Yes — propose a narrow admin edit/update slice that reuses the existing controller and UI conventions, keeps `starts_at`/`ends_at` nullable `datetime-local` fields only, adds an index edit action plus scoped update flash, and states explicitly that lifecycle rules and richer date business rules remain deferred.
