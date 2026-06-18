# Handoff: Admin/Public Identity Boundary

This handoff closes the completed `admin-public-identity-boundary` slice and defines the recommended next slice for a future session.

## Change objective

Implement a hard identity boundary between the public website and the admin backend before adding raffle-domain behavior.

Completed scope:

- Public identity remains Laravel `User` / `users`.
- Admin identity uses `Admin` / `admins`.
- Admin and public auth providers, guards, password brokers, session cookies, and remember-me behavior are isolated.
- Host separation is tested as routing only, not accepted as auth isolation by itself.
- The completed OpenSpec change was archived and synced into source-of-truth specs.

## Decisions made

| Area | Decision |
|------|----------|
| Identity naming | Public stays `User` / `users`; admin uses `Admin` / `admins`. |
| Public provider | `providers.users.model` is pinned to `App\Models\User::class`; `AUTH_MODEL` cannot repoint it. |
| Admin provider | Admin auth uses explicit `admin` guard, `admins` provider, and `admins` password broker. |
| Session isolation | Public/admin requests use separate session cookie names selected before `StartSession`. |
| Probe routes | Minimal `/_test/auth/*` routes exist only for boundary tests and are request-guarded with `app()->runningInConsole()`. |
| Delivery | The slice was completed in stacked work units, then committed and pushed to `main`. |
| Real-time | One-way server-to-client updates remain a future cross-cutting requirement; no realtime transport was implemented in this slice. |
| Next slice | The next recommended slice is `raffle-lifecycle-core`. |

## Files touched

Key implementation paths:

- `app/Models/Admin.php` — admin authenticatable model.
- `database/factories/AdminFactory.php` — admin factory.
- `database/migrations/0001_01_01_000003_create_admins_table.php` — admin identity table.
- `database/migrations/0001_01_01_000004_create_admin_password_reset_tokens_table.php` — admin password reset token table.
- `config/auth.php` — explicit public/admin guards, providers, and brokers.
- `config/session.php` — public/admin session cookie names.
- `app/Http/Middleware/ApplyIdentityBoundary.php` — request boundary detection and session cookie selection.
- `bootstrap/app.php` — prepends boundary middleware before session startup.
- `routes/web.php`, `routes/admin.php` — minimal guarded auth probes for tests.
- `app/Models/User.php`, `database/migrations/0001_01_01_000000_create_users_table.php` — clarified public identity boundary.
- `tests/Feature/Auth/AdminIdentityBoundaryTest.php` — admin identity contract tests.
- `tests/Feature/Auth/PublicIdentityBoundaryTest.php` — public identity and `AUTH_MODEL` regression tests.
- `tests/Feature/Auth/GuardSessionIsolationTest.php` — cross-host session and remember-me isolation tests.

OpenSpec paths:

- `openspec/specs/admin-identity-boundary/spec.md` — source-of-truth identity boundary spec.
- `openspec/specs/platform-foundation/spec.md` — updated platform foundation source spec.
- `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/` — archived SDD artifact trail.

## Pending files / areas

No dirty files at handoff time.

Remaining product areas are intentionally not implemented yet:

- Raffle lifecycle core.
- Entry/participation flow.
- Draw execution.
- Audit log implementation.
- Real-time transport for server-to-client updates.

Possible cleanup later:

- Move `/_test/auth/*` probe routes out of main route files into testing-only route registration instead of request-guarding them in normal route files.

## Commands executed

Verification commands used successfully:

```bash
bin/test tests/Feature/Auth/AdminIdentityBoundaryTest.php tests/Feature/Auth/PublicIdentityBoundaryTest.php
bin/test tests/Feature/Auth tests/Feature/Routing/HostSeparationTest.php
bin/test
bin/composer exec pint -- --test ...
```

Final verified results:

```text
12 tests, 74 assertions  # focused auth + routing verify
18 tests, 104 assertions # full suite
```

Git commands completed:

```bash
git commit -m "docs(openspec): normalize archived identity naming"
git commit -m "feat(auth): add admin identity boundary"
git push
```

Pushed commits:

```text
245b17e docs(openspec): normalize archived identity naming
d0a2fbb feat(auth): add admin identity boundary
```

## Known risks

- Running multiple `bin/test` processes in parallel can cause transient PostgreSQL migration failures. Use sequential test runs as verification evidence.
- `/_test/auth/*` probe routes are guarded, but still registered in main route files; this is accepted for now and can be cleaned up later.
- Real-time UI updates are a known future requirement, but no broadcasting/SSE/polling stack has been selected yet.

## Recommended next step

Start a new SDD change:

```text
raffle-lifecycle-core
```

Recommended scope:

- Add raffle model/table.
- Implement basic states: `draft`, `published`, `closed`.
- Add transition rules and domain tests.
- Keep entries, draw, audit, and realtime transport out of scope.
- Consider domain events in the design so later realtime updates can subscribe to lifecycle changes without retrofitting the core.
