# Exploration: admin-status-actions-ui

Issue: [#39](https://github.com/ferbal2000/testing-raffles/issues/39)

### Current State
Registration status is already persisted and type-enforced on `RaffleRegistration` through the `RaffleRegistrationStatus` enum (`active`, `flagged`, `cancelled`), with `active` as the default and public registration writes intentionally omitting status. The admin raffle registrations page is still read-only: it shows `name`, normalized `email`, `created_at`, and linked-account presence, but no status badge, no moderation actions, and no server-side transition entry points. Admin access is already protected by the existing `auth:admin` host boundary; there is no separate policy layer in the current codepath.

### Affected Areas
- `app/Models/RaffleRegistration.php` — likely needs explicit transition helpers or a controlled status setter boundary.
- `app/Http/Controllers/Admin/RaffleController.php` — current registrations action is read-only; new POST/PATCH endpoints would live here or in a sibling admin controller.
- `routes/admin.php` — needs new admin-host routes for registration status actions.
- `resources/views/admin/raffles/registrations.blade.php` — the UI surface for status display and per-row actions.
- `lang/es/admin-raffles.php` — admin-facing copy for status labels, action buttons, success/error flashes, and empty/blocked states.
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — existing read-only assertions must be replaced/extended with status-display and action-flow coverage.
- `openspec/specs/admin-raffle-participation-list/spec.md` — currently constrains this page to read-only; it needs a delta to permit bounded status controls.
- `openspec/specs/raffle-registration-status/spec.md` — currently says no admin UI/actions are introduced; it needs a delta to define the operational slice.
- `openspec/specs/realtime-update-candidate-map/spec.md` — status changes are observable admin-state changes, so the candidate map likely needs a new entry if the UI becomes actionable.

### Approaches
1. **Minimal moderation surface** — show the current status on the admin registrations page and add only the two useful state changes from the default `active` state: flag and cancel.
   - Pros: smallest useful slice, matches the existing enum vocabulary, and stays close to the current read-only list.
   - Cons: no restore/reactivation path yet; canceled/flagged rows may become terminal until a later slice.
   - Effort: Medium

2. **Full tri-state control** — allow admins to set any registration directly to `active`, `flagged`, or `cancelled` from the list.
   - Pros: reversible and simple to explain in the UI.
   - Cons: broader UI/state surface, easier to drift into a generic workflow tool, and more likely to exceed the intended first slice.
   - Effort: Medium

3. **Badge-only visibility first** — show status on the list but keep actions out of scope for now.
   - Pros: very small code change and low risk.
   - Cons: does not satisfy the requested admin-operational slice.
   - Effort: Low

### Recommendation
Choose **Minimal moderation surface**. Keep the first slice focused on the admin registrations page: expose the current registration status clearly, add bounded admin actions for the two meaningful exception states (`flagged` and `cancelled`), and enforce transitions server-side with the existing model-boundary style. Do not add audit history, bulk actions, notifications, or realtime runtime wiring; only update the realtime candidate map if the slice makes the status changes visible enough to be considered an observable admin-state change.

### Risks
- A generic “set any status” API would be easy to implement but would blur the domain and increase review surface without clear product need.
- If the UI uses approval-style language like confirm/reject, it may drift away from the actual persisted vocabulary (`active/flagged/cancelled`).
- Adding state actions without a clear transition rule could create no-op or invalid writes and make the admin page misleading.

### Ready for Proposal
Yes — propose a narrow admin moderation slice centered on the registrations page, with status visibility plus bounded server-enforced actions.
