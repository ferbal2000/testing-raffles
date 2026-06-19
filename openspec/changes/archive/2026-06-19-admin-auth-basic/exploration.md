## Exploration: admin-auth-basic

### Current State
The app already separates public and admin identity at the infrastructure level: `config/auth.php` defines an `admin` session guard, `admins` provider, and admin password broker; `ApplyIdentityBoundary` switches the session cookie by host; and `routes/admin.php` is mounted under the admin host. However, there is no real admin login form, no credential-based session creation route, no logout route, and no auth middleware protecting the admin home page. The current admin auth coverage is probe-oriented only, with console-only test routes proving guard/session isolation rather than product login behavior.

### Affected Areas
- `routes/admin.php` — currently exposes `admin.home` directly and only contains console-only auth probe routes; it is the main place to add guest/auth route groups.
- `bootstrap/app.php` — Laravel currently keeps the default guest redirect behavior (`route('login')`), so admin page protection needs an explicit redirect target for unauthenticated admin requests.
- `app/Http/Middleware/ApplyIdentityBoundary.php` — preserves host-driven session cookie isolation that the new admin auth flow must keep using.
- `config/auth.php` — already provides the correct `admin` guard/provider foundation that the login/logout flow should reuse.
- `resources/views/admin/home.blade.php` — current real admin page; likely the first route that must be protected.
- `app/Models/Admin.php` — authenticatable admin model used by credential login.
- `database/factories/AdminFactory.php` — provides a known test password (`password`) for feature coverage.
- `tests/Feature/Auth/GuardSessionIsolationTest.php` — existing proof that cross-boundary session leakage must remain impossible.
- `tests/Feature/Routing/HostSeparationTest.php` — confirms host-separated admin/public surfaces that the auth UX must respect.

### Approaches
1. **Native admin session auth on admin host** — add a small controller-backed form flow using Laravel's `Auth::guard('admin')`, guest/auth middleware groups, and an explicit admin guest redirect.
   - Pros: Reuses existing guard/provider/session isolation; smallest real slice; matches current Blade-first/Laravel conventions; easy to cover with feature tests.
   - Cons: Needs explicit unauthenticated redirect handling because the framework default points to missing `route('login')`; introduces the first admin-specific auth UI.
   - Effort: Medium

2. **Custom manual gate without `auth` middleware** — keep routes public but check `Auth::guard('admin')->check()` inside closures/controllers and redirect manually.
   - Pros: Avoids dealing with framework guest redirect configuration immediately.
   - Cons: Duplicates framework behavior; easier to miss future admin routes; weaker foundation for the next admin slices.
   - Effort: Medium

### Recommendation
Use native admin session auth on the admin host. Add a dedicated admin login page (`GET /login`), login submission (`POST /login`), logout (`POST /logout`), and protect the real admin page with `auth:admin`. Pair that with an explicit admin guest redirect strategy so unauthenticated admin requests go to the admin login route instead of Laravel's default `route('login')`. This is the smallest safe foundation because it builds on the existing `admin` guard and keeps the boundary rules centralized.

### Risks
- If `auth` middleware is added without overriding Laravel's default guest redirect, unauthenticated admin requests will try to resolve `route('login')`, which does not exist in this codebase.
- Protecting the admin root changes current host-separation expectations, so tests that now expect `200 OK` for the admin home as a guest will need to move to the login page or assert redirect behavior instead.
- Login/logout tests must stay host-aware; passing requests through the wrong host would give false confidence about the admin boundary.

### Ready for Proposal
Yes — propose a narrow slice limited to admin-host login form/session creation, logout invalidation, admin-page protection, and feature tests proving guest redirect, successful login, failed login, and logout behavior without changing public auth.
