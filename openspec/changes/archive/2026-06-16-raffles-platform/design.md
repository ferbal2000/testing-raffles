# Design: Raffles Platform Foundation Slice

## Technical Approach

Implement the foundation slice as a Laravel modular monolith with Blade-first rendering, PostgreSQL persistence, Pest/PHPUnit tests, and small Vue islands only where reactivity helps. Local execution standardizes on Docker Compose so PHP 8.3+, Composer, PostgreSQL, and Vite-backed Node commands run through repository wrappers instead of host tools. This keeps TDD runnable on any machine while preserving one app, one database, and a future extraction path through module boundaries.

## Architecture Decisions

| Decision | Choice | Alternatives considered | Rationale |
|---|---|---|---|
| Framework | Laravel 12-style app + Blade + Vite Vue islands | Full Vue SPA, Next.js monorepo | Laravel gives auth, validation, sessions, queues, migrations, and testing with less startup cost for a greenfield MVP. |
| Module shape | Modular monolith under `app/Modules/*` with thin HTTP layer | Default Laravel-by-layer only | Keeps bounded contexts visible now and easier to extract later without overbuilding services today. |
| Identity boundary | Historical draft assumed separate admin and public identity tables; superseded wording keeps Laravel `users` / `User` for the public website and introduces `admins` / `Admin` for admin, with separate guards, providers, password brokers, and session cookies | Shared users table with roles | Hard isolation is still required, but the corrected plan preserves the already-established public identity boundary instead of creating a second public identity model. |
| Rendering | Blade for full pages; Vue mounted per feature | SPA-first admin/public apps | Blade is faster for CRUD and auth flows; Vue remains available for richer admin widgets later. |
| Dev runtime | `compose.yaml` + repo `bin/*` wrappers as the default local runtime | Host-installed PHP/Composer/Node, full CI/prod containerization now | Containers remove PHP version drift immediately, wrappers keep commands short for TDD, and limiting scope to local runtime avoids premature ops work. |
| Draw integrity | One DB transaction with `lockForUpdate()`, unique `draws.raffle_id`, and audit append in the same commit | App-level mutex only | PostgreSQL constraints plus transaction boundaries are the reliable exactly-once control. |
| i18n | Spanish user copy via Laravel translation keys from day one | Hardcoded Spanish strings | Delivers Spanish UX now while making future locales a file/config change instead of a rewrite. |

## Data Flow

`admin.raffles.test` → admin routes/domain middleware → admin guard → module action → PostgreSQL → audit event

`www.raffles.test` → public routes/domain middleware → public guard → module action → PostgreSQL → audit event

Local TDD flow:

    Developer → `bin/test`
      → `docker compose run/exec app`
      → Composer/Pest inside PHP 8.3 container
      → PostgreSQL service in Compose
      → failing test → code change → green test

Draw flow:

    Admin POST /admin/raffles/{raffle}/draw
      → DrawRaffleAction
      → DB transaction
      → lock raffle row
      → verify status=closed and no draw exists
      → select winner from accepted entries
      → insert draws row + update raffle + append audit
      → commit

Rejected draw attempts still append an audit event, but outside the winning insert path if the transaction aborts before persistence.

## File Changes

| File | Action | Description |
|---|---|---|
| `app/Modules/Raffles/*` | Create | Raffle aggregate rules, actions, DTOs, repository contracts. |
| `app/Modules/Entries/*` | Create | Entry acceptance, idempotency, and eligibility window checks. |
| `app/Modules/Draws/*` | Create | Exactly-once draw orchestration and winner persistence. |
| `app/Modules/Audit/*` | Create | Append-only audit writer and query objects. |
| `app/Modules/IdentityAdmin/*` | Create | Admin user model, auth services, and policies. |
| `app/Modules/IdentityPublic/*` | Create | Historical planning label only; superseded by keeping `App\Models\User` as the public identity model and adding only public-side auth/session services where needed. |
| `app/Http/Controllers/Admin/*` | Create | Admin Blade controllers for raffle lifecycle and draw actions. |
| `app/Http/Controllers/Public/*` | Create | Public Blade controllers for auth and entry submission. |
| `compose.yaml` | Create | Local-only app/db/node runtime for PHP 8.3+, Composer, PostgreSQL, and Vite tasks. |
| `docker/php/Dockerfile` | Create | PHP CLI image with required extensions and Composer for Laravel commands/tests. |
| `bin/test`, `bin/artisan`, `bin/composer`, `bin/dev`, `bin/npm` | Create | Stable wrappers so tests and framework commands do not depend on host PHP/Composer/Node. |
| `routes/admin.php`, `routes/web.php` | Create/Modify | Host-separated route registration. |
| `config/auth.php`, `config/session.php` | Modify | Separate guards/providers/cookie names per surface. |
| `database/migrations/*` | Create | Corrected plan: `admins`, `raffles`, `raffle_entries`, `draws`, `audit_events`; keep existing Laravel `users` as the public identity table. |
| `resources/views/{admin,public}/*` | Create | Blade-first UI with translation keys. |
| `resources/js/*` | Create | Progressive Vue mounts only where needed. |
| `README.md`, `openspec/config.yaml` | Modify | Document wrapper-first setup and point TDD commands to containerized runners. |
| `tests/Feature/*`, `tests/Unit/*` | Create | TDD coverage for flows and domain rules. |

## Interfaces / Contracts

```php
final readonly class DrawRaffleCommand {
    public function __construct(
        public int $raffleId,
        public int $adminUserId,
        public CarbonImmutable $requestedAt,
    ) {}
}
```

Schema direction: `raffles.status` = `draft|published|closed|drawn`; `raffle_entries` unique on (`raffle_id`, `public_user_id`, `idempotency_key`); `draws` unique on `raffle_id`; `audit_events` append-only with actor type/id, action, target, outcome, metadata JSON, and timestamp.

Wrapper contract: `bin/test` is the canonical RED/GREEN entry point; `bin/artisan` and `bin/composer` proxy framework/package commands into the app container; `bin/dev` starts the Compose runtime; `bin/npm` remains optional for direct Vite tasks.

## Testing Strategy

| Layer | What to Test | Approach |
|---|---|---|
| Unit | State transitions, guard rules, audit payload building | Run RED/GREEN via `bin/test`; keep Pest fast inside the PHP container. |
| Integration | Entry idempotency, admin/public guard isolation, draw transaction, append-only audit | Use Compose PostgreSQL so feature tests match the intended relational runtime. |
| E2E | Critical host-separated happy path only if HTTP tests reveal gaps | Keep deferred; Laravel HTTP tests are primary for this slice. |

## Migration / Rollout

No data migration required beyond initial schema creation. Roll out the Compose runtime first, switch docs/test commands to wrappers, and keep CI/production containerization explicitly out of scope for this change.

## Open Questions

- [ ] Should Vite run in a dedicated `node` service or only through an on-demand wrapper when front-end work begins?
- [ ] Should rejected draw attempts that fail before DB transaction start still be persisted through the same audit writer or a dedicated preflight path?
