# Tasks: Admin Public Identity Boundary

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 520-680 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 auth schema/contracts → PR 2 session boundary/isolation |
| Delivery strategy | ask-on-risk |
| Chain strategy | stacked-to-main |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Add `Admin` / `admins` auth schema, provider, broker, and contract tests | PR 1 | Autonomous slice; include RED/GREEN/REFACTOR + `bin/test` |
| 2 | Add boundary middleware, cookie isolation, and cross-host auth tests | PR 2 | Depends on PR 1; ask chain strategy before apply |

## Phase 1: Identity Contracts (RED → GREEN → REFACTOR)

- [x] 1.1 RED: create `tests/Feature/Auth/AdminIdentityBoundaryTest.php` for `Admin` / `admins`, `guards.admin`, `providers.admins`, `passwords.admins`, and update `tests/Feature/Auth/PublicIdentityBoundaryTest.php` to expect implemented admin metadata; run `bin/test` on both files.
- [x] 1.2 GREEN: create `app/Models/Admin.php`, `database/factories/AdminFactory.php`, `database/migrations/*_create_admins_table.php`, `database/migrations/*_create_admin_password_reset_tokens_table.php`, and update `config/auth.php` to preserve `User` / `users` as public while adding explicit admin wiring.
- [x] 1.3 REFACTOR: update identity comments in `app/Models/User.php` and `database/migrations/0001_01_01_000000_create_users_table.php`; rerun `bin/test tests/Feature/Auth/AdminIdentityBoundaryTest.php tests/Feature/Auth/PublicIdentityBoundaryTest.php`.

## Phase 2: Session Boundary Wiring (RED → GREEN → REFACTOR)

- [x] 2.1 RED: create `tests/Feature/Auth/GuardSessionIsolationTest.php` proving host separation alone is not auth isolation, and that admin/public sessions plus remember-me state stay isolated across real HTTP requests; run `bin/test tests/Feature/Auth/GuardSessionIsolationTest.php`.
- [x] 2.2 GREEN: create `app/Http/Middleware/ApplyIdentityBoundary.php`, extend `config/session.php` with boundary cookie names, and prepend the middleware in `bootstrap/app.php` before `StartSession` when the design requires request-scoped cookie selection.
- [x] 2.3 GREEN: add the smallest probe/auth fixtures needed in `routes/admin.php` and `routes/web.php` so the isolation test uses real guards/cookies instead of `actingAs`-only shortcuts.
- [x] 2.4 REFACTOR: keep `config/auth.php`, `config/session.php`, and middleware helpers explicit and minimal; rerun `bin/test tests/Feature/Auth/GuardSessionIsolationTest.php`.

## Phase 3: Verification and Scope Guard

- [x] 3.1 VERIFY: run `bin/test tests/Feature/Auth tests/Feature/Routing/HostSeparationTest.php` to prove guard, broker, cookie, and host-boundary scenarios from both delta specs.
- [x] 3.2 VERIFY: run full `bin/test` to confirm the implemented boundary does not break platform foundation behavior.
- [x] 3.3 SCOPE: confirm no real-time transport files are added or changed in this slice, including `config/broadcasting.php`, `routes/channels.php`, and `resources/js/app.js`.
