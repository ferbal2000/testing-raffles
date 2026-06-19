# Proposal: Admin Auth Basic

## Intent

Turn the existing admin identity boundary into a usable product flow so real admin pages require an authenticated admin session instead of relying on probe-only routes.

## Scope

### In Scope
- Admin-host login page and credential submission using the existing `admin` guard.
- Admin logout that invalidates the admin session cleanly.
- Protection for real admin pages plus guest redirect to the admin login route.
- Feature tests for guest redirect, successful login, failed login, and logout.

### Out of Scope
- Password recovery, roles, permissions, or admin account management.
- Raffle management UI, public auth changes, or cross-slice work in `admin-raffle-management-basic`.

## Capabilities

### New Capabilities
- `admin-session-authentication`: Admin-host sign-in, sign-out, and protected-page access behavior.

### Modified Capabilities
- `admin-identity-boundary`: Verification expands from probe-only isolation to real admin auth flow coverage while preserving guard/session separation.

## Approach

Use Laravel's native session auth on the admin host: guest routes for `GET/POST /login`, an authenticated `POST /logout`, and `auth:admin` on real admin pages. Add an explicit admin guest redirect in the Laravel bootstrap path so unauthenticated admin requests never depend on missing `route('login')`.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/admin.php` | Modified | Add guest/auth groups, login/logout routes, and protect `admin.home`. |
| `bootstrap/app.php` | Modified | Define admin-aware guest redirect behavior. |
| `resources/views/admin/` | Modified/New | Add login form view and keep admin home behind auth. |
| `tests/Feature/Auth/` | Modified/New | Cover login, logout, redirect, and boundary-safe auth behavior. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| `auth` redirects to missing `login` route | Med | Configure explicit admin guest redirect before adding protection. |
| Host-agnostic tests give false confidence | Med | Keep all auth tests bound to `app.admin_url` host. |

## Rollback Plan

Remove admin login/logout routes and guest redirect override, restore `admin.home` to its prior public route behavior, and drop new auth feature tests.

## Dependencies

- Existing `admin` guard/provider/session isolation in `config/auth.php` and `ApplyIdentityBoundary`.

## Success Criteria

- [ ] Guests requesting admin pages are redirected to the admin login page on the admin host.
- [ ] Valid admin credentials create an admin session; invalid credentials do not.
- [ ] Logout clears the admin session without affecting the public boundary.
