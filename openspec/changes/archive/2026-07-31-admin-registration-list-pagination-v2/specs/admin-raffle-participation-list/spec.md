# Delta for Admin Raffle Participation List

## ADDED Requirements

### Requirement: Canonical registration pagination

Pages MUST contain 25 descending registration IDs without overlap, except sparse last pages. Counts MUST cover the raffle. Missing, malformed, zero, or negative `page` MUST canonicalize to page 1; over-last to the last page; empty raffles to page 1.

#### Scenario: Canonical page matrix
- GIVEN empty raffles or explicit mixed-status IDs
- WHEN boundary/malformed/over-last pages are requested
- THEN identity, rows, ordering, non-overlap, and counts MUST match this requirement without false emptiness

### Requirement: Authoritative snapshots and full fallback

HTML, page GET, mutation success, and stale conflict MUST share an authoritative snapshot. Only valid rows, counts, pagination, and actions MAY replace confirmed data. Without JavaScript, pagination and flag/cancel/restore MUST work through links, forms, and reloads.

#### Scenario: Snapshot and fallback matrix
- GIVEN confirmed data with/without JavaScript
- WHEN valid or malformed snapshot/action data arrives, or fallback controls are used
- THEN valid state/navigation MUST commit atomically; invalid data MUST preserve state and expose recovery

### Requirement: Deterministic navigation and accessibility

Navigation MUST commit data before history: success MUST push once, canonicalization replace once, and `popstate` never write. Superseded GETs MUST be ignored. Mutation/reconciliation MUST block links and defer latest `popstate`, consumed once. Success MUST focus the heading and announce the page; failure MUST retain focus and announce error.

#### Scenario: Request and history race matrix
- GIVEN GETs, mutation, reconciliation, or repeated `popstate` overlap
- WHEN responses resolve in any order
- THEN only the latest valid outcome MUST commit with exact history, deferral, focus, and live-region results

### Requirement: Non-optimistic mutation and bounded reconciliation

Consistency/recovery MUST outrank perceived speed; mutation MUST NOT be optimistic. One POST MUST commit valid success, or valid expected 409 stale state with feedback. Transport loss, malformed response, or unexpected failure MUST trigger one reconciliation GET and MUST NOT repeat POST. Failed reconciliation MUST preserve data, block mutation/navigation, and allow only another GET.

#### Scenario: Mutation outcome matrix
- GIVEN an action yields success, stale conflict, uncertainty, failed reconciliation, or retry
- WHEN handled
- THEN counts MUST be one POST; zero GET for valid 200/409, otherwise one automatic GET plus one per retry, committing only authoritative state

### Requirement: Terminal expiry and nested boundaries

Any page GET, POST, reconciliation, or retry 401/419 MUST preserve data, clear deferral, block interaction, expose login, and stop recovery. IDs MUST be numeric; nonexistent/cross-raffle registrations and malformed actions MUST fail without mutation or misleading state.

#### Scenario: Expiry and identity matrix
- GIVEN any asynchronous source or action route receives 401/419 or invalid nested identity
- WHEN processed
- THEN expiry MUST be terminal without later request/history writes; invalid identity MUST be rejected without mutation or misleading snapshot

## MODIFIED Requirements

### Requirement: Protected per-raffle registration visibility

The system MUST expose an `admin-host` `GET /raffles/{raffle}/registrations` page only to authenticated admins, listing the canonical page of stored registrations for that raffle newest-first with `name`, normalized `email`, `created_at`, linked-user signal, and status. Active rows MAY expose only flag/cancel actions. Flagged rows MUST expose only a bounded restore-to-active action that clears review. Cancelled rows MUST NOT expose mutation. The page MUST NOT imply ticket, payment, draw, export, notification, generic restore, or workflow semantics.
(Previously: the protected page listed all stored registrations without canonical pagination.)

#### Scenario: Authenticated admin opens a raffle registration list
- GIVEN an authenticated admin session on the admin host
- WHEN the admin requests `GET /raffles/{raffle}/registrations` for a raffle with stored registrations
- THEN rows show allowed fields and status newest-first
- AND active rows MAY show flag/cancel actions only
- AND flagged rows MUST show only restore-to-active / clear-review action

#### Scenario: Guest requests a raffle registration list
- GIVEN the request targets the admin host registrations page
- WHEN the requester is not authenticated as an admin
- THEN the system follows the existing admin authentication behavior
