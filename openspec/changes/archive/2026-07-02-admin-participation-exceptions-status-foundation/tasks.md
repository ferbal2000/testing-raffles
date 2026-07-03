# Tasks: Admin Participation Exceptions Status Foundation

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated implementation-code/test changed lines | 180-280 |
| 500-line budget risk | Low |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

This forecast covered the implementation-code/test impact only. The committed PR
also includes OpenSpec planning and archive artifacts, so the total review
surface is larger than the implementation forecast.

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Persist registration status foundation with tests | PR 1 | Keep enum, migration, model, factory, and tests together. |

## Phase 1: RED Tests

- [x] 1.1 In `tests/Feature/Raffles/PublicRaffleParticipationEntryTest.php`, add/extend tests for default `active`, explicit `flagged`, invalid `pending`, and existing-row effective `active` scenarios.
- [x] 1.2 In `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`, assert the read-only admin list still does not show status actions, approval/rejection language, tickets, payments, or winners.

## Phase 2: Persistence Foundation

- [x] 2.1 Create `app/Enums/RaffleRegistrationStatus.php` with backed cases `Active`, `Flagged`, and `Cancelled`.
- [x] 2.2 Create `database/migrations/*_add_status_to_raffle_registrations_table.php` adding non-null `raffle_registrations.status` default `active`, with `down()` dropping only that column.

## Phase 3: Model and Factory GREEN

- [x] 3.1 Update `app/Models/RaffleRegistration.php` to include `status` in `#[Fillable]`, cast it to `RaffleRegistrationStatus`, default raw attributes to `active`, and reject unsupported strings via `RaffleRegistrationStatus::from(...)`.
- [x] 3.2 Update `database/factories/RaffleRegistrationFactory.php` to include default `RaffleRegistrationStatus::Active` while preserving existing raffle/user/name/email defaults.
- [x] 3.3 Verify `app/Http/Controllers/Public/RaffleController.php` remains unchanged for registration writes; do not pass status from the public form flow.

## Phase 4: Verification and Refactor

- [x] 4.1 Run `bin/test --filter=PublicRaffleParticipationEntryTest` and fix only status-foundation failures.
- [x] 4.2 Run `bin/test --filter=AdminRaffleRegistrationsTest` and confirm no admin exception UI/actions were introduced.
- [x] 4.3 Run `bin/test` before handoff; keep any refactor limited to the enum/model/migration/factory/test paths above.

## Future Work / Not Done in This Slice

- This slice is only the persisted status foundation; `active` is the operational default, not final approval by fraud, credits, tickets, or eligibility workflows.
- Future product direction remains ad views => credits => tickets => raffle participation/eligibility; direct payments are not expected.
- Normal participation validity is automatic by default at MVP scale.
- Later slices must handle admin exception actions/UI, status reason/audit metadata, automated analysis/rules, and integration with credits/tickets/eligibility.
