# Tasks: Public Guest Participation Entry

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 420-560 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 data + entry flow tests → PR 2 public UI/copy + detail assertions |
| Delivery strategy | chained PRs |
| Chain strategy | stacked-to-main |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Add registration persistence and POST behavior with duplicate/closed-path coverage | PR 1 | Base TBD after user chooses chain strategy; keep tests in same slice |
| 2 | Add detail-page form, flash/error states, and copy updates | PR 2 | Depends on PR 1; finish with public detail assertions |

## Phase 1: Foundation RED

- [x] 1.1 Create `tests/Feature/Raffles/PublicRaffleParticipationEntryTest.php` for accepted entry, normalized-email duplicate, closed rejection, stale-page rejection, and validation errors.
- [ ] 1.2 Update `tests/Feature/Raffles/PublicRaffleDetailTest.php` to fail for open-form visibility, closed no-form state, and no ticket/number language.

## Phase 2: Foundation GREEN

- [x] 2.1 Create `database/migrations/*_create_raffle_registrations_table.php` with `raffle_id`, nullable `user_id`, `name`, normalized `email`, timestamps, and unique `['raffle_id','email']`.
- [x] 2.2 Create `app/Models/RaffleRegistration.php` and `database/factories/RaffleRegistrationFactory.php` as contact-only registration records; no ticket/number fields.
- [x] 2.3 Modify `app/Models/Raffle.php` to add only `registrations(): HasMany`, keeping `canAcceptParticipants()` as the canonical eligibility rule.

## Phase 3: Entry Flow GREEN

- [x] 3.1 Modify `routes/web.php` to add `POST /raffles/{raffle}/participation` named `public.raffles.participation.store` on the public host and fallback branch.
- [x] 3.2 Extend `app/Http/Controllers/Public/RaffleController.php` to normalize email before inline validation, re-resolve public raffle, re-check `canAcceptParticipants()`, and persist idempotently with friendly duplicate/unavailable flashes.
- [x] 3.3 REFACTOR controller flow only after GREEN to keep registration/contact semantics isolated from future number or ticket behavior.

## Phase 4: Public UI GREEN

- [ ] 4.1 Update `resources/views/public/raffles/show.blade.php` to render the guest form, validation feedback, and success/info flashes only when participation is open.
- [ ] 4.2 Update `lang/es/public-raffles.php` with form labels, CTA, duplicate confirmation, unavailable submission, and validation-adjacent public copy without ticket wording.
- [ ] 4.3 REFACTOR view copy/layout so closed raffles show friendly unavailable messaging and open raffles preserve current metadata blocks.

## Phase 5: Spec Compliance REFACTOR

- [ ] 5.1 Review `tests/Feature/Raffles/PublicRaffleParticipationEntryTest.php` and `tests/Feature/Raffles/PublicRaffleDetailTest.php` against all change-spec scenarios before implementation handoff.
- [x] 5.2 Update `openspec/changes/public-guest-participation-entry/tasks.md` execution checkboxes during apply; keep `bin/test` as the later verification command, not part of planning.
