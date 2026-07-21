## Exploration: Admin Registration List Pagination

**Approved issue:** [#56 — feat(admin): paginate raffle registrations without page reload](https://github.com/ferbal2000/testing-raffles/issues/56)

### Current State

The previous server-rendered recommendation is superseded. This slice MUST use a progressive Vue island for both pagination and `flag`, `cancel`, and `restore` actions so normal interaction updates the page without a full reload. Blade remains the page shell and a read-only failure boundary; this is not a Vue SPA and does not introduce a standalone API application.

`Admin\RaffleController::registrations()` currently eager-loads every registration newest-first and loads whole-raffle active, flagged, cancelled, and total counts. The Blade view owns the complete table, summary, confirmation forms, and feedback. Status endpoints accept session-authenticated form POSTs, lock the scoped registration, and redirect after success or a rejected transition.

The selected architecture is Laravel 13.15, PostgreSQL, Pest/PHPUnit, Blade-first rendering, Vite, and progressive Vue islands. Actual frontend implementation has not reached that architectural target:

- `package.json` and `package-lock.json` contain Vite and Tailwind but no `vue`, `@vitejs/plugin-vue`, Vitest, DOM environment, or Vue test utilities.
- `resources/js/app.js` is empty; there are no Vue components or mounting conventions in the repository.
- `vite.config.js` has one application entry and no Vue plugin.
- the shared layout loads the Vite entry conditionally but has no CSRF meta token.
- no JavaScript unit, component, or browser test capability exists. OpenSpec records Pest/PHPUnit integration tests and explicitly records E2E as unavailable.
- same-origin JSON requests already fit the admin boundary: admin routes use `web` and `auth:admin`, boundary middleware selects the admin session cookie, and exception rendering returns JSON when the request expects JSON.

The stable `admin-raffle-participation-list` capability requires newest-first rows, explicit empty state, bounded status actions, and whole-raffle counts, but does not define asynchronous interaction or pagination. Same-browser request/response reactivity is required here. Reverb/Echo, polling, SSE, WebSockets, and changes pushed from another browser session remain future realtime work.

### Affected Areas

- `package.json`, `package-lock.json`, and `vite.config.js` — add Vue 3, the Vue Vite plugin, and a minimal Vitest + Vue Test Utils + DOM test capability; generated lockfile churn is expected.
- `resources/js/app.js` and new feature-scoped files under `resources/js/admin/raffle-registrations/` — mount one island over the complete registration screen, own paginated state and async actions, coordinate browser history, and expose testable request/state boundaries.
- `resources/views/components/layouts/app.blade.php` — expose the same-origin CSRF token used by authenticated JSON mutations.
- `app/Http/Controllers/Admin/RaffleController.php` and a focused serializer/resource — paginate the relationship, produce one stable snapshot contract, and return HTML or JSON according to the request without duplicating domain transitions.
- `resources/views/admin/raffles/registrations.blade.php` — retain the Blade heading, table, and whole-raffle counts as a read-only failure boundary; embed an escaped initial snapshot; expose no mutating forms; and provide the island mount boundary.
- `lang/es/admin-raffles.php` — provide Spanish pagination, global busy, confirmation, toast, reconciliation, session-expiry, and read-only failure copy consumed by the server contract or initial props.
- `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` — prove the authenticated HTML/JSON contracts, pagination, whole-raffle counts, transition outcomes, canonical pages, and read-only Blade failure behavior with no mutation controls.
- new JavaScript component tests — prove pagination interception, push/replace/pop history, screen-wide operation locks, native confirmations, async transitions, temporary accessible toasts, safe GET reconciliation, session-expiry behavior, focus/live regions, and progressive failures.
- `openspec/specs/admin-raffle-participation-list/spec.md` — capability to modify through the future delta; `realtime-update-candidate-map` should clarify that cross-browser push remains unimplemented rather than treating this island as server-push realtime.

### Approaches

1. **Content-negotiated existing routes with one snapshot contract** — keep the current registration GET and action POST URLs. HTML requests return the Blade shell/read-only representation; requests with `Accept: application/json` return a normalized paginated snapshot or transition result. Blade embeds the first snapshot so Vue does not perform a duplicate initial fetch.
   - Pros: narrowest HTTP surface; preserves current auth, nested raffle scoping, CSRF enforcement, and route names; one backend transformer serves initial Blade props and later JSON refreshes; no frontend route reconstruction; remains a modular-monolith island rather than a separate API.
   - Cons: controller return types and tests become content-aware; the snapshot serializer must prevent Blade and JSON representations from drifting; read-only Blade and interactive Vue markup create some presentation duplication.
   - Effort: High

2. **Dedicated JSON subroutes for the island** — retain the Blade GET and add separate same-host data/action endpoints under the authenticated admin route group.
   - Pros: explicit transport boundary and simpler response types; easier to evolve independently from fallback HTML.
   - Cons: duplicates route surface and authorization/scoping tests; still needs an initial-data decision; risks growing a feature-local API where content negotiation is sufficient.
   - Effort: High

3. **SPA/router or server-push framework expansion** — introduce Vue Router/full SPA behavior, Inertia/Livewire, or Reverb/Echo while adding pagination.
   - Pros: broader client navigation or cross-browser update foundations.
   - Cons: violates the selected Blade-first progressive-island architecture or the explicit slice boundary; adds unrelated infrastructure, migration, and testing cost.
   - Effort: Very High

### Recommendation

Choose **one content-negotiated server snapshot plus one page-scoped Vue island**. Keep the existing GET and POST routes behind `web` and `auth:admin`. Extract snapshot construction so the initial Blade response, paginated/reconciliation JSON GET, successful mutation JSON response, rejected mutation JSON response, and read-only Blade representation all derive row state, available actions, labels, counts, and pagination metadata from the same server authority.

The snapshot should contain only this feature's data:

- registration rows with allowed fields, status, and server-generated available action descriptors/URLs;
- whole-raffle active, flagged, cancelled, and total counts;
- `current_page`, `last_page`, `per_page`, `from`, `to`, and canonical URL/link metadata;
- Spanish labels/messages needed by the island, or stable message keys resolved server-side.

Retain **25 rows per page**. Vue does not change the six-column moderation density, and 25 remains a practical fixed balance between navigation frequency, DOM/action count, and response size. Continue ordering by unique descending `id`. Search, filters, configurable size, cursor pagination, index tuning, and load/concurrency redesign remain out of scope.

#### Initial render and progressive boundary

Blade should render the page heading/back link, the requested page's table rows, and whole-raffle counts in read-only form. It MUST NOT render `flag`, `cancel`, or `restore` forms or a server-rendered pagination control. It should embed the identical initial snapshot in an escaped `application/json` script element beside a feature-specific mount boundary that encompasses the complete registration screen. `resources/js/app.js` should detect that boundary and mount only the registration island with parsed root props.

The Blade boundary should include a default read-only/unavailable notice that Vue removes only after a successful mount, with a `noscript` equivalent for disabled JavaScript. If assets fail or Vue cannot mount, the existing table and counts remain visible, all operational controls remain absent, and the notice clearly states that pagination and status actions are unavailable. There is no mutating Blade fallback and no normal full-page-reload operational path.

#### URL-addressable async pagination

Vue pagination controls should expose real `?page=N` URLs and prevent normal same-document full-page navigation. Fetch the JSON representation first, then render the snapshot and call `history.pushState`; use `replaceState` when the server canonicalizes malformed or out-of-range input. A `popstate` listener must fetch the URL's page and update the island without reloading or adding another history entry.

While any pagination or mutation operation is pending, block the entire registration screen, not only the initiating control. Preserve the visible snapshot, set a screen-level `aria-busy` state, and disable every interactive control in the screen, including pagination, row actions, and back navigation. Expose one accessible busy message. This global lock removes competing operations; request ordering or cancellation remains defensive protection against late responses.

Malformed, zero, or negative pages canonicalize to page 1. A truly empty raffle renders the existing empty state with zero whole-raffle counts. A positive out-of-range page with existing registrations canonicalizes to the last page and replaces the browser URL; it MUST NOT display the raffle-empty message. A direct HTML request may use a normal canonical redirect before the island mounts.

#### Async status actions

Vue should use native browser confirmation dialogs before every status mutation. Flag and restore confirmations remain concise; cancellation copy must explicitly warn that cancellation is terminal and cannot be restored. After confirmation, POST to the existing action URL with `Accept: application/json`, `X-CSRF-TOKEN`, and the current page. Do not optimistically mutate. The global operation lock applies from confirmation acceptance until the response or reconciliation completes. The existing database lock and transition rules remain authoritative.

After commit, return HTTP 200 with a fresh canonical snapshot and success feedback. Returning the full current-page snapshot, rather than patching one client row, guarantees that the affected row, available actions, global counts, and page contents agree with persisted state. Show successful feedback as a temporary toast and announce the same message through a persistent polite live region before the visual toast expires. Return HTTP 409 for a stale/rejected transition with a freshly queried snapshot and scoped error feedback. Duplicate clicks are blocked by the global client lock; duplicates arriving from another tab serialize under the existing database lock and the later request is rejected by transition rules rather than silently succeeding twice. Nested-scope misses remain 404.

If a mutation response is lost, times out, is malformed, or otherwise leaves the outcome uncertain, NEVER repeat the mutation automatically. Keep the interactive screen globally blocked and perform a safe GET of the current canonical snapshot. If reconciliation succeeds, replace local state and communicate whether the authoritative status confirms the requested result or shows a different persisted result. If reconciliation also fails, preserve the last confirmed visible data, declare the outcome unresolved, keep mutating actions blocked, and offer only safe GET reconciliation retry or the explicit login path when authentication expired.

The browser sends the boundary-selected HttpOnly admin session cookie automatically on same-origin fetches. The layout CSRF meta token supplies `X-CSRF-TOKEN`; JSON headers ensure unauthenticated/expired sessions produce 401 rather than login HTML. Treat 401 and 419 as session-expired states: preserve visible data, block pagination and mutations, announce the expiry, and provide an explicit admin login link outside the blocked controls. Do not immediately redirect and do not recommend repeating the mutation or using a mutating form fallback.

#### Accessibility and focus

The island should expose a labelled pagination `nav`, `aria-current="page"`, visible focus styles, and a visible result range. Use a polite live region for loaded-page, busy, reconciliation, and toast announcements; use an alert region for errors and unresolved outcomes; and expose `aria-busy` on the interactive region. The screen-wide busy layer must block controls without hiding the preserved table/counts from assistive technology. After explicit page navigation, move focus to a stable list heading or focusable results region. After a successful action whose button disappears, move focus to the updated row status or toast/feedback region. Rejected actions retain fresh server state and announce the error without leaving focus on a removed control. The read-only Blade boundary remains perceivable and contains no misleading interactive elements.

#### Testing and review workload

Pest feature tests should cover the shared snapshot shape, 25/26-row boundaries, newest-first non-overlap, whole-raffle counts on every page, read-only HTML with no mutation forms, authenticated JSON behavior, 200 success snapshots, 409 rejected/stale snapshots, 401/404 boundaries, and canonical empty/out-of-range pages. A new Vitest + Vue Test Utils + DOM environment is required under strict TDD because Pest cannot prove client history, global operation locks, confirmation wording, temporary toast announcements, uncertain-response GET reconciliation, session-expiry controls, focus, or live-region behavior. No browser E2E capability currently exists; that remains a verification limitation rather than a reason to leave the island untested.

The finalized scope is approximately **950–1,400 authored changed lines**, excluding generated `package-lock.json` churn and SDD archive movement. Risk against the configured 500-line budget is **High**. The user approved three PRs using a **Feature Branch Chain**; no size exception and no stacked-to-main delivery are allowed:

1. shared paginated snapshot/JSON contract, read-only Blade shell/failure boundary, and Pest coverage (**300–450 lines**);
2. Vue/Vitest foundation, URL-addressable async pagination, browser history, global busy state, and component tests (**300–450 lines**);
3. native-confirmed async status actions, dynamic rows/counts, temporary accessible toasts, uncertain-response reconciliation, session/failure/accessibility behavior, and tests (**350–500 lines**).

Each work unit keeps its tests and directly related documentation with the behavior it verifies and must remain independently reviewable within the budget. Child PR 1 targets the tracker/integration branch; later child PRs target their immediate predecessor according to the feature-branch chain. No child PR targets `main`. Only the fully integrated tracker branch may ultimately target `main`, and the slice is incomplete until all three units are integrated.

Reverb/Echo, polling, cross-browser propagation, search, filters, export, bulk actions, configurable page size, public pagination, and schema/index tuning are explicit non-goals.

### Risks

- The repository has no Vue or JavaScript test foundation; introducing both correctly is material scope, not incidental wiring.
- Maintaining a read-only Blade representation and a Vue representation can drift unless both consume one server snapshot and contract tests.
- Request races can overwrite newer navigation or feedback unless pagination cancellation/request ordering and mutation locks are explicit.
- Session expiry, CSRF failure, and lost mutation responses can otherwise produce HTML parsing errors, duplicate submissions, or false client state; uncertain mutations must remain blocked until safe GET reconciliation succeeds or the unresolved state is clearly surfaced.
- A default read-only notice that Vue removes after successful mount avoids hidden failure, but its visibility transition must not cause duplicate announcements or content flash.
- Offset pagination can shift rows when other sessions insert registrations; this slice refreshes on its own requests but does not provide snapshots or cross-browser push.
- Deep offsets and whole-raffle counts may still become costly without purpose-built indexes; profiling/index work remains separate.
- Component tests cover client behavior but cannot fully replace browser-level verification of focus and assistive-technology behavior; no E2E capability currently exists.
- The integrated slice exceeds 500 authored lines, so clean child-PR diffs and strict feature-branch-chain discipline are mandatory; neither a size exception nor stacked-to-main delivery is approved.

### Ready for Proposal

Yes. The exploration is finalized and proposal-ready under approved issue [#56](https://github.com/ferbal2000/testing-raffles/issues/56): one Blade-hosted Vue island, a content-negotiated Laravel snapshot contract, fixed 25-row URL-addressable pagination, globally blocked async operations, native confirmations, authoritative snapshot refresh and safe GET reconciliation, temporary accessible success toasts, explicit session-expiry login, read-only Blade failure behavior, and no server-push realtime. Delivery is locked to the approved three-PR feature-branch chain; no size exception or stacked-to-main alternative remains open. The next SDD phase is proposal, but this exploration phase MUST NOT create it.
