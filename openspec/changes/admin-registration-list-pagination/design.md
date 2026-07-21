# Design: Paginate the Admin Registration List

## Technical Approach

Keep issue [#56](https://github.com/ferbal2000/testing-raffles/issues/56) inside the admin-host `web` + `auth:admin` boundary. `RaffleController` builds one snapshot through a serializer for read-only Blade, initial Vue data, and negotiated JSON GET/mutations. No route, schema, public surface, or realtime transport is added.

## Architecture Decisions

| Decision | Choice | Alternative / rationale |
|---|---|---|
| Server authority | `RaffleRegistrationSnapshot` maps paginator, whole-raffle counts, labels, and action URLs. | Duplication risks drift; a separate API duplicates authentication/scoping. |
| Pagination | Count first, canonicalize the requested page, then `orderByDesc('id')->paginate(25, ..., $canonicalPage)`. | Laravel normalizes invalid values to 1 but does not clamp high pages; cursor pagination conflicts with numbered URLs. |
| Progressive boundary | Blade renders the full read-only screen inside one mount element; Vue replaces it only after parsing adjacent initial JSON. | Hydration/SPA adds complexity; forms would create a forbidden operational fallback. |
| Client consistency | Commit complete snapshots; serialize operations, sequence/abort superseded GETs, and never abort/repeat POSTs. | Optimistic patches cannot reliably update actions, counts, or uncertain outcomes. |
| Delivery | Exactly three Feature Branch Chain units. | A single PR exceeds 500 lines; size exceptions and stacked-to-main are prohibited. |

## Data Flow

`GET/POST route → auth/CSRF → controller/locked transition → snapshot serializer → Blade or JSON → Vue state → History API/live regions`

## Interfaces and Failure Contract

```json
{
  "snapshot": {
    "raffle": {"id": 7},
    "rows": [{"id": 91, "name": "…", "email": "…", "status": {"value": "active", "label": "Activa"}, "created_at": "2026-07-21 12:30", "linked_account": {"value": true, "label": "Cuenta vinculada"}, "actions": [{"kind": "flag", "label": "…", "confirm": "…", "url": "…?page=2"}]}],
    "counts": {"active": 30, "flagged": 2, "cancelled": 1, "total": 33},
    "pagination": {"current_page": 2, "last_page": 2, "per_page": 25, "from": 26, "to": 33, "canonical_url": "…?page=2", "links": [{"page": 1, "url": "…?page=1", "current": false}]},
    "copy": {"busy": "…", "login_url": "…"}
  },
  "feedback": null
}
```

JSON GET and successful POST return `200` plus a full snapshot; rejected/stale transitions return `409` plus a fresh snapshot and `{level:"error",code,message}` feedback. HTML noncanonical GET redirects; JSON canonicalizes in-place. Missing/nested-scope resources return `404`. Middleware `401`, CSRF `419`, and `5xx` bodies are ignored; status is authoritative. Preserve the last snapshot; `401/419` blocks with a login link. A mutation transport/malformed/`5xx` failure triggers exactly one safe GET, never another POST. Successful reconciliation replaces state; failure remains blocked with GET retry/login. Pagination failure preserves data and permits retry.

Blade emits `<meta name="csrf-token">` and XSS-safe `<script type="application/json">{!! Js::encode($snapshot) !!}</script>` outside the mount node. Fetch uses same-origin credentials, `Accept: application/json`, and mutation `X-CSRF-TOKEN`.

Pagination fetches before `pushState`; canonical corrections use `replaceState`; `popstate` fetches without adding history. A monotonically increasing GET token prevents late commits. Browser history reached during a POST is deferred until the POST/reconciliation settles.

One global lock and `aria-busy` disable in-screen controls while preserving data and showing a busy message. Native confirmations precede POST; cancellation says terminal/non-restorable. Navigation focuses the results heading; mutation focuses updated status or feedback. Durable polite announcements survive temporary success toasts; errors use `role="alert"`.

## File Changes

Create `app/Http/Resources/Admin/RaffleRegistrationSnapshot.php`, `resources/js/admin/raffle-registrations/RaffleRegistrations.vue`, and `resources/js/admin/raffle-registrations/RaffleRegistrations.test.js`. Modify `RaffleController.php`, the registrations Blade view, shared layout, `lang/es/admin-raffles.php`, `resources/js/app.js`, `vite.config.js`, `package.json`, `package-lock.json`, and `AdminRaffleRegistrationsTest.php`. No route file changes.

## Testing Strategy

Strict RED-GREEN: Pest covers 25/26 boundaries, ordering/non-overlap, whole counts, canonical HTML/JSON, read-only/XSS-safe Blade, statuses, and scoped locking (`bin/test tests/Feature/Raffles/AdminRaffleRegistrationsTest.php`; full `bin/test`). Add Vue 3, `@vitejs/plugin-vue`, Vitest, Vue Test Utils, and jsdom; `npm run test:js` covers history, sequencing, locks, confirmations, POST-once reconciliation, expiry, toast/timers, focus, and live regions. Browser E2E remains unavailable; record manual keyboard/screen-reader checks.

## Threat Matrix

| Boundary | Applicability / response / RED test |
|---|---|
| Authenticated HTTP routes | Applicable: JSON negotiation, guest, CSRF, nested-scope, stale and server failures retain safe state; feature tests above. |
| Documentation-like paths | N/A: no path classification/execution. |
| Git repository selection | N/A: no Git execution. |
| Commit state | N/A: no VCS automation. |
| Push state | N/A: no push automation. |
| PR commands | N/A: no PR command composition. |

## Delivery, Rollout, and Rollback

1. `feat/admin-registration-list-pagination-server` → tracker: snapshot/JSON, canonical Blade boundary, translations, Pest.
2. `feat/admin-registration-list-pagination-navigation` → unit 1: Vue/Vitest foundation, pagination/history/busy state, component tests.
3. `feat/admin-registration-list-pagination-actions` → unit 2: mutations, recovery/expiry/toasts/accessibility, tests.

No child targets `main`; only the integrated tracker does. Each unit keeps tests/docs with behavior and stays within 500 reviewed lines. No migration or data backfill. Roll out through normal assets/application deployment; rollback all three units together, including frontend dependencies, without database action.

## Documentation Evidence and Open Questions

Context7 verified Laravel 13 pagination/negotiation, Vue mounting/cleanup, and Vitest 4.1.6 Vite/jsdom/mocking. Returned excerpts omitted `@vitejs/plugin-vue`, Vue Test Utils, and History API semantics; validate locked versions in unit 2. Open questions: none.
