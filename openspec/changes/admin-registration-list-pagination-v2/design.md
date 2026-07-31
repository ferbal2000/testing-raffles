# Design: Resilient Admin Registration List Pagination v2

## Technical Approach

Laravel owns pagination, transitions, localization, and one versioned snapshot contract. Blade renders that contract completely for no-JavaScript use and embeds XSS-safe JSON; JavaScript mounts Vue only after full validation. The enhanced client is a non-optimistic state machine that never replaces confirmed data with unvalidated input and never retries a POST.

## Architecture Decisions

| Topic | Decision and rationale |
|---|---|
| Server authority | `RaffleRegistrationSnapshot` builds rows, whole-raffle counts, allowed POST actions, localized copy, login URL, and pagination (`current`, `last`, `perPage=25`, `total`, `canonicalUrl`, links). Rows are descending IDs. The same builder serves HTML hydration, negotiated GET 200, mutation 200, and expected stale 409, preventing contract drift. Counts and rows are authoritative at response time, not a serializable database-wide view. |
| Canonicalization | A page is valid only when its sole scalar value is positive decimal digits. Missing renders page 1 at the queryless URL. Explicit `1`, malformed/array/zero/negative redirect there for HTML; over-last redirects to the last URL (queryless when last=1); valid `2..last` renders. Negotiated JSON never redirects: it returns 200 plus the canonical snapshot/URL. |
| Mutations | Forms carry canonical `page`. HTML POST uses existing redirect/flash semantics. Negotiated POST locks the raffle-scoped row: valid transition returns snapshot+feedback 200; unavailable transition returns authoritative snapshot+feedback 409; missing/cross-raffle/malformed identity returns bare 404 without snapshot. |
| Enhancement boundary | Blade supplies complete links and CSRF forms for flag/cancel/restore, then embeds `Js::encode($snapshot)` in `type="application/json"`. `app.js` parses and validates the entire schema before mounting; failure leaves fallback DOM untouched. Snapshot actions are an allow-listed `{kind,label,confirm,url,method:"POST"}` union. |
| Tooling | Current ESM stack has Vite 8.1 and Node 22. Add compatible Vue 3.5, `@vitejs/plugin-vue` 6, Vitest 4, Vue Test Utils 2, and jsdom; configure Vue and jsdom in existing `vite.config.js`. Extend `bin/test` as the canonical wrapper to run deterministic Vitest and Pest suites. No installation occurs in design. |

## Operation State

| State | Confirmed data / enabled controls | Permitted transitions |
|---|---|---|
| `idle` | Visible; links and valid actions enabled | GET→`navigating`; POST→`mutating` |
| `navigating` | Retained; in-screen controls disabled | valid latest GET→`idle`; failure→`idle`; 401/419→`expired` |
| `mutating` | Retained; all in-screen controls disabled | valid 200/409→`idle`; uncertainty→`reconciling`; 401/419→`expired` |
| `reconciling` | Retained; all controls disabled | valid GET→`idle`; failure→`unresolved`; 401/419→`expired` |
| `unresolved` | Retained; only GET-retry/login enabled | retry→`reconciling`; 401/419→`expired` |
| `expired` | Retained; only login enabled | terminal |

Each navigation has an AbortController and generation token; superseded/late results cannot commit. Mutation aborts navigation. During mutation/reconciliation, only the latest `popstate` URL is retained; after authoritative resolution it is consumed once as a GET, after unresolved it waits for successful retry, and expiry clears it. Click success commits then `pushState` once; server canonicalization commits then `replaceState` once; same-URL navigation and every `popstate` perform no history write.

Uncertain POST outcomes (transport, malformed payload, unexpected status) trigger exactly one reconciliation GET. Failed reconciliation exposes GET-only retry; retries add one GET each and never repeat POST. Any asynchronous 401/419 aborts/invalidates peers, preserves data, clears deferral, writes no history, and exposes login. Successful navigation focuses a `tabindex="-1"` heading and announces page identity; errors retain focus and announce through an `aria-live` region. Copy remains server-localized.

## File Changes

| Action | Paths |
|---|---|
| Create | `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php`; `resources/js/admin/raffle-registrations/{snapshot.js,RaffleRegistrations.vue,RaffleRegistrations.test.js}` |
| Modify | `app/Http/Controllers/Admin/RaffleController.php`; `routes/admin.php`; `resources/views/admin/raffles/registrations.blade.php`; `lang/es/admin-raffles.php`; `resources/js/app.js`; `vite.config.js`; `package.json`; `package-lock.json`; `bin/test`; `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` |

## Testing Strategy

Strict RED-GREEN-REFACTOR uses `bin/test`. Pest feature matrices use explicit IDs/statuses to prove 25-row boundaries, ordering/non-overlap/counts, HTML redirect versus JSON 200 canonicalization, fallback forms/CSRF, shared 200/409 schema, XSS hydration, 401/419, and numeric/nonexistent/cross-raffle 404s. Vitest controls deferred promises and history spies: superseded GET permutations, late commits, repeated popstates, focus/live announcements, invalid hydration/no mount, and every expiry source. Assert one push or replace (never both), zero writes for popstate, one POST/zero GET for valid 200/409, one POST/one automatic GET for uncertainty, then exactly one GET per safe retry.

## Threat Matrix

| Boundary | Applicability | Safe/failure behavior and planned RED test |
|---|---|---|
| Application HTTP routes | Applicable | Numeric raffle/registration IDs bind only within the raffle; malformed, absent, or cross-raffle identities fail 404 without mutation/snapshot. Pest covers every action symmetrically. |
| Documentation-like paths | N/A | No file classification or execution boundary. |
| Git repository selection | N/A | No Git invocation. |
| Commit state | N/A | No commit automation. |
| Push state | N/A | No push automation. |
| PR commands | N/A | No PR automation. |

## Migration / Rollout

No migration or realtime transport: Candidate remains documentation-only. Rollback removes Vue/Vitest/tooling additions and restores the unpaginated Blade controller/view and synchronous forms.

## Open Questions

None.
