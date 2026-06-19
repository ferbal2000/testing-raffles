# Tasks: Admin Auth Basic

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 300-380 |
| 400-line budget risk | Medium |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-always |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Deliver admin login, logout, redirects, and host-aware tests | Single PR | Keep RED/GREEN/REFACTOR together with `bin/test` coverage |

## Phase 1: Test-First Routing Foundation

- [x] 1.1 RED: Update `tests/Feature/Routing/HostSeparationTest.php` so admin host `/` now redirects guests to `route('admin.login')` and admin `/login` still renders admin copy.
- [x] 1.2 RED: Create `tests/Feature/Auth/AdminSessionAuthenticationTest.php` for admin-host guest login page, protected-page redirect, explicit redirect handling, valid login, invalid login, logout, and post-logout redirect.
- [x] 1.3 RED: Extend `tests/Feature/Auth/GuardSessionIsolationTest.php` to prove admin login/logout never authenticates the public boundary before or after the flow.

## Phase 2: Redirect and Route Wiring

- [x] 2.1 GREEN: Update `bootstrap/app.php` with host/context-aware `redirectGuestsTo()` so admin HTML guests go to `admin.login` and JSON/API-like requests still resolve as 401.
- [x] 2.2 GREEN: Add `app/Http/Controllers/Admin/Auth/AuthenticatedSessionController.php` with `create`, `store`, and `destroy` using `Auth::guard('admin')`, session regeneration, invalid-credential errors, invalidation, and token refresh.
- [x] 2.3 GREEN: Refactor `routes/admin.php` into `guest:admin` login routes, `auth:admin` protected home/logout routes, and keep `/_test/auth/*` probes console-only.

## Phase 3: Admin UI Slice

- [x] 3.1 GREEN: Create `resources/views/admin/auth/login.blade.php` with email/password inputs, CSRF, validation feedback, and submit action to `route('admin.login.store')`.
- [x] 3.2 GREEN: Update `resources/views/admin/home.blade.php` to show authenticated admin copy plus a visible POST logout control.
- [x] 3.3 GREEN: Create `lang/es/admin-auth.php` for login labels, button text, and invalid-credentials messaging used by the admin auth views.

## Phase 4: Verification and Refactor

- [x] 4.1 REFACTOR: Tighten `tests/Feature/Auth/AdminSessionAuthenticationTest.php` around intended redirect-to-home behavior and no-session assertions for failed login.
- [x] 4.2 REFACTOR: Recheck `tests/Feature/Routing/HostSeparationTest.php` and `tests/Feature/Auth/GuardSessionIsolationTest.php` for host-aware helpers only, avoiding public-auth regressions.
- [x] 4.3 VERIFY: Run targeted `bin/test` coverage for the three auth/routing feature files and confirm every scenario in both OpenSpec specs is exercised.
