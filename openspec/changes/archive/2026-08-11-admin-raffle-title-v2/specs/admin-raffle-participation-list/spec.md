# Delta for Admin Raffle Participation List

## MODIFIED Requirements

### Requirement: Protected per-raffle registration visibility

The system MUST expose an `admin-host` `GET /raffles/{raffle}/registrations` page only to authenticated admins, listing the canonical page of stored registrations for that raffle newest-first with `name`, normalized `email`, `created_at`, linked-user signal, and status. The page MUST show the current raffle's persisted title as its primary visible identity and numeric ID as a separate, visible secondary reference. The title MUST render as text so authored markup is escaped rather than interpreted, and title plus ID MUST remain visible for a backfilled `Sorteo #<ID>` title. Active rows MAY expose only flag/cancel actions. Flagged rows MUST expose only a bounded restore-to-active action that clears review. Cancelled rows MUST NOT expose mutation. Identity presentation MUST NOT change registration ordering, pagination, counts, actions, or the existing registration JSON contract. The page MUST NOT imply ticket, payment, draw, export, notification, generic restore, or workflow semantics.

(Previously: the protected registration page did not require the persisted title as primary identity or the numeric ID as a separate visible reference.)

#### Scenario: Authenticated admin opens a raffle registration list

- GIVEN an authenticated admin session and a raffle with stored registrations
- WHEN the admin requests `GET /raffles/{raffle}/registrations`
- THEN the page shows the raffle title as primary identity, its numeric ID separately, and allowed rows newest-first
- AND existing status actions, pagination, and counts remain unchanged

#### Scenario: Guest requests a raffle registration list

- GIVEN the request targets the admin host registrations page
- WHEN the requester is not authenticated as an admin
- THEN the system follows the existing admin authentication behavior

#### Scenario: Current raffle title is escaped

- GIVEN the current raffle title contains HTML or script-like text
- WHEN an authenticated admin opens its registrations page
- THEN the title is visibly rendered as text and no authored markup is interpreted

#### Scenario: Backfilled identity keeps both values visible

- GIVEN raffle ID `42` has the persisted title `Sorteo #42`
- WHEN an authenticated admin opens its registrations page
- THEN `Sorteo #42` and a separate numeric reference `42` are both visible

#### Scenario: Registration JSON remains unchanged

- GIVEN an existing registration snapshot or mutation response is requested
- WHEN the title-plus-ID heading is available on the HTML page
- THEN the registration JSON retains its existing fields and semantics
- AND no JSON field is added or removed solely for the heading identity
