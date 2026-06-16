# Handoff: Platform Foundation Slice

This handoff summarizes the completed foundation slice so the next session can continue with SDD/TDD from a clean baseline.

## Change objective

Establish the local development and testing foundation for a raffle platform before implementing business features.

Completed scope:

- Laravel foundation scaffold.
- Docker Compose runtime for PHP, Composer, Node/Vite tooling, and PostgreSQL.
- Pest/PHPUnit test runner through `./bin/test`.
- Public/admin host separation smoke tests.
- Spanish application copy behind translation keys.
- OpenSpec + Engram SDD artifact trail.

## Decisions made

| Area | Decision |
|------|----------|
| Architecture | Start with a modular monolith. |
| Stack | Laravel + PostgreSQL + Pest/PHPUnit, Blade-first, progressive Vue only where it adds value. |
| Runtime | Docker Compose is the default local runtime; avoid host PHP/Composer coupling. |
| Local domains | Use `www.raffles.test` and `admin.raffles.test` locally. |
| Remote domains | Use real DNS for remote environments, e.g. `staging.raffles.com`, `admin.staging.raffles.com`, `www.raffles.com`, `admin.raffles.com`. |
| Identity | `User` / `users` means public website identity only. Admin identity will be separate in a later slice. |
| Database | One PostgreSQL database for now. |
| Language | Technical docs and comments in English; app-facing copy in Spanish via translation keys. |
| Personal files | `notas.txt` and `.atl/` are ignored by Git. |
| Delivery | Use small stacked-to-main work units. |

## Files touched

Key paths:

- `compose.yaml` — local app/db runtime.
- `docker/php/Dockerfile` — PHP 8.3 container image with Composer and Node tooling.
- `bin/test`, `bin/artisan`, `bin/composer`, `bin/dev`, `bin/npm` — Dockerized wrappers.
- `README.md` — local setup, host mapping, migration step, and runtime docs.
- `routes/web.php`, `routes/admin.php` — host-scoped public/admin placeholders.
- `resources/views/public/home.blade.php`, `resources/views/admin/home.blade.php` — placeholder pages using translation keys.
- `lang/es/home.php` — Spanish app-facing copy.
- `app/Models/User.php`, `config/auth.php`, `database/migrations/0001_01_01_000000_create_users_table.php` — public-only user identity clarification.
- `tests/Feature/**` — smoke and boundary tests.
- `openspec/specs/platform-foundation/spec.md` — synced implemented foundation behavior.
- `openspec/changes/archive/2026-06-16-raffles-platform/` — archived planning, apply, verify, and archive artifacts.

## Pending files / areas

No dirty files at handoff time.

Remaining product areas are intentionally not implemented yet:

- Admin identity model/table/guard/session isolation.
- PostgreSQL-backed integration assertions for real persistence behavior.
- Raffle lifecycle.
- Entry/participation flow.
- Draw execution.
- Audit log implementation.
- GitHub Actions alignment with Docker/wrapper runtime.

## Commands executed

Common local commands used and verified:

```bash
./bin/artisan key:generate
./bin/artisan migrate --force
./bin/test
docker compose config
./bin/artisan route:list
./bin/composer --version
./bin/npm --version
```

Git/GitHub commands used during setup:

```bash
git init -b main
git remote add origin https://github.com/ferbal2000/testing-raffles.git
git push -u origin main
git pull --rebase origin main
git push
```

Final verified test result:

```text
11 tests, 44 assertions
```

## Known risks

- Current tests use SQLite in-memory; the next persistence slice must add PostgreSQL-backed assertions.
- Initial scaffold tasks have imperfect strict-TDD traceability because they were created before the Dockerized runner existed.
- GitHub Actions still uses host PHP/Composer rather than the repository wrapper flow.
- Admin identity is only documented as separate; it is not implemented yet.

## Recommended next step

Start a new SDD change for Phase 2:

```text
admin-public-identity-boundary
```

Recommended scope:

- Add separate admin identity model/table/guard/provider/session boundary.
- Keep `User` / `users` as public website identity only.
- Add the first PostgreSQL-backed integration assertion.
- Preserve strict TDD through `./bin/test`.
