# Design: Admin Auth Basic

## Technical Approach

Implement a small Laravel session-auth slice on the admin host only. Reuse the existing `admin` guard/provider/session boundary, add controller-backed `GET/POST /login` plus `POST /logout`, protect `admin.home` with `auth:admin`, and configure an explicit admin guest redirect in `bootstrap/app.php`. This maps directly to `admin-session-authentication` and preserves the isolation guarantees required by `admin-identity-boundary`.

## Architecture Decisions

### Decision: Use native `auth:admin` middleware with explicit guest redirect

| Option | Tradeoff | Decision |
|---|---|---|
| Keep manual `Auth::guard('admin')->check()` gates in routes/controllers | Duplicates framework behavior and is easy to miss on future routes | Rejected |
| Use `auth:admin` and override guest redirect centrally | Requires bootstrap configuration, but keeps protection consistent | Chosen |

**Rationale**: Laravel 13.8 defaults guest redirects to `route('login')` in `ApplicationBuilder`, which would fail here. Centralizing `redirectGuestsTo()` keeps route protection conventional while removing the missing-route risk.

### Decision: Make redirect resolution admin-aware and non-global

| Option | Tradeoff | Decision |
|---|---|---|
| Point every guest redirect to `route('admin.login')` | Leaks admin UX into non-admin contexts | Rejected |
| Return `route('admin.login')` only for admin-host/admin-boundary HTML requests; return `null` otherwise | Future public auth will need its own rule later | Chosen |

**Rationale**: The callback should inspect `identity_boundary` and host, so admin HTML requests redirect to `admin.login` and other contexts do not mix boundaries. JSON/API-like requests still get framework 401 behavior because `Authenticate` skips redirect targets when `expectsJson()` is true.

### Decision: Use one admin session controller, not route closures

| Option | Tradeoff | Decision |
|---|---|---|
| Keep auth flow inline in `routes/admin.php` | Fast initially, but validation/session logic bloats the route file | Rejected |
| Add `AuthenticatedSessionController` with inline validation | One new class, cleaner expansion path for later admin slices | Chosen |

**Rationale**: The codebase is still small, but login/logout already need validation, session regeneration, intended redirects, invalidation, and error responses. A single controller keeps `routes/admin.php` readable without introducing extra request classes yet.

## Data Flow

Guest admin request:

    Admin browser -> routes/admin.php -> guest:admin -> login view

Protected page request:

    Admin browser -> auth:admin -> redirectGuestsTo(admin-aware) -> route('admin.login')

Successful login:

    POST /login -> Auth::guard('admin')->attempt(...) -> session()->regenerate()
               -> redirect()->intended(route('admin.home'))

Logout:

    POST /logout -> Auth::guard('admin')->logout()
                 -> session()->invalidate() -> regenerateToken()
                 -> redirect(route('admin.login'))

`ApplyIdentityBoundary` remains first in the `web` stack, so the admin flow continues using the admin session cookie and does not share public auth state.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `bootstrap/app.php` | Modify | Add `redirectGuestsTo()` callback for admin-aware unauthenticated redirects; optionally pair with `redirectUsersTo()` for `guest:admin` routes. |
| `routes/admin.php` | Modify | Add guest/auth route groups, name `admin.login`/`admin.logout`, and move `admin.home` behind `auth:admin`. Keep probe routes console-only. |
| `app/Http/Controllers/Admin/Auth/AuthenticatedSessionController.php` | Create | Handle login form display, credential attempt, session regeneration, and logout invalidation. |
| `resources/views/admin/auth/login.blade.php` | Create | Blade login form using the existing app layout. |
| `resources/views/admin/home.blade.php` | Modify | Add authenticated admin affordance, including logout form. |
| `lang/es/admin-auth.php` | Create | Store Spanish UI copy for labels, button text, and invalid credential message. |
| `tests/Feature/Auth/AdminSessionAuthenticationTest.php` | Create | Host-aware feature tests for login page, guest redirect, success, failure, and logout. |
| `tests/Feature/Auth/GuardSessionIsolationTest.php` | Modify | Add/assert that admin login/logout does not authenticate the public boundary. |
| `tests/Feature/Routing/HostSeparationTest.php` | Modify | Update expectations: admin host guest root redirects to login, while `/login` serves admin copy. |

## Interfaces / Contracts

```php
final class AuthenticatedSessionController
{
    public function create(Request $request): View;
    public function store(Request $request): RedirectResponse;
    public function destroy(Request $request): RedirectResponse;
}
```

Login uses `Auth::guard('admin')->attempt(['email' => ..., 'password' => ...])`. On success it MUST regenerate the session and redirect to the intended admin page; on failure it MUST redirect back with validation errors and no admin session.

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | None added | Behavior is mostly HTTP/session orchestration; value is low here. |
| Integration | Admin auth lifecycle and boundary safety | Pest feature tests using `HTTP_HOST` = `app.admin_url`, admin factory credentials, redirects, session assertions, and cross-host probes. |
| E2E | N/A | No E2E harness exists in this repo. |

## Migration / Rollout

No migration required.

## Open Questions

- [ ] None.
