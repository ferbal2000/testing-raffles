# Apply Progress: Admin Public Identity Boundary

**Change**: `admin-public-identity-boundary`
**Mode**: Strict TDD
**Work Unit**: 2 / PR 2
**Chain Strategy**: stacked-to-main

## Completed Tasks

- [x] 1.1 RED: create `tests/Feature/Auth/AdminIdentityBoundaryTest.php` for `Admin` / `admins`, `guards.admin`, `providers.admins`, `passwords.admins`, and update `tests/Feature/Auth/PublicIdentityBoundaryTest.php` to expect implemented admin metadata; run `bin/test` on both files.
- [x] 1.2 GREEN: create `app/Models/Admin.php`, `database/factories/AdminFactory.php`, `database/migrations/*_create_admins_table.php`, `database/migrations/*_create_admin_password_reset_tokens_table.php`, and update `config/auth.php` to preserve `User` / `users` as public while adding explicit admin wiring.
- [x] 1.3 REFACTOR: update identity comments in `app/Models/User.php` and `database/migrations/0001_01_01_000000_create_users_table.php`; rerun `bin/test tests/Feature/Auth/AdminIdentityBoundaryTest.php tests/Feature/Auth/PublicIdentityBoundaryTest.php`.
- [x] 2.1 RED: create `tests/Feature/Auth/GuardSessionIsolationTest.php` proving host separation alone is not auth isolation, and that admin/public sessions plus remember-me state stay isolated across real HTTP requests; run `bin/test tests/Feature/Auth/GuardSessionIsolationTest.php`.
- [x] 2.2 GREEN: create `app/Http/Middleware/ApplyIdentityBoundary.php`, extend `config/session.php` with boundary cookie names, and prepend the middleware in `bootstrap/app.php` before `StartSession` when the design requires request-scoped cookie selection.
- [x] 2.3 GREEN: add the smallest probe/auth fixtures needed in `routes/admin.php` and `routes/web.php` so the isolation test uses real guards/cookies instead of `actingAs`-only shortcuts.
- [x] 2.4 REFACTOR: keep `config/auth.php`, `config/session.php`, and middleware helpers explicit and minimal; rerun `bin/test tests/Feature/Auth/GuardSessionIsolationTest.php`.
- [x] 3.1 VERIFY: run `bin/test tests/Feature/Auth tests/Feature/Routing/HostSeparationTest.php` to prove guard, broker, cookie, and host-boundary scenarios from both delta specs.
- [x] 3.2 VERIFY: run full `bin/test` to confirm the implemented boundary does not break platform foundation behavior.
- [x] 3.3 SCOPE: confirm no real-time transport files are added or changed in this slice, including `config/broadcasting.php`, `routes/channels.php`, and `resources/js/app.js`.

## Files Changed

| File | Action | What Was Done |
|------|--------|---------------|
| `tests/Feature/Auth/AdminIdentityBoundaryTest.php` | Created | Added admin boundary contract tests for model/table, guard/provider, and password broker wiring. |
| `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Modified | Updated public boundary expectations to prove `User` / `users` remains the public contract after admin wiring and cannot be repointed by `AUTH_MODEL`. |
| `app/Models/Admin.php` | Created | Added the admin authenticatable model. |
| `database/factories/AdminFactory.php` | Created | Added admin factory support for future auth flows. |
| `database/migrations/0001_01_01_000003_create_admins_table.php` | Created | Added the additive `admins` identity table. |
| `database/migrations/0001_01_01_000004_create_admin_password_reset_tokens_table.php` | Created | Added the isolated admin password reset token table. |
| `config/auth.php` | Modified | Added explicit admin identity metadata, guard, provider, and password broker while pinning the public `users` provider to `User::class` without an `AUTH_MODEL` override path. |
| `config/session.php` | Modified | Added explicit public/admin session cookie names for request-scoped boundary switching. |
| `bootstrap/app.php` | Modified | Prepended the identity-boundary middleware to the `web` middleware group before session startup. |
| `app/Http/Middleware/ApplyIdentityBoundary.php` | Created | Resolves the current host boundary, records it on the request, and swaps the session cookie name before `StartSession`. |
| `routes/web.php` | Modified | Added minimal console-only public auth probe/login fixtures for real cookie-based boundary tests. |
| `routes/admin.php` | Modified | Added minimal console-only admin auth probe/login fixtures for real cookie-based boundary tests. |
| `tests/Feature/Auth/GuardSessionIsolationTest.php` | Created | Added cross-host admin/public session and remember-me isolation coverage using real HTTP requests and carried cookies. |
| `app/Models/User.php` | Modified | Refined boundary comments to state admin identity is separate. |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Modified | Refined migration comments to keep `users` public-only and `admins` separate. |
| `openspec/changes/admin-public-identity-boundary/tasks.md` | Modified | Marked Work Unit 1 and Work Unit 2 tasks complete and recorded the resolved chain strategy. |
| `openspec/changes/admin-public-identity-boundary/verify-report.md` | Modified | Recorded WU2 verification results, full-suite pass, scope guard, and no realtime transport changes. |

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/Auth/AdminIdentityBoundaryTest.php`, `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Integration | ✅ `PublicIdentityBoundaryTest`: 2/2 passing baseline | ✅ Wrote failing expectations for admin metadata and updated public boundary assertions first | ✅ Focused `bin/test` run failed as expected with 4 failures / 1 pass | ✅ 5 focused assertions cover admin model-table metadata, guard-provider wiring, password broker wiring, and preserved public defaults | ➖ None needed in RED task |
| 1.2 | `tests/Feature/Auth/AdminIdentityBoundaryTest.php`, `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Integration | N/A (new files + same focused safety net carried from 1.1) | ✅ Used the failing 1.1 tests as the production-code gate | ✅ Focused `bin/test` run passed with 6 tests / 25 assertions after the post-review `AUTH_MODEL` regression fix | ✅ Separate tests prove four distinct contract paths: admin identity metadata, admin auth wiring, broker isolation, and public provider pinning while public wiring stays unchanged | ➖ None needed beyond minimal implementation |
| 1.3 | `tests/Feature/Auth/AdminIdentityBoundaryTest.php`, `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Integration | ✅ 6/6 passing before refactor comment updates and post-review bookkeeping | ✅ Approval coverage already existed before touching comments/bookkeeping | ✅ Focused `bin/test` rerun passed with 6 tests / 25 assertions | ➖ Triangulation skipped: comment/bookkeeping-only refactor with preserved behavior surfaces | ✅ Updated boundary comments and TDD evidence with no behavior change |
| 2.1 | `tests/Feature/Auth/GuardSessionIsolationTest.php` | Integration | ✅ `AdminIdentityBoundaryTest`, `PublicIdentityBoundaryTest`, `HostSeparationTest`: 9/9 passing baseline | ✅ Wrote failing cross-host isolation tests before middleware or probe routes existed | ✅ Focused `bin/test` run failed as expected with 2 failing tests / 2 assertions on missing routes | ✅ Added admin-session, public-remember, and public-session cross-host cases so routing alone could not fake a green | ➖ None needed in RED task |
| 2.2 | `tests/Feature/Auth/GuardSessionIsolationTest.php` | Integration | N/A (same focused safety net carried from 2.1) | ✅ Used the failing 2.1 tests as the middleware/config gate | ✅ Focused `bin/test` run passed with 3 tests / 42 assertions after adding boundary middleware, request-scoped cookie selection, and web-group prepending | ✅ The same file proves both boundary cookie names and opposite-host guest behavior under carried cookies | ➖ None needed beyond minimal middleware/config wiring |
| 2.3 | `tests/Feature/Auth/GuardSessionIsolationTest.php` | Integration | N/A (same focused safety net carried from 2.1) | ✅ Used the failing 2.1 tests as the route-fixture gate | ✅ Focused `bin/test` run passed with 3 tests / 42 assertions after adding minimal host-scoped login/probe fixtures in `routes/web.php` and `routes/admin.php` | ✅ Real HTTP requests now cover admin login, public login, public remember-me, and cross-host probes without `actingAs` shortcuts | ➖ None needed beyond minimal fixtures |
| 2.4 | `tests/Feature/Auth/GuardSessionIsolationTest.php` | Integration | ✅ 3/3 passing before the cleanup pass | ✅ Approval coverage existed before tightening the route fixtures and helper boundaries | ✅ Focused `bin/test` rerun passed with 3 tests / 42 assertions; broader auth+host regression rerun passed with 12 tests / 74 assertions | ✅ Added the explicit host-routing-is-not-auth-isolation case and kept the middleware/session helpers minimal | ✅ Restricted the probe routes to console execution and kept boundary payload helpers local to each route file |

## Test Summary

- **Total tests written**: 9
- **Total tests passing**: 9
- **Layers used**: Unit (0), Integration (9), E2E (0)
- **Approval tests (refactoring)**: Existing focused contract tests reused for WU1 comment-only refactor and WU2 fixture/middleware cleanup
- **Pure functions created**: 0

## Deviations from Design

None — implementation matches the Work Unit 1 + Work Unit 2 design scope.

## Post-Review WU1 Fix

- Added a regression test that loads `config/auth.php` with `AUTH_MODEL=App\Models\Admin` and proves the public provider still resolves to `App\Models\User`.
- Removed the `env('AUTH_MODEL', ...)` override from `config/auth.php` so the public `users` provider stays on the explicit WU1 contract.
- Reran focused auth boundary tests and the full `bin/test` suite after the fix.

## Issues Found

None.

## Remaining Tasks

None for this change. Work Units 1 and 2 plus verification/scope guard are complete.

## Workload / PR Boundary

- **Mode**: stacked PR slice
- **Current work unit**: Work Unit 2 / PR 2
- **Boundary**: starts at request-scoped session cookie selection plus minimal host-scoped auth probes, and stops before verify/archive, raffle lifecycle, entries, draw, audit, or any real-time transport
- **Estimated review budget impact**: focused to boundary middleware, cookie isolation, and probe tests so the second stacked PR stays reviewable against the 400-line target

## Status

10/10 tasks complete. WU2 verification passed and bookkeeping is synchronized.
