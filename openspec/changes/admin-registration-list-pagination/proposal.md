# Proposal: Paginate the Admin Registration List

**Approved issue:** [#56](https://github.com/ferbal2000/testing-raffles/issues/56)

## Intent

Provide a URL-addressable list without full reloads during pagination, flag, cancel, and restore workflows.

## Scope

### In Scope
- Fixed 25-row pages with canonical `?page=N`, back/forward support, invalid/out-of-range handling, and accessible loading, error, focus, and live-region behavior.
- A Blade-hosted progressive Vue island. Blade renders the initial/failure table and whole-raffle counts read-only, without forms or mutation fallback.
- Native confirmations, including an explicit terminal/non-restorable cancellation warning; screen-wide blocking during every pending operation; temporary success toast plus accessible announcement.
- Never auto-retry uncertain mutations. Attempt safe GET reconciliation; on failure, preserve data, declare the outcome unresolved, and keep mutations blocked. Session expiry preserves data, blocks actions, and offers an admin login path.

### Out of Scope
- Cross-browser realtime/server push, polling, search, filters, export, bulk actions, configurable page size, public pagination, index tuning, and concurrency/load redesign.

## Capabilities

### New Capabilities
None.

### Modified Capabilities
- `admin-raffle-participation-list`: add paginated read-only Blade output and same-browser Vue reactivity, async moderation, recovery, history, and accessibility contracts.
- `realtime-update-candidate-map`: clarify that same-browser Vue updates are delivered interaction behavior, while cross-browser propagation remains future-only realtime work.

## Approach

Use authenticated routes with content negotiation and one server-authored snapshot for Blade, JSON GETs, and mutations. Mount one page-scoped Vue island; keep state server-authoritative.

## Affected Areas

| Area | Impact |
|---|---|
| Admin controller and snapshot serialization | Paginated HTML/JSON contract |
| `resources/views/admin/raffles/registrations.blade.php`, shared layout, Spanish copy | Read-only boundary, mount data, CSRF, messaging |
| `resources/js/app.js`, feature-scoped Vue files | Island interaction and recovery |
| Frontend package/Vite configuration | Vue and Vitest foundation |
| PHP and JavaScript tests | Server and component contracts |

## Risks and Mitigations

- **Foundation risk:** Vue/Vitest do not exist; introduce only the minimal tested foundation.
- **State drift/races:** derive representations from one snapshot and serialize operations.
- **No browser E2E:** use component tests and explicit manual accessibility verification.

## Rollback Plan

Revert the three integrated work units together, restoring the prior server-rendered list and dependencies without data migration.

## Dependencies and Delivery

Vue 3, Vite integration, Vitest, Vue Test Utils, and a DOM environment are required. Forecast: 950–1,400 authored lines, excluding generated lockfile/archive movement. Deliver exactly three Feature Branch Chain units; no child targets `main`, no size exception or stacked-to-main path, and only the integrated tracker reaches `main`.

## Success Criteria

- [ ] Listed interactions avoid normal full reload and preserve authoritative rows and whole-raffle counts.
- [ ] Pagination, recovery, expiry, global blocking, confirmation, feedback, URL, focus, and announcement behaviors pass server/component verification.
- [ ] Blade remains useful but mutation-free when Vue is unavailable; no cross-browser realtime is introduced.
