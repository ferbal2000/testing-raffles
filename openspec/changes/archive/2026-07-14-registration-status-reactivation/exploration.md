# Exploration: registration-status-reactivation

Issue: [#41](https://github.com/ferbal2000/testing-raffles/issues/41)

### Current State
The merged admin status-actions slice already established the implementation pattern: `RaffleRegistration` owns bounded transition methods, the admin controller performs locked status mutations inside a `DB::transaction()`, routes are explicit POST endpoints, and the Blade list renders per-row forms only for `active` registrations. The current domain language also draws a hard line between the two terminal states: `flagged` means retained for review, while `cancelled` means annulled/not valid and kept for traceability.

The current specs still reflect that terminal model. `raffle-registration-status` says `flagged` and `cancelled` are terminal with no restore/reactivate, and `admin-raffle-participation-list` says flagged/cancelled rows must not expose further mutation. `realtime-update-candidate-map` already documents status changes as observable admin updates, so any new reactivation behavior would need a follow-up candidate entry.

### Affected Areas
- `app/Models/RaffleRegistration.php` — would need a new bounded restore/reactivate guard and transition method.
- `app/Http/Controllers/Admin/RaffleController.php` — needs a new POST handler for the restore action and the same transaction/lock pattern.
- `routes/admin.php` — needs a new admin-host route for the restore action.
- `resources/views/admin/raffles/registrations.blade.php` — needs a restore affordance only on eligible rows and matching feedback copy.
- `lang/es/admin-raffles.php` — needs labels, confirmation, flash text, and unavailable-action error copy.
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — later tests should cover success, terminal rejection, and list rendering for the new action.
- `openspec/specs/admin-raffle-participation-list/spec.md` — must allow a bounded restore action on the relevant status.
- `openspec/specs/raffle-registration-status/spec.md` — must relax the terminal clause for the chosen source status.
- `openspec/specs/realtime-update-candidate-map/spec.md` — should add the new delivered observable status change if the slice ships.

### Approaches
1. **Flagged -> active only** — add a narrow “restore / clear review” action for flagged rows, keep cancelled terminal.
   - Pros: smallest useful slice, matches the meaning of `flagged` as reviewable, avoids reopening annulled records.
   - Cons: does not let admins undo a mistaken cancellation yet.
   - Effort: Medium

2. **Flagged -> active and cancelled -> active** — expose a generic reactivation action for both terminal states.
   - Pros: fully reversible status management from the list.
   - Cons: expands the domain meaning of `cancelled`, weakens traceability semantics, and pushes the UI toward a generic workflow tool.
   - Effort: Medium

3. **Generic status setter** — let admins pick any status from any row.
   - Pros: straightforward to implement mechanically.
   - Cons: conflicts with the current bounded domain language and would overshoot the requested slice.
   - Effort: Medium

### Recommendation
Choose **Flagged -> active only**. That is the smallest slice that corrects a review decision without collapsing the distinction between “needs review” and “annulled for traceability.” Keep `cancelled` terminal for now. The UI should be a single inline row action on flagged registrations, labeled something like “Restore to active” or “Clear review,” with server-enforced transition checks and a scoped success flash.

### Risks
- The current specs explicitly say restore/reactivate is out of scope, so this slice needs a deliberate spec delta rather than a small implementation tweak.
- If the UI label is too generic, it may read like a generic workflow tool instead of a bounded correction action.
- Allowing `cancelled -> active` now would blur domain meaning and create a harder review surface than the product asks for.

### Ready for Proposal
Yes — propose a narrow admin reactivation slice for `flagged -> active` only, update the relevant specs, and keep cancelled registrations terminal.
