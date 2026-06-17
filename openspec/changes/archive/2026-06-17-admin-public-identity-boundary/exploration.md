## Exploration: admin-public-identity-boundary

### Current State
The application is a Laravel 13 scaffold with Blade-first rendering, optional Vite assets, and host-separated public/admin entry points. `bootstrap/app.php` loads `routes/web.php` and `routes/admin.php` under the same `web` middleware stack, while `config/auth.php` defines only one implemented guard/provider pair (`web` + `users`) and explicitly documents it as the public website identity. `config/session.php` still uses one global Laravel session configuration, and the current test suite proves host separation plus the documented public-only boundary, but it does not yet prove admin/public authentication or session isolation. There is no real-time infrastructure yet: no broadcasting config, no channels, no events, and `resources/js/app.js` is empty.

### Affected Areas
- `bootstrap/app.php` — admin routes are registered inside the shared `web` middleware stack today.
- `config/auth.php` — only the public `User` / `users` boundary is implemented; admin is still marked as planned.
- `config/session.php` — session cookie/storage behavior is global and will need an explicit isolation decision.
- `app/Models/User.php` — currently the public-site identity model and should remain so unless the boundary decision is reversed.
- `database/migrations/0001_01_01_000000_create_users_table.php` — documents `users` as the public identity table in the source-of-truth foundation slice.
- `routes/web.php` / `routes/admin.php` — host separation already exists and should become the basis for auth boundary enforcement.
- `tests/Feature/Auth/PublicIdentityBoundaryTest.php` — proves the current public-only contract; Phase 2 should add guard-isolation coverage beside it.
- `openspec/specs/platform-foundation/spec.md` — source of truth says `User` / `users` means public identity until separate admin identity is implemented.
- `openspec/changes/archive/2026-06-16-raffles-platform/tasks.md` — archived Phase 2 intent should be reconciled with the newer decision that Laravel `users` / `User` remains the public identity boundary.

### Approaches
1. **Bounded identity slice, defer real-time infrastructure** — Implement only the admin/public identity split now, and record real-time as a cross-cutting constraint for later raffle/entry/draw slices.
   - Pros: Best alignment with archived Phase 2 intent, keeps review scope controlled, preserves Blade-first delivery, avoids choosing transport before domain events exist.
   - Cons: A later slice must still add event publication and client subscriptions.
   - Effort: Medium

2. **Identity slice plus Reverb/Echo foundation now** — Add separate identities and also introduce Laravel broadcasting primitives, frontend Echo bootstrapping, and Reverb-ready configuration.
   - Pros: Uses Laravel’s official real-time path for one-way updates; works with Blade pages through small JS islands or later Livewire listeners.
   - Cons: Scope creep; there are no meaningful domain events or authenticated channel rules yet; adds ops/frontend complexity before the product surfaces exist.
   - Effort: High

3. **Identity slice plus polling-oriented UI foundation** — Keep Blade-first and prepare later updates through Livewire polling or manual polling instead of event broadcasting.
   - Pros: Lower operational overhead than WebSockets; easy to add for simple counters and dashboards.
   - Cons: Inefficient for cross-user updates, weaker fit for future admin/public live state propagation, and likely becomes throwaway once domain events mature. SSE is possible in Laravel, but it has the same “too early / too narrow” problem here.
   - Effort: Medium

### Recommendation
Choose **Approach 1**. The right boundary for `admin-public-identity-boundary` is: keep `App\Models\User` and `users` as the public website identity because that is already the implemented source of truth, then add a separate admin identity (`Admin`, `admins`, admin guard/provider/broker, and explicit route/middleware/session isolation). Do **not** broaden this slice into real-time delivery. Instead, carry a cross-cutting architecture note into the next proposal/spec work: Blade-first remains viable, and the preferred future push model should be Laravel broadcasting with Reverb/Echo once real domain events (raffle state changes, participant count changes, draw completion) actually exist. Polling may be acceptable for low-value read models, but it should not be the default architecture decision.

### Risks
- The archived Phase 2 task text still assumes a separate public identity table/model; proposal/spec work must resolve that explicitly by keeping Laravel `users` / `User` as public identity and introducing only `admins` / `Admin` for admin identity.
- Session isolation can look “done” because hosts differ, but guard keys, password brokers, remember-me behavior, and any future shared root-domain cookie strategy still need explicit tests.
- Adding real-time infrastructure too early would inflate the slice beyond the 400-line review budget before any domain event payloads are stable.

### Ready for Proposal
Yes — tell the user the next phase should scope this change to separate admin identity + auth/session boundary only, while recording real-time updates as a future cross-cutting constraint with Reverb/Echo as the likely default once event-producing domain slices arrive.
