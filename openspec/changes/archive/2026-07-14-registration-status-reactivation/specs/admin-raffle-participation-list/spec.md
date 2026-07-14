# Delta for Admin Raffle Participation List

## MODIFIED Requirements

### Requirement: Protected per-raffle registration visibility

The system MUST expose an `admin-host` `GET /raffles/{raffle}/registrations` page only to authenticated admins, listing stored registrations for that raffle newest-first with `name`, normalized `email`, `created_at`, linked-user signal, and status. Active rows MAY expose only flag/cancel actions. Flagged rows MUST expose only a bounded restore-to-active action that clears review. Cancelled rows MUST NOT expose mutation. The page MUST NOT imply ticket, payment, draw, export, notification, generic restore, or workflow semantics.
(Previously: flagged and cancelled rows could not expose further mutation.)

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

### Requirement: Explicit empty and sparse registration states

The system MUST show an explicit empty state when a raffle has no stored registrations. The system MUST also render registrations that lack an existing linked-user signal without inventing account linkage. Active rows MAY show only flag/cancel actions, flagged rows MUST show only restore-to-active / clear-review, and cancelled rows MUST show no mutation.
(Previously: only eligible active registration status actions could be shown on sparse registration rows.)

#### Scenario: Raffle has no registrations

- GIVEN a persisted raffle has no stored registrations
- WHEN an authenticated admin opens its registrations page
- THEN the system shows an explicit empty state instead of an empty table

#### Scenario: Registration has no linked-user signal

- GIVEN a raffle registration exists without a linked user reference
- WHEN an authenticated admin opens the registrations page
- THEN the system still shows the registration row
- AND the page does not invent account linkage
