## Exploration: raffle-lifecycle-basic

### Current State
The repository is still at the Laravel foundation stage. Public and admin HTTP surfaces are host-separated, admin/public identity is isolated, and the active test suite only proves routing, translations, health, and auth-boundary behavior. There is no raffle domain code yet: no `raffles` table, no raffle model, no domain module under `app/Modules/*`, and no lifecycle tests. The archived `raffles-platform` artifacts describe a broader lifecycle (`draft -> published -> closed -> drawn`) plus entries, draw, and audit concerns, but that scope was intentionally too large and must not be revived here.

### Affected Areas
- `bootstrap/app.php` — shows the app currently boots only web/admin route surfaces with shared Laravel middleware.
- `routes/admin.php` — the eventual minimal admin lifecycle surface would land here if HTTP actions are introduced.
- `config/auth.php` — confirms raffle lifecycle changes should rely on the existing `admin` boundary instead of redefining identity.
- `database/migrations/` — currently has only identity/framework tables, so raffle persistence would start here.
- `tests/Feature/Auth/*` and `tests/Feature/Routing/*` — prove foundation constraints already in place and define the current verification style.
- `openspec/specs/platform-foundation/spec.md` — keeps raffle lifecycle explicitly out of the current source-of-truth foundation scope.
- `openspec/specs/admin-identity-boundary/spec.md` — establishes the admin boundary the lifecycle slice should build on.

### Approaches
1. **Domain-first lifecycle slice** — Add a minimal raffle persistence/model boundary and prove `draft -> published -> closed` rules in tests before exposing a very small admin surface.
   - Pros: Best fit for strict TDD, keeps the slice intentionally small, avoids coupling business rules to controllers, and preserves room for later entries/draw/audit work.
   - Cons: Requires choosing the first domain shape (`App\Models\Raffle` vs `app/Modules/Raffles/*`) before any existing raffle code can guide the pattern.
   - Effort: Medium

2. **HTTP-first admin scaffold** — Start with admin routes/controllers/views and let lifecycle rules live close to request handlers.
   - Pros: Faster to demo manually, aligns with the current Blade-first app shape, and may feel simpler in a greenfield repo.
   - Cons: Higher risk of mixing transport and domain rules, harder to keep review size controlled, and more likely to create throwaway UI while the lifecycle contract is still moving.
   - Effort: Medium

### Recommendation
Choose **Domain-first lifecycle slice**. The first change should only establish a single minimal cycle: an admin-owned raffle record that starts in `draft`, can be published, and can later be closed. `starts_at` / `ends_at` may exist as nullable data needed for future availability, but the first proposal should avoid automatic time-driven transitions, public entry behavior, draw logic, audit trails, reopen flows, or policy-heavy published edits. If an HTTP surface is needed at all, keep it to the thinnest admin endpoints necessary to exercise the lifecycle.

### Risks
- The repo documents a planned `app/Modules/*` architecture, but no module implementation exists yet, so the proposal must explicitly choose whether the first lifecycle slice begins with plain Laravel model/action structure or establishes the first module boundary.
- Adding admin CRUD screens, validation branches, or audit logging too early would recreate the oversized `raffle-lifecycle-core` problem and threaten the 400-line review budget.
- `starts_at` / `ends_at` can quietly expand scope if the slice tries to define scheduling policy instead of treating them as simple stored fields.
- Open question: should the first slice prove lifecycle rules only at the domain/test level, or also include a minimal admin HTTP path in the same change?

### Ready for Proposal
Yes — tell the user the next proposal should lock scope to a minimal admin-managed raffle lifecycle with only `draft`, `published`, and `closed`, explicitly defer draw/cancel/audit/reopen concerns, and answer one design question up front: domain-only first or domain plus a very thin admin HTTP path.
