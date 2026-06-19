# Tasks: Raffle Lifecycle Basic

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 220-320 |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-always |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Persist raffle lifecycle and enforce `draft -> published -> closed` | PR 1 | Single slice; tests and migration stay together |

## Phase 1: RED Tests

- [x] 1.1 Create `tests/Feature/Raffles/RaffleLifecycleTest.php` with failing tests for draft default and persisted `starts_at` / `ends_at` fields.
- [x] 1.2 Extend `tests/Feature/Raffles/RaffleLifecycleTest.php` with failing tests for invalid status rejection and `draft -> published -> closed` transition rules.
- [x] 1.3 Add a failing test in `tests/Feature/Raffles/RaffleLifecycleTest.php` proving past/future dates never auto-change status, then run `bin/test --filter=RaffleLifecycleTest`.

## Phase 2: GREEN Persistence Foundation

- [x] 2.1 Create `database/migrations/*_create_raffles_table.php` with `status`, nullable `starts_at`, nullable `ends_at`, and timestamps.
- [x] 2.2 Create `app/Enums/RaffleStatus.php` and `app/Exceptions/InvalidRaffleTransition.php` limited to `draft`, `published`, and `closed`.
- [x] 2.3 Create `database/factories/RaffleFactory.php` with draft defaults and optional schedule values for the feature tests.

## Phase 3: GREEN Domain Behavior

- [x] 3.1 Create `app/Models/Raffle.php` with `HasFactory`, fillable lifecycle fields, enum/datetime casts, and default `draft` persistence behavior.
- [x] 3.2 Implement `publish()` in `app/Models/Raffle.php` so only persisted `draft` raffles become `published`, otherwise throw `InvalidRaffleTransition`.
- [x] 3.3 Implement `close()` in `app/Models/Raffle.php` so only `published` raffles become `closed`, with no `isAvailableAt()` helper in this slice.

## Phase 4: REFACTOR and Verify

- [x] 4.1 Refactor `tests/Feature/Raffles/RaffleLifecycleTest.php` and `database/factories/RaffleFactory.php` to remove duplication while preserving the spec scenarios.
- [x] 4.2 Run `bin/test --filter=RaffleLifecycleTest` and `bin/test` to verify the lifecycle suite through the canonical runner.
