## Exploration: admin-raffle-participation-list

### Current State
The admin host already exposes a controller-backed raffle index, create/edit flows, and manual participation open/close actions through `App\Http\Controllers\Admin\RaffleController` and `routes/admin.php`. Public guest entry is already live: `Public\RaffleController::storeParticipation()` writes contact-only records into `raffle_registrations` using `Raffle::registrations()` and a per-raffle unique email constraint. Those registrations currently have only `name`, normalized `email`, nullable `user_id`, and timestamps. There is no admin route, query, Blade view, translation copy, or test coverage for viewing or managing those registrations.

### Affected Areas
- `app/Http/Controllers/Admin/RaffleController.php` — current admin raffle HTTP surface; likely place for a new participation-list action unless a separate admin controller is introduced.
- `routes/admin.php` — needs the protected admin-host route for the registrations screen.
- `app/Models/Raffle.php` — already owns `registrations()` and is the natural place for eager loading / count-based read-model support.
- `resources/views/admin/raffles/index.blade.php` — current per-raffle actions area is the natural entry point for a “view registrations” link and maybe a count.
- `resources/views/admin/raffles/` — would host the new admin participation list Blade page.
- `lang/es/admin-raffles.php` — existing admin copy file would need labels, empty-state text, and any scoped feedback for this screen.
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — should prove the new index entry point is visible only where intended.
- `tests/Feature/Raffles/*` — needs new protected-route/read-model coverage for registrations.

### Approaches
1. **Dedicated per-raffle admin participation page** — add a protected route such as `GET /raffles/{raffle}/registrations` that renders one raffle’s registration list with a narrow read model.
   - Pros: Matches the existing controller-backed admin pattern, keeps review scope tight, scales better if later slices add filters/export/removal, and avoids bloating the main raffle index.
   - Cons: Adds a new page and route instead of solving everything inline.
   - Effort: Medium

2. **Inline registrations embedded on the admin raffle index** — load registrations or counts directly into the existing `/raffles` page.
   - Pros: Fewer routes/views in the short term and quick visibility from the existing screen.
   - Cons: The index is already carrying create/edit/open/close actions; embedding registrations risks a crowded page, awkward queries, and higher reviewer cognitive load.
   - Effort: Low now / Medium later

### Recommendation
Use **Dedicated per-raffle admin participation page**. The next proposal should keep scope view-first: add an admin-only registrations screen linked from each raffle row, show a minimal newest-first list of stored contact registrations (`name`, normalized `email`, `created_at`, optional `user_id` presence only if useful), and optionally show a simple count on the raffle index. Keep “manage” deliberately narrow in this slice because the current registration model has no status, notes, assignment, or audit fields that justify broader mutation semantics. A tiny read-only export does **not** belong in the first slice unless the user explicitly needs it, because there is no existing export pattern and it would widen the contract beyond the missing operational visibility layer.

### Risks
- “Manage” is underspecified: today the schema supports viewing contact registrations, but not richer admin workflows such as statusing, notes, ownership, or safe removal audit.
- The shared layout is centered and narrow (`max-w-4xl`), so a registrations table must stay compact or use the same overflow strategy already used by the admin raffle index.
- If the route/query loads full registration collections directly on the index, the page could grow noisy and couple two different admin read models too early.

### Ready for Proposal
Yes — propose a narrowly scoped admin-only participation list change centered on read visibility for `raffle_registrations`, with a per-raffle entry point from the admin raffle index, explicit empty states, and no ticket/number/payment/draw/winner/export/notification/capacity/funding semantics.
