## Exploration: live-registration-count-visibility-foundation

### Current State
Guest registrations already exist as read-only persisted records with per-raffle uniqueness on normalized email, and the admin raffle index already exposes a persisted registration count via `withCount('registrations')` plus a translatable count label. The admin registrations page shows the stored guest rows newest-first, but it does not surface a summary count. Public screens currently show participation availability and success/duplicate/unavailable feedback, but neither the public catalog nor the public raffle detail page shows registration counts today. The realtime candidate map already treats registration-count visibility and guest-registration creation as delivered-observable behavior, but it explicitly forbids runtime broadcasting in this slice.

### Affected Areas
- `app/Http/Controllers/Admin/RaffleController.php` — already loads registration counts for the admin index and the per-raffle registrations page.
- `resources/views/admin/raffles/index.blade.php` — already shows the registration count and registrations entry point; this is the clearest existing admin count surface.
- `resources/views/admin/raffles/registrations.blade.php` — read-only admin list that could benefit from a small count summary without changing workflow.
- `app/Http/Controllers/Public/RaffleController.php` — public detail and participation flow currently do not load or expose registration counts.
- `resources/views/public/raffles/show.blade.php` — the most likely public screen for a read-only count summary tied to guest participation.
- `resources/views/public/home.blade.php` — optional later extension if the catalog also needs visibility, but likely too broad for the first slice.
- `openspec/specs/public-raffle-participation-entry/spec.md` — confirms the public guest registration model stays contact-only.
- `openspec/specs/admin-raffle-list/spec.md` — already allows a simple registration count on the admin index.
- `openspec/specs/admin-raffle-participation-list/spec.md` — constrains the admin registrations page to read-only visibility.
- `openspec/specs/realtime-update-candidate-map/spec.md` — must remain the guardrail for future observable state changes; any new interactive visibility slice should update it during spec/design.

### Approaches
1. **Admin index + public detail count surfaces** — keep the existing admin count and add a read-only count summary to the public raffle detail page.
   - Pros: smallest useful boundary, uses already-persisted data, and gives both admin and public a visible count without inventing new workflow.
   - Cons: does not touch the catalog card list, so visibility stays focused on the detail page.

2. **Admin index + public catalog + public detail** — expose counts more broadly across both public entry points.
   - Pros: broader visibility and stronger social-proof coverage.
   - Cons: larger scope, more copy/query work, and easier to drift into UI polish instead of foundation work.

### Recommendation
Choose **Admin index + public detail count surfaces**. Treat the current admin index count as the established baseline, then add one new read-only public count surface on the raffle detail page so both hosts have a clear registration-count read model. Keep the slice narrowly about visibility; do not add any realtime transport, event classes, channels, listeners, push subscriptions, or client-side auto-refresh. If later spec/design work introduces an observable interactive state change, update the realtime candidate map in that same future slice.

### Risks
- Count wording can accidentally imply popularity, eligibility, or capacity semantics; the copy must stay neutral and read-only.
- If the public count is added too broadly, the slice can balloon into catalog redesign work.
- Any future reactive refresh work needs to keep this documentation in sync with the realtime candidate map; this slice must not imply runtime broadcasting exists.

### Ready for Proposal
Yes — propose a narrow visibility slice that keeps the existing admin count surface, adds a public raffle-detail count summary, and stays strictly out of runtime realtime infrastructure.
