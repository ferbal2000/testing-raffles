# Tasks: Raffles Platform Foundation Slice

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 1100-1450 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 → PR 2 → PR 3 → PR 4 |
| Delivery strategy | ask-on-risk |
| Chain strategy | stacked-to-main |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Compose runtime + wrappers + scaffold verification | PR 1 | Must remove host PHP/Composer dependency and keep smoke tests runnable via `bin/test`. |
| 2 | Separate admin/public identities and guard isolation | PR 2 | Depends on PR 1; keep auth tests in same diff. |
| 3 | Raffle publish/close + entry idempotency + audit success events | PR 3 | Depends on PR 2; keep Blade + feature tests together. |
| 4 | Exactly-once draw + rejected draw audit path | PR 4 | Depends on PR 3; transaction and lock tests included. |

## Phase 1: Runtime / TDD Bootstrap

- [x] 1.1 Create Laravel app scaffold (`artisan`, `composer.json`, `bootstrap/`, `app/`, `config/`, `routes/`) with PostgreSQL defaults and Vite enabled.
- [x] 1.2 Add Pest/PHPUnit runner setup in `tests/Pest.php`, `phpunit.xml`, `tests/TestCase.php`, and wire the initial test command plus `.env.example` DB/app URLs.
- [x] 1.3 Write RED smoke tests in `tests/Feature/HealthCheckTest.php` and `tests/Feature/Routing/HostSeparationTest.php`; make them pass with `routes/web.php` and `routes/admin.php` placeholders.
- [x] 1.4 Add `compose.yaml` and `docker/php/Dockerfile` so PHP 8.3+, Composer, and PostgreSQL run in containers; keep CI and production images out of scope.
- [x] 1.5 Add `bin/test`, `bin/artisan`, `bin/composer`, and `bin/dev` (optional `bin/npm`); update `README.md` and `openspec/config.yaml` so RED/GREEN runs through wrappers, not host PHP/Composer.

## Phase 2: Identity Boundary Foundation

- [ ] 2.1 Write RED feature tests in `tests/Feature/Auth/AdminGuardIsolationTest.php` and `tests/Feature/Auth/PublicGuardIsolationTest.php` using `bin/test`.
- [ ] 2.2 Add migrations for `admin_users` and `public_users`; create `app/Modules/IdentityAdmin/AdminUser.php` and `app/Modules/IdentityPublic/PublicUser.php`.
- [ ] 2.3 Configure `config/auth.php`, `config/session.php`, and route/domain middleware so admin and public cookies/guards stay isolated; make Phase 2 tests pass.

## Phase 3: Raffle + Entry Core Flow

- [ ] 3.1 Add RED unit tests in `tests/Unit/Raffles/RaffleLifecycleTest.php` for `draft -> published -> closed` guard rules before creating `app/Modules/Raffles/*`.
- [ ] 3.2 Add raffle and audit migrations plus `app/Modules/Raffles/*` and `app/Modules/Audit/*` actions to pass publish/close tests and append success audit events.
- [ ] 3.3 Add RED feature tests in `tests/Feature/Entries/SubmitEntryTest.php` for accepted entry, duplicate idempotency, and admin rejection.
- [ ] 3.4 Add `raffle_entries` migration, `app/Modules/Entries/*`, public entry controller, and `resources/views/public/raffles/show.blade.php` using Spanish translation keys.

## Phase 4: Draw Control / Verification

- [ ] 4.1 Add RED tests in `tests/Feature/Draws/DrawRaffleTest.php` for successful draw, repeated draw rejection, empty raffle rejection, and rejected-draw audit.
- [ ] 4.2 Add `draws` migration, `app/Modules/Draws/DrawRaffleAction.php`, admin draw controller/view, and transaction + `lockForUpdate()` flow to pass draw tests.
- [ ] 4.3 Add translation files in `lang/es/*.php`, document local subdomain + wrapper usage in `README.md`, and keep `openspec/config.yaml` test commands aligned with `bin/test`.
