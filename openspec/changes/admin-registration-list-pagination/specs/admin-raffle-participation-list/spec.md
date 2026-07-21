# Delta for Admin Raffle Participation List

## ADDED Requirements

### Requirement: Paginated authoritative registration view

The system MUST expose canonical `?page=N` pages of at most 25 unique newest-first registrations with whole-raffle active, flagged, cancelled, and total counts.

#### Scenario: Populated page
- GIVEN a raffle has over 25 registrations
- WHEN an admin opens page 2
- THEN the next non-overlapping newest-first rows and whole-raffle counts appear

#### Scenario: Noncanonical page
- GIVEN a nonempty raffle
- WHEN `page` is malformed, non-positive, or out of range
- THEN it canonicalizes to page 1 or the last page respectively
- AND out-of-range MUST NOT appear empty

#### Scenario: Raffle is truly empty
- GIVEN a raffle has no registrations
- WHEN its list is requested
- THEN an empty state and four zero counts appear

### Requirement: URL-addressable reactive interaction

The screen MUST expose real `?page=N` links and update pagination, back/forward, and confirmed `flag`, `cancel`, and `restore` actions without full reload. Rows, actions, and counts MUST remain server-authoritative.

#### Scenario: Page history
- GIVEN interaction is available
- WHEN pagination or back/forward navigation occurs
- THEN results and canonical URL update without full reload
- AND focus and a polite live region identify the results

#### Scenario: Interaction unavailable
- GIVEN interaction cannot run
- WHEN the page appears
- THEN its table and counts remain read-only
- AND mutation controls or mutating reload fallback MUST NOT exist

### Requirement: Safe status transitions and recovery

Every status action MUST require native confirmation; cancellation MUST warn it is terminal and non-restorable. The system MUST NOT optimistically update or auto-repeat uncertain mutations.

#### Scenario: Mutation succeeds or is rejected
- GIVEN an admin confirms an action
- WHEN it succeeds or is rejected as stale or invalid
- THEN authoritative rows, actions, and counts refresh without full reload
- AND success has a temporary toast and announcement; rejection reports error

#### Scenario: Uncertain outcome
- GIVEN a mutation response is lost, timed out, or malformed
- WHEN its outcome is uncertain
- THEN it MUST NOT repeat and MUST reconcile read-only, communicating authoritative outcome
- AND reconciliation failure preserves data, declares unresolved outcome, and blocks mutations

#### Scenario: Session expires
- GIVEN visible data and an expired session
- WHEN an operation detects expiry
- THEN data remains, operations block, and expiry is announced
- AND an admin login path appears without immediate redirect

### Requirement: Accessible operation state

The screen MUST provide labelled pagination, current-page semantics, visible focus, result context, focus restoration, polite live regions, and alert errors. Pending operations MUST block the whole screen with accessible busy state while data remains perceivable.

#### Scenario: Operation is pending
- GIVEN an operation has started
- WHEN it remains pending
- THEN pagination, row actions, and screen navigation are unavailable
- AND busy semantics and a message appear without hiding data
