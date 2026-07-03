# Exploration: admin-participation-exceptions-status-foundation

### Current State
`raffle_registrations` stores only `raffle_id`, optional `user_id`, `name`, `email`, and timestamps. Public participation already auto-creates registrations when a raffle is open, and the admin registrations page is read-only with no status controls. The only existing status axes in the app are raffle publication (`draft`/`published`/`closed`) and raffle participation timing on `raffles`; there is no per-registration validity or moderation state yet.

### Affected Areas
- `database/migrations/2026_06_30_150000_create_raffle_registrations_table.php` — needs a new status column if registrations become moderatable.
- `app/Models/RaffleRegistration.php` — natural place for the default-valid rule and future status helpers.
- `database/factories/RaffleRegistrationFactory.php` — should set the default registration state for tests.
- `app/Http/Controllers/Public/RaffleController.php` — should keep creating registrations in the default valid state.
- `resources/views/admin/raffles/registrations.blade.php` — likely later place to surface status, but not required for the first slice.
- `openspec/specs/admin-raffle-list/spec.md` / future `openspec/specs/raffle-registration-status/spec.md` — current admin list spec is explicitly read-only, so status controls need a separate delta.

### Approaches
1. **Default-valid registration status axis** — add a simple `status` column on `raffle_registrations` with default `active` or `valid`, plus a tiny model helper such as `isActive()`.
   - Pros: Minimal, matches “valid by default,” and gives a durable foundation for rare admin exceptions later.
   - Cons: No admin UI or automation yet; the status exists before users can act on it.
   - Effort: Low

2. **Full moderation workflow now** — add `active/flagged/cancelled` plus admin actions and list badges in the same slice.
   - Pros: Immediate operational control.
   - Cons: Too wide for the current objective, and it conflicts with the current OpenSpec boundary that keeps the registrations page read-only.
   - Effort: Medium

3. **Boolean exception flags only** — add one or more boolean/admin-note fields instead of a status axis.
   - Pros: Small schema diff.
   - Cons: Harder to extend when automated analysis and approve/reject behavior arrive; state combinations become ambiguous.
   - Effort: Low now, High later

### Recommendation
Split it. The first slice should be **persistence-only**: add a single default-valid status axis on `raffle_registrations` (prefer `active/flagged/cancelled` over approval jargon), with model/factory support and no admin UI mutation yet. That keeps the slice under review budget and preserves the current read-only admin registrations contract; admin actions and automated analysis should be a follow-up slice.

### Risks
- If the status naming is too approval-oriented now, it may lock the product into premature human-review semantics.
- Adding status to the admin registrations page too early will collide with the existing read-only OpenSpec contract and bloat the review slice.
- Without audit fields, later admin exception actions will need a second schema change.

### Ready for Proposal
Yes — propose the persistence-only foundation first, then a separate admin moderation slice if the product still wants visible flag/cancel controls.
