# Proposal: Admin Raffle Participation Lifecycle

## Intent

Separate raffle publication/visibility from participation eligibility so admins can open or close entries without redefining raffle status or relying on `starts_at` / `ends_at`.

## Scope

### In Scope
- Persist `participation_opened_at`, `participation_closed_at`, `participation_closed_reason`, and nullable `participation_closed_by_admin_id`.
- Add `Raffle::canAcceptParticipants()` as the canonical participation eligibility rule.
- Add admin-only manual open/close participation actions for published raffles, plus index feedback.

### Out of Scope
- Participants, ticket purchases, payments, funding calculations, or automatic closure.
- Reopen flow or broad raffle lifecycle redesign.

## Capabilities

### New Capabilities
- `raffle-participation-lifecycle`: Timestamp-based participation state, canonical eligibility checks, and admin audit metadata.

### Modified Capabilities
- `raffle-lifecycle`: Clarify that `published` controls visibility/publication, not participant entry, and that `starts_at` / `ends_at` stay metadata in this slice.
- `admin-raffle-list`: Add admin participation open/close controls and scoped success feedback on the index.

## Approach

Model two lifecycle axes on `Raffle`: status (`draft` / `published` / `closed`) and participation availability. `canAcceptParticipants()` MUST be the only rule for user-entry checks; callers MUST NOT infer eligibility from raw `status` or raw timestamps. Manual close records `admin_closed`, `participation_closed_at`, and admin reference when present. The design MUST leave room for future automatic closures.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Models/Raffle.php` | Modified | Add participation fields, transitions, and `canAcceptParticipants()`. |
| `database/migrations/*raffles*` | Modified | Persist participation lifecycle timestamps/reason/admin audit reference. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modified | Add manual open/close participation actions. |
| `routes/admin.php` | Modified | Add protected admin participation action routes. |
| `resources/views/admin/raffles/index.blade.php` | Modified | Show participation actions and feedback. |
| `lang/es/admin-raffles.php` | Modified | Extend existing admin raffle UI copy. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Status and participation drift | Med | Define valid transitions and use `canAcceptParticipants()` everywhere. |
| Future auto-close conflicts | Med | Keep closure reason/admin audit nullable and non-admin-safe. |

## Rollback Plan

Revert the participation schema fields, remove admin action routes/UI, and restore all entry checks to the pre-slice behavior before releasing dependent participant flows.

## Dependencies

- Existing `admins` table and admin guard for nullable audit linkage.

## Success Criteria

- [ ] Specs state two lifecycle axes and require entry checks through `canAcceptParticipants()` only.
- [ ] Published raffles can be manually opened/closed for participation without making `starts_at` / `ends_at` authoritative.
