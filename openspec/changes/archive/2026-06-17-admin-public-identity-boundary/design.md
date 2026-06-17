# Design: Admin Public Identity Boundary

## Technical Approach

Implement an additive Laravel auth boundary: keep `App\Models\User` + `users` as the public contract, add `App\Models\Admin` + `admins` for admin auth, and isolate admin/public state with explicit guards, brokers, and host-aware session cookies. This follows the proposal and both delta specs while preserving the existing Blade-first, host-separated foundation.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Admin identity persistence | Reuse `users`; rename the public table; add `admins` | Add `admins` only | Preserves the verified public contract and avoids risky renames/data migration. |
| Admin auth wiring | Switch default guard by host; explicit `admin` guard/provider/broker | Add explicit `admin` guard/provider/broker and keep `web` as public default | Hidden host-based defaults are harder to test; explicit guards make route protection and future broadcasting auth clearer. |
| Session isolation | Shared session cookie; host-only distinct cookies | Distinct host-aware session cookies (`public` vs `admin`) selected before `StartSession` | Guard keys alone isolate auth, but separate cookies/sessions satisfy the stronger boundary requirement and avoid cross-surface leakage later. |
| Password recovery | Share `password_reset_tokens`; separate table | Separate `admin_password_reset_tokens` broker table | The Laravel reset table is email-keyed; sharing it would couple public/admin recovery when the same email exists in both tables. |

## Data Flow

`public host` → `web` middleware + boundary middleware → public session cookie → `web` guard → `users`

`admin host` → `web` middleware + boundary middleware → admin session cookie → `admin` guard → `admins`

Remember-me stays guard-scoped (`remember_web_*` vs `remember_admin_*`), and password recovery resolves through the matching broker only.

## File Changes

| File | Action | Description |
|---|---|---|
| `app/Models/Admin.php` | Create | Admin-only authenticatable model matching Laravel `User` conventions. |
| `database/factories/AdminFactory.php` | Create | Test/admin identity factory for strict TDD flows. |
| `database/migrations/*_create_admins_table.php` | Create | Add additive admin identity table with remember token. |
| `database/migrations/*_create_admin_password_reset_tokens_table.php` | Create | Isolate admin password recovery state. |
| `app/Http/Middleware/ApplyIdentityBoundary.php` | Create | Resolve boundary from host and set request-scoped session cookie config before session start. |
| `bootstrap/app.php` | Modify | Prepend boundary middleware to the `web` group. |
| `config/auth.php` | Modify | Add `identity_boundary.admin`, `guards.admin`, `providers.admins`, and `passwords.admins`. |
| `config/session.php` | Modify | Add explicit boundary cookie names/config helpers; keep domain host-only by default. |
| `routes/admin.php` | Modify | Keep host scoping and prepare explicit `auth:admin` / `guest:admin` route groups when auth endpoints land. |
| `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Modify | Update expectations from “planned” admin to implemented boundary metadata. |
| `tests/Feature/Auth/AdminIdentityBoundaryTest.php` | Create | Prove provider/guard/broker contracts. |
| `tests/Feature/Auth/GuardSessionIsolationTest.php` | Create | Prove cookie, session, and remember-me isolation across hosts via real HTTP requests. |

## Interfaces / Contracts

```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'admin' => ['driver' => 'session', 'provider' => 'admins'],
];

'passwords' => [
    'users' => ['provider' => 'users', 'table' => 'password_reset_tokens'],
    'admins' => ['provider' => 'admins', 'table' => 'admin_password_reset_tokens'],
];
```

Boundary middleware contract:

```php
$request->attributes->set('identity_boundary', 'public|admin');
config(['session.cookie' => $boundaryCookieName]);
```

## Testing Strategy

| Layer | What to Test | Approach |
|---|---|---|
| Unit | Boundary host → cookie-name resolution | Small middleware tests if extraction becomes non-trivial. |
| Integration | Guard/provider/broker wiring | RED first: `bin/test` assertions on `config('auth...')`, model classes, and broker tables. |
| Integration | Session/cookie/remember isolation | RED first: log in as admin, carry cookies to public host, assert public guest; repeat inverse and remember-me case with multi-request HTTP tests. Avoid `actingAs`-only proofs. |
| E2E | None in this slice | Not available; feature tests are the verification surface. |

## Migration / Rollout

Additive rollout only: create `admins` and `admin_password_reset_tokens`, update config/middleware, and keep `users` untouched. No rename or data migration is required.

## Out of Scope

No raffle domain logic, entries, draw execution, audit trails, real-time transport, or UI-heavy auth flows beyond the minimum endpoints/fixtures needed to prove boundary behavior.

## Review Workload

This likely exceeds the 400-line budget once migrations, middleware, and isolation tests land. Recommend **two chained PRs**: (1) admin model/provider/broker schema + contract tests, (2) session-cookie middleware + cross-host isolation tests.

## Open Questions

- [ ] Should this slice add minimal admin login/reset endpoints, or should isolation be proven with test-only probe routes and config-level contracts first?
