## Exploration: realtime-update-candidates-map

### Current State
The app is still Blade-first and host-separated. Admin and public raffle screens already reflect the same underlying raffle record, but only after a full request/redirect cycle. `Raffle` already owns the lifecycle and participation gates (`publish`, `close`, `openParticipation`, `closeParticipation`, `canAcceptParticipants()`), while the admin/public controllers and views render separate slices of the same state. `RaffleRegistration` already exists with a default `active` status foundation, but there is no broadcasting config, no channel definitions, no event classes, and no runtime push transport in the repo yet.

### Affected Areas
- `app/Models/Raffle.php` — source of truth for lifecycle and participation transitions that will eventually drive reactive updates.
- `app/Models/RaffleRegistration.php` — future candidate for registration status/count-driven updates.
- `app/Http/Controllers/Admin/RaffleController.php` — publish/open/close actions that should eventually invalidate admin views.
- `app/Http/Controllers/Public/RaffleController.php` — public catalog, detail, and guest participation flows that should eventually react to state changes.
- `routes/admin.php` / `routes/web.php` — current screen entry points that define the relevant admin/public surfaces.
- `resources/views/admin/raffles/index.blade.php` / `resources/views/admin/raffles/registrations.blade.php` — admin screens most likely to need immediate refresh for status, counts, and list changes.
- `resources/views/public/home.blade.php` / `resources/views/public/raffles/show.blade.php` — public screens most likely to need immediate refresh for visibility, availability, and participation feedback.
- `openspec/specs/*.md` and archived exploration notes — current source for lifecycle, participation, identity-boundary, and realtime-defer decisions.

### Approaches
1. **Candidate map only** — record the screens, transitions, and future event candidates now; do not introduce runtime transport or event wiring yet.
   - Pros: Keeps the slice tiny, reviewable, and aligned with the current deferment of realtime infra.
   - Cons: No user-visible realtime behavior yet.
   - Effort: Low

2. **Candidate map plus domain-event sketch** — document proposed domain events and which views they would refresh, still without implementing broadcasting.
   - Pros: Gives the next proposal a clearer contract for event names and recipients.
   - Cons: Slightly more design overhead; still no runtime value by itself.
   - Effort: Low/Medium

### Recommendation
Choose **Candidate map only** for this slice. Capture the future update triggers as architecture notes for delivered observable behavior: raffle publication, participation open/close, guest registration creation, and counter updates. Defer registration-status realtime candidates until a future slice delivers observable status-change/status-visibility behavior. Treat admin/public screens as read models that will later subscribe to those changes through Laravel Broadcasting with Reverb/Echo, but keep this slice documentation-only.

### Risks
- The realtime boundary can drift if the next proposal does not name the exact screens and transitions that must update together.
- If counts and badges are not defined now, later broadcast payloads may become inconsistent across admin/public views.
- This slice deliberately does not solve delivery latency; it only prevents future retrofit ambiguity.

### Ready for Proposal
Yes — propose a documentation/architecture slice that records the candidate realtime/reactive update map and the future push model boundary, without implementing broadcasting runtime.
