## Exploration: public-guest-participation-entry

### Current State
The public flow currently stops at read-only discovery: `GET /` lists published raffles and `GET /raffles/{id}` shows a published raffle detail page. Participation availability is only messaging, driven by `Raffle::canAcceptParticipants()`. The domain has raffle lifecycle and admin participation open/close timestamps, but there is no participant-entry model, table, route, controller action, or test coverage for public submissions yet. Existing persisted public identity support is limited to the default `users` table (`name`, `email`, `password`), and no current domain object suggests phone is required.

### Affected Areas
- `app/Models/Raffle.php` — remains the canonical eligibility rule via `canAcceptParticipants()` and likely gains a participation relation.
- `app/Http/Controllers/Public/RaffleController.php` — currently renders read-only detail; would need entry-form state or extracted composition.
- `routes/web.php` — public host needs a write route for guest participation submission.
- `resources/views/public/raffles/show.blade.php` — current detail page has no form or feedback states.
- `database/migrations/*.php` — a new persistence table is required because no participant-entry storage exists today.
- `tests/Feature/Raffles/PublicRaffleDetailTest.php` — existing read-only assertions must evolve for conditional entry UI.
- `tests/Feature/Raffles/*` — new feature/domain coverage is needed for submission, validation, and closed-state rejection.

### Approaches
1. **Dedicated raffle participation entry aggregate** — Add a first-class persistence table for raffle entries keyed to a raffle, with guest contact fields now and nullable future `user_id` for later auth linkage.
   - Pros: Clean future path to authenticated users, preserves raffle lifecycle boundaries, supports real submissions now without fake auth scaffolding.
   - Cons: Introduces a new table/model/spec surface in this slice.
   - Effort: Medium

2. **Store guest contact directly on raffles or temporary session-only flow** — Avoid a new aggregate and keep entry data attached to existing raffle/session structures.
   - Pros: Lower short-term code volume.
   - Cons: Wrong domain shape, poor auditability, blocks multiple entries per raffle, and creates migration debt when real users arrive.
   - Effort: Low now / High later

### Recommendation
Use **Dedicated raffle participation entry aggregate**. Keep the slice narrow: public detail page shows a simple guest form only when `canAcceptParticipants()` is true, submission persists guest `name` and `email`, and the record reserves a nullable `user_id` for future authenticated public users. Do not include phone in the first slice because the current domain and existing user/admin identities only establish `name` and `email`.

### Risks
- If the spec does not define whether one guest can submit multiple times per raffle, validation/uniqueness rules may be ambiguous.
- If the UI posts to a numeric raffle route without re-checking `canAcceptParticipants()` server-side, stale pages could accept invalid submissions.
- Reusing the existing `users` table prematurely would blur “registered public user” vs “guest participant” and make future auth harder.

### Ready for Proposal
Yes — propose a narrow slice centered on a new raffle participation entry record, public detail form gating by `canAcceptParticipants()`, guest `name` + `email` capture only, and a future-safe nullable `user_id` without public authentication.
