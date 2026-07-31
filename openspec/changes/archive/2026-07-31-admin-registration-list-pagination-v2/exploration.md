## Exploration: Admin Registration List Pagination v2

Approved issue: [#61](https://github.com/ferbal2000/testing-raffles/issues/61)

### Current State

The clean branch is based on `0edffb4d8b89e5f1c39f9eec7d05d80bdaf9a6b3`. The admin-host route renders one Blade page through `Admin\RaffleController::registrations()`. That action eagerly loads every registration newest-first and computes whole-raffle status counts. The Blade view renders the complete table and synchronous POST forms for flag, cancel, and flagged-to-active restore. HTML guests redirect to login and JSON guests receive 401 through `auth:admin`; CSRF remains provided by the web middleware.

There is no current Vue runtime, JSON snapshot resource, JavaScript test stack, or pagination behavior. `resources/js/app.js` is empty, and Vite has no Vue/Vitest integration. The stable participation-list specification requires protected newest-first rows, whole-raffle summaries, bounded status actions, sparse/empty states, and no invented workflow semantics, but does not yet define pagination or client-side recovery.

Read-only inspection of `stash@{0}` and `stash@{1}` confirms that the previous slice explored a server-authored snapshot plus progressive Vue enhancement. Those implementations remain historical evidence only. Reusable concepts were revalidated against current main; old change identities, issue/PR references, receipts, dependency state, and remediation artifacts are not active inputs.

The v2 state contract must explicitly cover:

- Canonical server pagination of exactly 25 rows, ordered by descending registration ID, with non-overlapping pages and whole-raffle counts. Missing `page` is canonical page 1; malformed, zero, negative, and over-last-page values must resolve deterministically without showing a false empty state.
- Real page URLs and progressive fallback. HTML noncanonical requests should redirect; negotiated JSON may return the canonical snapshot. Client navigation must fetch before committing data/history, push only successful canonical navigation, replace canonicalized URLs, handle `popstate` without adding history, abort superseded page GETs, and ignore late responses.
- One authoritative snapshot shape for initial HTML hydration, page GETs, mutation success, and stale-transition conflict. Snapshot validation must reject incomplete rows and malformed nested actions before replacing confirmed data.
- No optimistic status mutation. A confirmed 200 commits the returned snapshot; an expected stale transition commits an authoritative 409 snapshot and error feedback. A transport loss, malformed response, or unexpected server failure leaves the POST outcome uncertain and triggers exactly one safe reconciliation GET, never a repeated POST.
- Mutation/request races. Page-link navigation is blocked during mutation or reconciliation; `popstate` reached during those states is deferred and consumed once only after resolution. Superseded page GETs cannot overwrite mutation or reconciliation state. If reconciliation fails, confirmed rows remain visible, mutation and navigation remain blocked as appropriate, and retry performs only another GET.
- Session expiry from every asynchronous source: pagination GET, mutation POST, initial reconciliation GET, and reconciliation retry must treat 401 and 419 as terminal, preserve confirmed data, clear deferred history, block further in-screen actions/navigation, and expose the login route. No reconciliation follows a direct 401/419 mutation result.
- Unresolved-state navigation semantics, exact POST/GET/history call counts, focus and live-region behavior, and deterministic server fixtures. Fixtures must use explicit IDs/order/status distribution rather than faker ordering assumptions.
- Nested action hardening for flag, cancel, and restore. Cross-raffle registration IDs, nonnumeric raffle IDs, and nonnumeric registration IDs must fail without mutation or a misleading snapshot; all action routes need symmetric numeric constraints.
- Realtime-impact maintenance. Pagination and request-response recovery do not add runtime broadcasting, but proposal/spec/design must evaluate whether the existing registration-list candidate description needs clarification while preserving the documentation-only boundary.

### Affected Areas

- `app/Http/Controllers/Admin/RaffleController.php` — replace eager loading with canonical pagination and negotiate HTML/JSON snapshots and mutation feedback while retaining transactional row locking.
- `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php` — new server-owned serialization boundary for rows, counts, pagination URLs, allowed actions, localized interaction copy, and canonical page identity.
- `routes/admin.php` — preserve admin/web middleware and apply symmetric numeric constraints to all nested registration actions.
- `resources/views/admin/raffles/registrations.blade.php` — provide an accessible server-rendered fallback and safely embed the initial snapshot for progressive enhancement.
- `lang/es/admin-raffles.php` — add pagination, busy, retry, expiry, unresolved, reconciliation, and terminal-cancellation copy while preserving Spanish UI conventions.
- `resources/js/app.js` — mount the registration component defensively and leave the Blade fallback intact when hydration data is invalid.
- `resources/js/admin/raffle-registrations/RaffleRegistrations.vue` — new navigation, history, mutation, reconciliation, session-expiry, focus, and announcement state machine.
- `resources/js/admin/raffle-registrations/RaffleRegistrations.test.js` — new deterministic unit tests for snapshots, races, exact request/history counts, malformed actions, expiry sources, and fallback mounting.
- `package.json`, `package-lock.json`, `vite.config.js` — Vue and Vitest tooling required by the component and unit tests; versions must be selected fresh rather than copied blindly from a stash.
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — server pagination, canonicalization, negotiation, CSRF, 200/409/error payloads, XSS-safe hydration, nested-route hardening, and existing behavior regressions.
- `database/factories/RaffleRegistrationFactory.php` and `tests/Feature/Raffles/RaffleAdminTestSupport.php` — likely test-support inputs; prefer explicit per-test attributes and deterministic creation order before changing shared helpers.
- `openspec/specs/admin-raffle-participation-list/spec.md` — future delta must define pagination and recovery behavior without weakening existing visibility/action requirements.
- `openspec/specs/realtime-update-candidate-map/spec.md` — future spec/design review must record whether clarification is needed; no runtime realtime implementation belongs in this slice.

### Approaches

1. **Blade-only full-page pagination** — use Laravel paginator links and retain synchronous POST/redirect actions.
   - Pros: smallest runtime surface; no new frontend dependencies; browser history and authentication remain native.
   - Cons: does not satisfy the evidenced in-place navigation, confirmed-data preservation, request race, POST uncertainty, or reconciliation requirements.
   - Effort: Low

2. **Fresh server snapshot with progressively enhanced Vue state machine** — keep accessible Blade output as fallback, make the server authoritative, and enhance navigation/actions only after validated hydration.
   - Pros: supports canonical URLs, recoverable navigation, exact race handling, no-optimism mutations, safe POST reconciliation, and focused deterministic unit tests while preserving no-JavaScript access.
   - Cons: adds Vue/Vitest dependencies and a nontrivial state machine; likely exceeds the 400-line review budget and therefore requires work-unit planning under `ask-on-risk`.
   - Effort: High

3. **Client-owned API/SPA rewrite** — introduce dedicated endpoints and move rendering and state ownership entirely to Vue.
   - Pros: clean frontend separation and maximal client flexibility.
   - Cons: unnecessary routing/API expansion, weaker progressive fallback, broader authentication/CSRF surface, and excessive scope for this feature.
   - Effort: High

### Recommendation

Use Approach 2, implemented freshly rather than replayed from either stash. Define the complete server snapshot and state/race matrix before implementation, then plan autonomous server, navigation, and actions/recovery work units. Preserve server authority, real URLs, HTML fallback, whole-raffle counts, transactional mutation locking, and exactly-once POST semantics. Validate all external payloads before state commit and make unresolved/expired states explicit terminal or retryable modes. Because the historical implementation and tests alone are well beyond 400 changed lines, the proposal/tasks phases should forecast a high review-budget risk and ask for a chained delivery decision before apply.

### Risks

- The combined server, Vue, tests, dependency lockfile, and SDD artifacts will likely exceed the 400-line review budget.
- A loosely modeled `busy` flag can reintroduce page-request, mutation, reconciliation, and deferred-history races; explicit operation states and transition tests are required.
- Treating all mutation failures alike can repeat a possibly successful POST or commit untrusted data.
- Missing 401/419 handling in any fetch path can leave stale controls active after session expiry.
- Incomplete nested route constraints or payload validation can expose cross-raffle ambiguity or malformed action rendering.
- Pagination counts and rows are separate database reads; snapshots are authoritative at response time but not a serializable database-wide view unless the design deliberately adds stronger locking, which is probably unjustified.
- Vue/Vitest dependency versions and lockfile changes must be selected and verified against the current Vite/Laravel stack, not inherited from historical stash state.
- Reusing old OpenSpec/GitHub/Gentle identities would contaminate the fresh lifecycle; all such references remain historical only.

### Ready for Proposal

No. Exploration is complete, but the strict project gate requires a new GitHub issue with the required labels, including `status:approved`, before proposal. Do not reuse issues #56/#59 or PRs #57/#58, and do not start proposal until that gate is satisfied.
