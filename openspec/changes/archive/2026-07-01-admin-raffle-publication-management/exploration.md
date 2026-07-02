## Exploration: admin-raffle-publication-management

### Current State
The raffle domain already supports publication at the model level: `App\Models\Raffle::publish()` transitions `draft -> published`, `RaffleStatus` already includes `draft`, `published`, and `closed`, and the factory/test suite already uses published/closed states. Admin HTTP wiring exists for listing, creating, editing availability, and opening/closing participation, but there is no admin route, controller action, or UI control to publish a draft raffle. Public visibility already depends on `published`, and participation entry already requires `published` plus an open participation window, so publication is the missing step in the operational flow.

### Affected Areas
- `app/Http/Controllers/Admin/RaffleController.php` — add a publish action that calls the existing domain method and handles invalid transitions.
- `routes/admin.php` — add a protected admin route for publishing a raffle.
- `resources/views/admin/raffles/index.blade.php` — add a draft-only publish action alongside the existing row actions.
- `lang/es/admin-raffles.php` — add publish button text and success/error copy.
- `tests/Feature/Raffles/AdminRaffleIndexTest.php` — cover visibility of the publish action and the post-publish index state.
- `tests/Feature/Raffles/AdminRafflePublicationManagementTest.php` — likely new feature coverage for auth, transition success, and invalid transition rejection.
- `app/Models/Raffle.php` — probably no domain change needed unless the slice wants an explicit `canPublish()` helper for cleaner UI branching.

### Approaches
1. **Index-only publish action** — add `POST /raffles/{raffle}/publish` and show the button only for draft rows in the admin index.
   - Pros: Smallest coherent slice; matches the existing admin index as the control center; keeps publication separate from edit and participation actions.
   - Cons: Adds one more action to the crowded index row; publish remains unavailable from the edit form.
   - Effort: Low

2. **Publish from the edit screen** — reuse the edit page as the place to publish a draft raffle.
   - Pros: Keeps lifecycle actions near the data form.
   - Cons: Blurs availability editing with lifecycle control; increases the edit slice; less aligned with the current index-driven admin workflow.
   - Effort: Medium

### Recommendation
Use the **index-only publish action**. The domain already enforces the transition, so this slice should stay thin: wire a protected admin route, call `Raffle::publish()`, redirect back to the index with scoped success feedback, and surface the action only for draft raffles. Do **not** add new lifecycle states, date-based publication rules, or participation changes in this slice.

### Risks
- If the UI shows the action for non-draft rows, the controller will still reject the transition, so the view predicate must stay in sync with the domain.
- There is no dedicated `canPublish()` helper today; adding one would be convenient but is not required and would widen the domain surface.
- The admin index already carries create/update/open/close actions, so the row may become dense; keep the publish control visually minimal.
- Strict TDD means the new route/action should be introduced with feature tests first, but no test run is needed during exploration.

### Ready for Proposal
Yes — propose a narrow admin publish slice that exposes draft-only publication on the admin index, reuses the existing `publish()` domain transition, and leaves participation moderation as the next step in the workflow.
