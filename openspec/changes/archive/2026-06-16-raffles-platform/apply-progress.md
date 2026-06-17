# Apply Progress: Raffles Platform Foundation Slice

## Change

- Change name: `raffles-platform`
- Mode: Strict TDD
- Delivery: chained PR slice (`stacked-to-main`)
- Current work unit: PR 1 / Work Unit 1
- Boundary: Laravel scaffold + Pest/PHPUnit bootstrap + PostgreSQL base config + admin/public route smoke tests + Docker Compose local runtime + wrapper scripts

## Verification Follow-up Fixes

- [x] Resolved the PR 1 verify warning for inline Spanish literals in `resources/views/public/home.blade.php` and `resources/views/admin/home.blade.php` by moving the copy to Laravel translation keys/files.
- [x] Resolved the PR 1 verify warning about agent/tooling metadata and public/admin identity ambiguity by ignoring `.atl/` in Git and documenting that Laravel `User` / `users` currently means public website users only.
- [x] Resolved the PR 1 pre-commit README routing warning by aligning local runtime docs with the actual host-scoped public/admin domains and the `.test` vs real-DNS environment convention.

## Completed Tasks

- [x] 1.1 Create Laravel app scaffold (`artisan`, `composer.json`, `bootstrap/`, `app/`, `config/`, `routes/`) with PostgreSQL defaults and Vite enabled.
- [x] 1.2 Add Pest/PHPUnit runner setup in `tests/Pest.php`, `phpunit.xml`, `tests/TestCase.php`, and wire the initial test command plus `.env.example` DB/app URLs.
- [x] 1.3 Write RED smoke tests in `tests/Feature/HealthCheckTest.php` and `tests/Feature/Routing/HostSeparationTest.php`; make them pass with `routes/web.php` and `routes/admin.php` placeholders.
- [x] 1.4 Add `compose.yaml` and `docker/php/Dockerfile` so PHP 8.3+, Composer, and PostgreSQL run in containers; keep CI and production images out of scope.
- [x] 1.5 Add `bin/test`, `bin/artisan`, `bin/composer`, and `bin/dev` (optional `bin/npm`); update `README.md` and `openspec/config.yaml` so RED/GREEN runs through wrappers, not host PHP/Composer.

## Files Changed

| File | Action | What changed |
|------|--------|--------------|
| `compose.yaml` | Created | Added local-only app/db runtime with PHP container build, PostgreSQL service, and mounted workspace. |
| `docker/php/Dockerfile` | Created | Added PHP 8.3 CLI image with Composer, PostgreSQL extensions, Node 22 tooling, and git safe-directory config. |
| `bin/test` | Created | Added canonical Dockerized Pest runner wrapper. |
| `bin/artisan` | Created | Added Dockerized Artisan wrapper. |
| `bin/composer` | Created | Added Dockerized Composer wrapper. |
| `bin/dev` | Created | Added local runtime launcher for app + database. |
| `bin/npm` | Created | Added optional Dockerized Node/Vite wrapper for scaffold asset commands. |
| `README.md` | Updated | Replaced stock Laravel README with wrapper-first local setup instructions. |
| `openspec/config.yaml` | Updated | Set `bin/test` as canonical apply/verify runner and documented wrapper-first TDD. |
| `.env.example` | Updated | Switched the default database host to the Compose PostgreSQL service. |
| `tests/Feature/Tooling/ContainerRuntimeTest.php` | Created | Added RED/GREEN coverage for compose runtime, wrappers, and canonical test command wiring. |
| `phpunit.xml` | Updated | Added testing APP key so containerized HTTP tests can boot consistently. |
| `resources/views/components/layouts/app.blade.php` | Updated | Made Vite assets optional until a manifest or hot file exists, preventing smoke test failures before asset build. |
| `tests/Feature/Routing/HostSeparationTest.php` | Updated | Switched smoke requests to absolute URLs so host separation verification uses the intended domain. |
| `resources/views/public/home.blade.php` | Updated | Replaced inline Spanish literals with `home.public.*` translation keys. |
| `resources/views/admin/home.blade.php` | Updated | Replaced inline Spanish literals with `home.admin.*` translation keys. |
| `lang/es/home.php` | Created | Added Spanish translation lines for the public and admin home placeholders. |
| `tests/Feature/Routing/HomeTranslationsTest.php` | Created | Added focused RED/GREEN coverage proving both home views resolve copy from translation keys. |
| `.gitignore` | Updated | Ignored `.atl/` metadata while preserving the personal `notas.txt` ignore. |
| `config/auth.php` | Updated | Added explicit identity-boundary metadata and comments clarifying that the default Laravel `User` / `users` wiring is public-site only in this slice. |
| `app/Models/User.php` | Updated | Added model-level documentation that `User` represents public website users only for PR 1 / Work Unit 1. |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Updated | Added migration comments clarifying that `users` is the public identity table for the current slice only. |
| `README.md` | Updated | Documented the temporary Laravel naming boundary so future work keeps admin identity separate. |
| `README.md` | Updated | Corrected local browser/runtime guidance so host-scoped routing uses `www.raffles.test` and `admin.raffles.test`, and documented `.test` locally vs real DNS outside local. |
| `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Created | Added focused RED/GREEN coverage for the public-only `User` / `users` boundary and the planned separate admin identity. |

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 1.1 | `tests/Feature/HealthCheckTest.php`, `tests/Feature/Routing/HostSeparationTest.php` | Integration | Inherited from prior batch before container runner existed | ✅ Written in prior batch | ✅ Passed via `bin/test` in this batch | ✅ Multiple smoke scenarios | ➖ Prior batch scaffold; no new refactor |
| 1.2 | `tests/Feature/HealthCheckTest.php`, `tests/Feature/Routing/HostSeparationTest.php` | Integration | Inherited from prior batch before container runner existed | ✅ Written in prior batch | ✅ Passed via `bin/test` in this batch | ✅ Multiple smoke scenarios | ✅ Added testing `APP_KEY` for operational runner consistency |
| 1.3 | `tests/Feature/Routing/HostSeparationTest.php` | Integration | ✅ Existing smoke suite executed through `bin/test` | ✅ Written in prior batch | ✅ Passed via `bin/test` in this batch | ✅ Public host, admin host, and unknown host cases | ✅ Updated requests to absolute URLs for real host matching |
| 1.4 | `tests/Feature/Tooling/ContainerRuntimeTest.php` | Integration | N/A (new files) | ✅ Written first | ✅ `./bin/test tests/Feature/Tooling/ContainerRuntimeTest.php` passed | ➖ Structural runtime task; compose and db cases covered in one file | ✅ Added git safe-directory config after first containerized install warning |
| 1.5 | `tests/Feature/Tooling/ContainerRuntimeTest.php` | Integration | ✅ Existing smoke suite and tooling test executed through `bin/test` | ✅ Written first | ✅ `./bin/test` passed | ✅ Wrapper scripts + canonical command assertions | ✅ README/config/docs aligned to wrapper-first runner |
| WU1 verify warning fix | `tests/Feature/Routing/HomeTranslationsTest.php` | Integration | ✅ `./bin/test tests/Feature/Routing/HostSeparationTest.php` baseline passed (3 tests, 7 assertions) | ✅ Wrote translation-key rendering tests first | ✅ `./bin/test tests/Feature/Routing/HomeTranslationsTest.php` passed | ✅ Public and admin translation overrides verified | ➖ No further refactor needed |
| WU1 pre-commit identity/gitignore fix | `tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Integration | ✅ `./bin/test` baseline passed (9 tests, 37 assertions) before touching auth/user files | ✅ Wrote identity-boundary config tests first | ✅ `./bin/test tests/Feature/Auth/PublicIdentityBoundaryTest.php` passed | ✅ Public-boundary mapping plus separate admin-plan scenario verified | ✅ Added explicit config/docs/comments without expanding auth scope |
| WU1 pre-commit README routing fix | Existing routing/tooling suite | Integration | ✅ `./bin/test` baseline passed (11 tests, 44 assertions) before editing docs | ➖ Documentation-only follow-up; no new executable behavior to drive with a failing test | ✅ `./bin/test` passed after the README correction | ➖ Triangulation skipped: documentation-only scope, behavior already covered by existing routing tests | ✅ Refined README structure around environment domains, hosts entries, and browser URLs |

## Commands Run

| Command | Result |
|---------|--------|
| `docker --version` | Passed |
| `docker compose version` | Passed |
| `docker compose config` | Passed after removing required `env_file` dependency on a missing local `.env` |
| `./bin/test tests/Feature/Tooling/ContainerRuntimeTest.php` | Passed (3 tests, 21 assertions) |
| `./bin/test` | Initially failed on missing `APP_KEY`, then failed on Vite manifest and host-routing verification, then passed after fixes (7 tests, 29 assertions) |
| `./bin/artisan route:list` | Passed |
| `./bin/artisan tinker --execute='dump(config("app.public_url"), config("app.admin_url"));'` | Passed |
| `./bin/test tests/Feature/Routing/HostSeparationTest.php` | Passed baseline before the translation-key follow-up (3 tests, 7 assertions) |
| `./bin/test tests/Feature/Routing/HomeTranslationsTest.php` | Failed first because the views still rendered inline Spanish literals; passed after moving copy to translation keys/files (2 tests, 8 assertions) |
| `./bin/test` | Passed after the translation-key follow-up (9 tests, 37 assertions) |
| `./bin/test` | Passed baseline before the pre-commit identity/gitignore fix (9 tests, 37 assertions) |
| `./bin/test tests/Feature/Auth/PublicIdentityBoundaryTest.php` | Failed first because `auth.identity_boundary.*` metadata was not defined; passed after adding explicit public/admin boundary config (2 tests, 7 assertions) |
| `./bin/test` | Passed after the pre-commit identity/gitignore fix (11 tests, 44 assertions) |
| `./bin/test` | Passed after the README routing/docs fix (11 tests, 44 assertions) |

## Verification Status

- Strict TDD can now be enforced operationally through `bin/test` when Docker is available.
- Current verification result: `./bin/test` passes.
- The PR 1 Blade i18n warning is resolved: the public/admin placeholder copy now comes from `lang/es/home.php` via stable translation keys.
- The PR 1 pre-commit warnings are resolved: `.atl/` is ignored and the Laravel `User` / `users` naming is now explicitly documented as public-site-only for this slice.
- The PR 1 README now matches the actual host-scoped runtime by documenting `www.raffles.test` and `admin.raffles.test` for local, plus real-DNS examples for staging/production.

## Deviations from Design

- None. The runtime remains local-development focused and keeps CI/production containerization out of scope.

## Issues Found

- Running tests inside containers exposed three bootstrap blockers from the pre-container scaffold: missing test `APP_KEY`, Vite manifest coupling in the shared layout, and host smoke requests that needed absolute URLs to exercise domain routing correctly.
- The verify warning about inline Spanish literals in the public/admin Blade placeholders is resolved in this batch.
- Keeping the Laravel `User` model in PR 1 conflicts with the long-term separate-identity design unless the boundary is made explicit now; this batch resolves that ambiguity in config, docs, comments, and tests without implementing admin auth early.
- The README had drifted from the actual routing contract by mentioning `localhost` as the browser target even though the route registration is bound to the configured public/admin hosts; this batch fixes the documentation only.

## Remaining Tasks

- [ ] 2.1 Write RED feature tests in `tests/Feature/Auth/AdminGuardIsolationTest.php` and `tests/Feature/Auth/PublicGuardIsolationTest.php` using `bin/test`.
- [ ] 2.2 Historical wording corrected: add migration for `admins` and create `app/Modules/IdentityAdmin/Admin.php`; keep Laravel `users` / `App\Models\User` as the public identity boundary instead of creating a separate public identity table/model.
- [ ] 2.3 Configure `config/auth.php`, `config/session.php`, and route/domain middleware so admin and public cookies/guards stay isolated; make Phase 2 tests pass.

## Workload / PR Boundary

- Mode: stacked PR slice
- Current work unit: PR 1 / Work Unit 1
- Boundary: foundation runtime only; stops before identity models, domain migrations, raffle lifecycle, entries, draw flow, and audit log implementation
- Review budget note: the repository still has no initial commit, so the eventual PR diff will include scaffold breadth even though the logical work unit stayed limited.

## Status

5/14 tasks complete. Ready for verify on PR 1 scope; the inline-Blade i18n warning, pre-commit identity/gitignore warnings, and README host-routing warning are resolved.
