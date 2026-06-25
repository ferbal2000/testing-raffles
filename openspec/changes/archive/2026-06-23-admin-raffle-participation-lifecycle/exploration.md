## Exploration: admin-raffle-participation-lifecycle

### Current State
Raffles currently have two separate concerns in code: a persisted status lifecycle (`draft`, `published`, `closed`) and optional availability metadata (`starts_at`, `ends_at`). The `Raffle` model owns lifecycle transitions through `publish()` and `close()`, while admin CRUD only lists, creates, and edits availability fields through `RaffleController` and the admin raffle Blade views. There is no participation lifecycle yet, no participant/payment automation, and no admin action routes beyond create/update.

### Affected Areas
- `app/Models/Raffle.php` — add participation timestamps/reason/admin metadata plus the domain rule such as `canAcceptParticipants()` and manual open/close methods.
- `database/migrations/2026_06_18_160000_create_raffles_table.php` or a follow-up migration — current schema has only `status`, `starts_at`, and `ends_at`; participation fields need persistence.
- `database/factories/RaffleFactory.php` — likely needs helpers for opened/closed participation states.
- `app/Http/Controllers/Admin/RaffleController.php` — current admin raffle actions live here, so manual open/close endpoints would likely land here for the first slice.
- `routes/admin.php` — add protected admin routes for manual participation open/close actions.
- `resources/views/admin/raffles/index.blade.php` — current actions column is the natural place for open/close controls and feedback.
- `lang/es/admin-raffles.php` — existing admin raffle UI copy is Spanish, so new button/flash text would extend that file.
- `tests/Feature/Raffles/*` — lifecycle and admin host tests already define the current contract and would need the new participation coverage later.

### Approaches
1. **Timestamp-based participation lifecycle on `Raffle`** — persist `participation_opened_at`, `participation_closed_at`, `participation_closed_reason`, and nullable `participation_closed_by_admin_id`, then derive participation state from timestamps.
   - Pros: Matches the existing domain decision, keeps dates as non-authoritative metadata, supports future automatic closure, gives audit-friendly history, and keeps `canAcceptParticipants()` as the single rule.
   - Cons: Adds more domain logic to `Raffle`; status and participation lifecycle can drift unless the rules are explicit.
   - Effort: Medium

2. **Status/availability-driven participation without new lifecycle fields** — infer participation eligibility from `status`, `starts_at`, and `ends_at`, or add a simple enum/boolean only.
   - Pros: Lower immediate schema and UI surface.
   - Cons: Conflicts with the captured domain decision, overcouples participation to temporary dates, weakens auditability, and makes future manual vs automatic closure harder.
   - Effort: Low now, High later

### Recommendation
Use the timestamp-based participation lifecycle directly on `Raffle`. Keep `canAcceptParticipants()` independent from `starts_at`/`ends_at` and make it return `true` only when the raffle is `published`, participation has been manually opened, and it has not been closed. For `participation_closed_by_admin_id`, the project already has a real `admins` table, `Admin` model, and dedicated `admin` guard/provider, so the slice can safely persist a **nullable** admin foreign key now rather than deferring it; nullable remains necessary for future non-admin automatic closures.

### Risks
- The model will now carry two lifecycles (`status` and participation), so invalid combinations must be defined clearly to avoid contradictory UI or future automation.
- If overall raffle closure later implies forced participation closure, this slice should avoid baking assumptions that block that follow-up.
- Keeping manual actions inside `RaffleController` is fine for a small slice, but more lifecycle actions could justify extraction later.
- Existing create/edit specs explicitly keep availability editing broad, so proposal/spec work must state that participation actions do not make `starts_at`/`ends_at` authoritative.

### Ready for Proposal
Yes — propose a narrowly scoped slice with admin-only open participation and close participation actions on published raffles, timestamp-derived participation state on `Raffle`, nullable `participation_closed_by_admin_id` referencing `admins`, UI feedback on the admin raffle index, and explicit non-scope for participants, payments, funding automation, and reopen behavior.
