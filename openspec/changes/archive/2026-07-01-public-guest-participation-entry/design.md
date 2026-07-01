# Design: Public Guest Participation Entry

## Technical Approach

Add a dedicated guest-registration aggregate behind the public raffle detail page. The GET detail page keeps `Raffle::canAcceptParticipants()` as the single eligibility source; the new POST path re-checks that method before persistence, normalizes email, then creates or reuses one registration per raffle/email pair.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Registration storage | `raffle_participants`, `raffle_participations`, `raffle_registrations` | `raffle_registrations` + `App\Models\RaffleRegistration` | “Registration” stays contact-only and avoids future confusion with tickets, numbers, or a richer participation domain. |
| Validation boundary | New FormRequest vs inline validation | Inline validation in `Public\RaffleController` | Existing controllers validate inline. This keeps the codebase consistent while still allowing pre-validation normalization with `$request->merge(...)`. |
| Duplicate handling | Validation-only unique rule vs DB constraint + recovery | Composite unique index + `firstOrCreate`/conflict recovery | Prevents duplicates at the database level and remains friendly under concurrent submissions. |

## Data Flow

`GET /raffles/{id}` → `Public\RaffleController@show` → `Raffle::canAcceptParticipants()` → detail view renders form or closed message

`POST /raffles/{id}/participation` → resolve published raffle → normalize `email=Str::lower(trim(...))` → validate `name`/`email` → re-check `canAcceptParticipants()` → create/find registration → redirect to detail with scoped flash

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/*_create_raffle_registrations_table.php` | Create | Add registration storage with raffle/user FKs and per-raffle email uniqueness. |
| `app/Models/RaffleRegistration.php` | Create | Contact-only aggregate with `raffle()` and nullable `user()` relations. |
| `database/factories/RaffleRegistrationFactory.php` | Create | Support feature tests for duplicate and closed-flow coverage. |
| `app/Models/Raffle.php` | Modify | Add `registrations(): HasMany` relation only; keep eligibility logic canonical. |
| `app/Http/Controllers/Public/RaffleController.php` | Modify | Add POST handler, normalization, validation, eligibility re-check, and friendly flash responses. |
| `routes/web.php` | Modify | Register public POST route beside `public.raffles.show`. |
| `resources/views/public/raffles/show.blade.php` | Modify | Render form, validation feedback, and success/info/unavailable states. |
| `lang/es/public-raffles.php` | Modify | Add Spanish copy for form labels, CTA, duplicate confirmation, and unavailable submission feedback. |
| `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Modify | Replace read-only assertions with conditional form/closed-state assertions. |
| `tests/Feature/Raffles/PublicRaffleParticipationEntryTest.php` | Create | Cover happy path, duplicate retry, validation failure, and stale-page rejection. |

## Interfaces / Contracts

```php
Schema::create('raffle_registrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('raffle_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name');
    $table->string('email'); // normalized lowercase + trimmed before save
    $table->timestamps();
    $table->unique(['raffle_id', 'email']);
});
```

Route contract: `POST /raffles/{raffle}/participation` named `public.raffles.participation.store`.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | Email normalization helper behavior if extracted | Small Pest test only if normalization leaves controller. |
| Integration | Registration persistence, duplicate no-op, eligibility re-check | Laravel HTTP feature tests with `RefreshDatabase`; later run via `bin/test`. |
| E2E | None in repo today | Not applicable. |

## Migration / Rollout

Single additive migration only. No backfill, feature flag, or auth rollout required. Nullable `user_id` preserves a future link point for public accounts without changing this slice.

## Open Questions

- [ ] Should duplicate responses use a distinct “already registered” flash, or the same success copy for stronger idempotent UX?
- [ ] Should validation errors stay on the detail page via redirect back only, or do we want anchor-based scrolling to the form in a follow-up?
