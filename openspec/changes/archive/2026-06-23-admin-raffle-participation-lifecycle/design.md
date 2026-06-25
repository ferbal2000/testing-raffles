# Design: Admin Raffle Participation Lifecycle

## Technical Approach

Add a second raffle lifecycle axis for participation on `raffles` while keeping `status` as publication only. `Raffle::canAcceptParticipants()` becomes the sole user-entry gate; `starts_at` and `ends_at` remain metadata only. Manual admin open/close actions stay in the existing admin raffle surface and reuse `auth:admin` without RBAC changes.

## Architecture Decisions

### Decision: Participation persistence

| Option | Tradeoff | Decision |
|---|---|---|
| Reuse `status` | Mixes visibility with eligibility | No |
| Add participation columns on `raffles` | Small schema growth, simplest read model | Yes |

Use nullable columns: `participation_opened_at TIMESTAMP`, `participation_closed_at TIMESTAMP`, `participation_closed_reason VARCHAR(32)`, `participation_closed_by_admin_id BIGINT NULL`. `participation_closed_by_admin_id` SHOULD reference `admins.id` with `nullOnDelete()` so audit survives admin deletion without blocking deletes.

### Decision: Canonical domain API

| Option | Tradeoff | Decision |
|---|---|---|
| Raw controller/view checks | Drift across callers | No |
| Model methods for eligibility and transitions | More model surface, but one rule source | Yes |

Add `canAcceptParticipants(): bool`, `canOpenParticipation(): bool`, `canCloseParticipation(): bool`, `openParticipation(CarbonImmutable $openedAt)`, and `closeParticipation(CarbonImmutable $closedAt, string $reason = 'admin_closed', ?Admin $admin = null)`. `canAcceptParticipants()` returns true only when `status === published`, `participation_opened_at !== null`, and `participation_closed_at === null`.

### Decision: Admin audit capture

| Option | Tradeoff | Decision |
|---|---|---|
| Require non-null admin everywhere | Blocks future job/system closures | No |
| Nullable admin at model boundary, strict admin route caller | Future-safe and auditable | Yes |

Controller actions should read the actor with `$request->user('admin')`. Admin routes run behind `auth:admin`; if the resolved user is not an `Admin`, abort `403` instead of silently losing audit. The model still accepts `null` for future automatic/system closures.

## Data Flow

    Admin index form -> admin route -> RaffleController
         -> Raffle model transition -> raffles row updated
         -> redirect /raffles with scoped flash

Open: validate route access -> `openParticipation(now())` -> set `participation_opened_at` only.

Close: validate route access -> resolve admin -> `closeParticipation(now(), 'admin_closed', $admin)` -> set `participation_closed_at`, `participation_closed_reason`, and nullable admin FK.

## File Changes

| File | Action | Description |
|---|---|---|
| `database/migrations/*_add_participation_lifecycle_to_raffles_table.php` | Create | Add four nullable participation columns and admin FK. |
| `app/Models/Raffle.php` | Modify | Add casts, admin relation, canonical participation methods, and transition guards. |
| `app/Http/Controllers/Admin/RaffleController.php` | Modify | Add `openParticipation` / `closeParticipation` actions and scoped flashes. |
| `routes/admin.php` | Modify | Add admin-only POST participation routes beside existing raffle routes. |
| `resources/views/admin/raffles/index.blade.php` | Modify | Add participation action buttons/forms in the row actions area and flash slots. |
| `lang/es/admin-raffles.php` | Modify | Extend existing Spanish admin copy for buttons, labels, and participation flashes. |
| `database/factories/RaffleFactory.php` | Modify | Add states/helpers for opened and participation-closed raffles. |
| `tests/Feature/Raffles/*`, `tests/Unit|Feature/...Raffle*` | Modify/Create | Cover model rules and admin flow with strict TDD. |

## Interfaces / Contracts

```php
public function admin(): BelongsTo;
public function canAcceptParticipants(): bool;
public function canOpenParticipation(): bool;
public function canCloseParticipation(): bool;
public function openParticipation(CarbonImmutable $openedAt): void;
public function closeParticipation(CarbonImmutable $closedAt, string $reason = 'admin_closed', ?Admin $admin = null): void;
```

Routes:
- `POST /raffles/{raffle}/participation/open`
- `POST /raffles/{raffle}/participation/close`

Flash keys:
- `admin.raffles.participation_open_success`
- `admin.raffles.participation_close_success`

## Testing Strategy

| Layer | What to Test | Approach |
|---|---|---|
| Unit/Model | `canAcceptParticipants()` and invalid transitions | Pest tests for draft/published/closed plus timestamp combinations. |
| Integration/HTTP | Admin auth, eligible routes, redirects, flashes, DB audit writes | Feature tests through admin host using `actingAs($admin, 'admin')`. |
| E2E | None in this repo | Rely on Blade feature coverage; run via `bin/test` only when implementation starts. |

## Migration / Rollout

Existing raffles backfill as participation-closed-by-default because both participation timestamps start null, so published raffles remain visible but not eligible until explicitly opened. No data migration beyond nullable columns is required. Funding auto-close and reopen remain out of scope.

## Open Questions

- [ ] None blocking. Future slice should define allowed non-admin closure reasons (for example `funding_goal_reached`) before automation is added.
