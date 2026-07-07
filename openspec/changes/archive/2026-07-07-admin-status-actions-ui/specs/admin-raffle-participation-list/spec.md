# Delta for Admin Raffle Participation List

## MODIFIED Requirements

### Requirement: Protected per-raffle registration visibility

The system MUST expose an admin-host page at `GET /raffles/{raffle}/registrations` for authenticated admins only. The page MUST list stored registrations for that raffle newest-first and MUST show `name`, normalized `email`, `created_at`, linked-user signal, and status. Active rows MAY expose flag/cancel admin actions; flagged/cancelled rows MUST NOT expose further mutation. The page MUST NOT imply ticket, payment, draw, export, notification, restore, or generic workflow semantics.
(Previously: the page was status-blind and strictly read-only.)

#### Scenario: Authenticated admin opens a raffle registration list

- GIVEN an authenticated admin session on the admin host
- WHEN the admin requests `GET /raffles/{raffle}/registrations` for a raffle with stored registrations
- THEN each row shows the allowed stored fields and status newest-first
- AND active rows MAY show flag/cancel actions only

#### Scenario: Guest requests a raffle registration list

- GIVEN the request targets the admin host registrations page
- WHEN the requester is not authenticated as an admin
- THEN the system follows the existing admin authentication behavior

### Requirement: Explicit empty and sparse registration states

The system MUST show an explicit empty state when a raffle has no stored registrations. The system MUST also render registrations that lack an existing linked-user signal without inventing account linkage; only eligible active registration status actions MAY be shown.
(Previously: sparse rows could not include any admin actions.)

#### Scenario: Raffle has no registrations

- GIVEN a persisted raffle has no stored registrations
- WHEN an authenticated admin opens its registrations page
- THEN the system shows an explicit empty state instead of an empty table

#### Scenario: Registration has no linked-user signal

- GIVEN a raffle registration exists without a linked user reference
- WHEN an authenticated admin opens the registrations page
- THEN the system still shows the registration row
- AND the page does not invent account linkage

### Requirement: Read-only current raffle registration summary

The system MUST show summary counts for the current raffle on `GET /raffles/{raffle}/registrations`. Counts MUST reflect persisted registrations for that raffle only and MUST visually separate active from cancelled totals so annulled registrations are not collapsed into one ambiguous total.
(Previously: the page showed one read-only total for all persisted registrations.)

#### Scenario: Summary count appears with registrations

- GIVEN an authenticated admin opens a raffle registration list with active and cancelled registrations
- WHEN the page is rendered
- THEN active and cancelled totals are visually separated
- AND the registration list remains newest-first

#### Scenario: Summary count appears for empty list

- GIVEN an authenticated admin opens a raffle registration list with no stored registrations
- WHEN the page is rendered
- THEN the active and cancelled totals reflect zero persisted registrations
- AND the explicit empty state remains visible
