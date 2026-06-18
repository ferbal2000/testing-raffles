# Design: Raffle Lifecycle Basic

## Technical Approach

Implement the first raffle slice with plain Laravel conventions, not a new module skeleton. This change adds a `raffles` table, an `App\Models\Raffle` Eloquent model, and a small domain rule layer for explicit `publish` and `close` transitions. The design maps directly to `raffle-lifecycle` spec requirements: persistence starts in `draft`, only `draft -> published -> closed` is allowed, and `starts_at` / `ends_at` stay as stored lifecycle data without automatic transitions.

## Architecture Decisions

### Decision: First raffle slice shape

| Option | Tradeoff | Decision |
|-------|----------|----------|
| `app/Modules/Raffles/*` first | Matches planned architecture, but introduces a new project structure before any local precedent exists | No |
| Plain Laravel model + focused support types | Fits current codebase (`app/Models`, migrations, factories, Pest feature tests), keeps review size small | Yes |

Rationale: the repo has no live `app/Modules/*` implementation yet, while current code already uses standard Laravel model/factory/migration patterns.

### Decision: Transition rules location

| Option | Tradeoff | Decision |
|-------|----------|----------|
| Controllers/routes | Out of scope and couples domain rules to transport | No |
| Model methods backed by a status enum and domain exception | Keeps transitions explicit, testable, and reusable by future admin HTTP code | Yes |

Rationale: this change is domain-first only, so lifecycle rules must live below any future admin surface.

### Decision: Availability behavior scope

| Option | Tradeoff | Decision |
|-------|----------|----------|
| Automatic status updates from time fields | Expands into scheduling policy and contradicts the spec | No |
| Persist `starts_at` / `ends_at` and keep any availability helper read-only | Supports future use without hidden side effects | Yes |

Rationale: the spec requires explicit transitions only. If a helper is added, it must never mutate status.

## Data Flow

`Raffle::create()` persists a new record with default `draft` status.

`Raffle` instance -> `publish()` -> validate current status -> set `published` -> save

`Raffle` instance -> `close()` -> validate current status -> set `closed` -> save

`starts_at` and `ends_at` are stored and cast as datetimes. Reading them, or any optional availability helper, does not change lifecycle state.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/*_create_raffles_table.php` | Create | Add `raffles` persistence with `status`, nullable `starts_at`, nullable `ends_at`, and timestamps. |
| `app/Models/Raffle.php` | Create | Eloquent model with fillable lifecycle fields, casts, default draft handling, and transition methods. |
| `app/Enums/RaffleStatus.php` | Create | Supported lifecycle states limited to `draft`, `published`, `closed`. |
| `app/Exceptions/InvalidRaffleTransition.php` | Create | Domain-level rejection for unsupported publish/close transitions. |
| `database/factories/RaffleFactory.php` | Create | Test factory with draft defaults and optional schedule fields. |
| `tests/Feature/Raffles/RaffleLifecycleTest.php` | Create | Pest + `RefreshDatabase` coverage for persistence, allowed transitions, rejected transitions, and non-automatic time behavior. |

## Interfaces / Contracts

```php
enum RaffleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
}

final class Raffle extends Model
{
    public function publish(): void;
    public function close(): void;
    public function isAvailableAt(CarbonInterface $moment): bool; // optional, read-only
}
```

Rules:
- new raffles persist as `draft`
- `publish()` allowed only from `draft`
- `close()` allowed only from `published`
- unsupported status values are rejected by enum casting/model assignment before persistence

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | None required separately for this slice | Keep logic covered through model-focused Pest tests to stay within PR budget. |
| Integration | Persistence defaults, enum restriction, publish/close transitions, stored schedule fields, no auto-transition | Add `tests/Feature/Raffles/RaffleLifecycleTest.php` with `RefreshDatabase`; run through `bin/test`. |
| E2E | Not applicable | No HTTP surface in this change. |

## Migration / Rollout

Run the new raffle migration with the normal Laravel migration flow. No feature flag or phased rollout is required because no route, controller, or public/admin UI contract changes in this slice.

## Open Questions

- [ ] Should the optional `isAvailableAt()` helper be included now, or deferred if the publish/close + persistence slice already approaches the 400-line review budget?
