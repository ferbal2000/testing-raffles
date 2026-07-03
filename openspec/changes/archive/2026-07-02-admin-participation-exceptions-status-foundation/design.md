# Design: Admin Participation Exceptions Status Foundation

## Technical Approach

Add a small persisted status axis to raffle registrations without changing public participation behavior. The database stores `active` by default, the model exposes a PHP backed enum contract, and existing public registration writes continue omitting status so the default path stays automatically valid.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|---|---|---|---|
| Status contract | Create `App\Enums\RaffleRegistrationStatus` with `Active`, `Flagged`, and `Cancelled` cases. | Inline strings or reusing `RaffleStatus`. | A dedicated enum prevents approval/workflow leakage and mirrors the existing `RaffleStatus` pattern. |
| Persistence migration | Add a new migration that adds `raffle_registrations.status` as non-null string defaulting to `active`. | Edit the original create-table migration only. | A new migration preserves already-migrated local/dev databases and backfills existing rows through the default; fresh installs still converge. |
| Unsupported status enforcement | Enforce at the Eloquent model boundary with enum casting plus a status setter using `RaffleRegistrationStatus::from(...)`. | Database enum/check constraint only, or no enforcement beyond tests. | The current code enforces `Raffle.status` vocabulary in the model, not with DB constraints. Following that pattern rejects invalid app writes before storage while keeping migrations simple. |
| Public flow impact | Do not pass status from `RaffleController::storeParticipation`. | Explicitly write `active` in the controller. | Omitting status proves the default-active contract and avoids coupling public participation to future admin exception workflows. |

## Data Flow

```text
Public form ──→ RaffleController validation ──→ registrations()->createOrFirst(...)
                                                     │
                                                     └── DB/model default: active

Future admin slice ──→ RaffleRegistration model ──→ enum-validated status write
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Enums/RaffleRegistrationStatus.php` | Create | Backed enum for `active`, `flagged`, and `cancelled`. |
| `database/migrations/*_add_status_to_raffle_registrations_table.php` | Create | Add non-null `status` string column with default `active`; drop it in `down()`. |
| `app/Models/RaffleRegistration.php` | Modify | Add `status` to `Fillable`, cast it to `RaffleRegistrationStatus`, set a raw `active` default, and reject unsupported strings through the setter. |
| `database/factories/RaffleRegistrationFactory.php` | Modify | Include default `RaffleRegistrationStatus::Active`. |
| `app/Http/Controllers/Public/RaffleController.php` | Verify only | Public writes remain unchanged and rely on defaults. |
| `tests/Feature/Raffles/PublicRaffleParticipationEntryTest.php` | Modify | Assert public participation stores `active` by default and invalid model writes are rejected. |
| `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` | Modify if needed | Keep read-only registration listing assertions scoped; do not add badges/actions. |

## Interfaces / Contracts

```php
enum RaffleRegistrationStatus: string
{
    case Active = 'active';
    case Flagged = 'flagged';
    case Cancelled = 'cancelled';
}
```

`RaffleRegistration::$status` should read as `RaffleRegistrationStatus` and persist as its string value. Invalid strings should throw `ValueError` via `RaffleRegistrationStatus::from(...)` before saving.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Feature / model boundary | Allowed status persists and reads back as the enum. | Create a registration with `flagged`, refresh it, assert enum/value. |
| Feature / model boundary | Unsupported status is rejected and not stored. | Attempt direct `RaffleRegistration::create([... 'status' => 'pending'])`, expect `ValueError`, assert database count unchanged. |
| Feature / public flow | Guest registration defaults to `active`. | Extend the existing successful participation test to assert `status = active`. |
| Regression | No operational side effects. | Existing public/admin registration tests should continue passing without UI/action assertions changing. |

## Migration / Rollout

Create an additive migration. On PostgreSQL, adding a non-null string column with default `active` gives existing rows an effective active status. Rollback drops only the `status` column and removes enum/model/factory references before later slices depend on it.

## Open Questions

- None.
