# Delta for Admin Raffle Edit

## MODIFIED Requirements

### Requirement: Protected admin raffle edit form access

The system MUST expose `GET /raffles/{raffle}/edit` on the admin host for authenticated admins. The edit form MUST display the raffle's numeric ID and MUST provide its persisted title as an escaped, editable value alongside the `starts_at` and `ends_at` availability fields. Guests MUST follow existing admin authentication behavior.

(Previously: the edit form included only `starts_at` and `ends_at` availability fields.)

#### Scenario: Authenticated admin opens the edit form

- GIVEN an authenticated admin and an existing raffle
- WHEN the admin requests `GET /raffles/{raffle}/edit`
- THEN the system returns an edit form showing the raffle's numeric ID
- AND the persisted title is escaped and editable alongside `starts_at` and `ends_at`

#### Scenario: Guest requests the edit form

- GIVEN the request targets an admin raffle edit route
- WHEN the requester is not authenticated as an admin
- THEN the system redirects using the existing admin login behavior

### Requirement: Successful update persists title and availability

The system MUST expose `PATCH /raffles/{raffle}` for authenticated admins. It MUST allow updates to only the title, `starts_at`, and `ends_at` for raffles in `draft`, `published`, or `closed`. It MUST remove surrounding title whitespace before validation and persistence, reject a missing or normalized blank title, reject a title longer than 100 characters, and allow duplicate titles. Invalid input MUST return to the edit form with errors and old input without changing any raffle field. Success MUST redirect to `admin.raffles.index`. Future status-based immutability MUST be introduced by a separate spec change. This slice MUST NOT add date ordering, status transitions, lifecycle actions, audit, roles, participants, draws, winners, or schema changes.

(Previously: successful update persisted only `starts_at` and `ends_at`.)

#### Scenario: Admin updates permitted fields for any current status

- GIVEN an existing raffle with status `draft`, `published`, or `closed`
- WHEN an authenticated admin submits a valid title and nullable availability values
- THEN the system persists only the normalized title, `starts_at`, and `ends_at`
- AND redirects to `admin.raffles.index`

#### Scenario: Edited title is normalized

- GIVEN an authenticated admin submits an edited title with surrounding whitespace
- WHEN the update request is processed
- THEN the persisted title contains no surrounding whitespace

#### Scenario: Invalid update is rejected atomically

- GIVEN an authenticated admin submits a missing, whitespace-only, non-string, or over-100-character title with changed availability values
- WHEN the update request is processed
- THEN no raffle field changes and the edit form returns with a title error
- AND any submitted title and availability values are retained as old input

#### Scenario: Duplicate edited title is accepted

- GIVEN another raffle already has the submitted normalized title
- WHEN an authenticated admin updates the current raffle with that title
- THEN the current raffle persists the same title and keeps its own numeric ID

## RENAMED Requirements

- FROM: `### Requirement: Successful update persists availability only`
- TO: `### Requirement: Successful update persists title and availability`

(Reason: The delivered update contract now includes the required raffle title.)
(Migration: References to the former requirement name MUST use the new requirement name.)
