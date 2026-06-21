# Admin Raffle Edit Specification

## Purpose

Allow authenticated admins to edit persisted raffle availability fields without adding lifecycle restrictions or broader CRUD behavior.

## Requirements

### Requirement: Protected admin raffle edit form access

The system MUST expose `GET /raffles/{raffle}/edit` on the admin host for authenticated admins. Guests MUST follow existing admin auth behavior.

#### Scenario: Authenticated admin opens the edit form

- GIVEN an authenticated admin and an existing raffle
- WHEN the admin requests `GET /raffles/{raffle}/edit`
- THEN the system returns an edit form for that raffle
- AND the form includes only `starts_at` and `ends_at` availability fields

#### Scenario: Guest requests the edit form

- GIVEN the request targets an admin raffle edit route
- WHEN the requester is not authenticated as an admin
- THEN the system redirects using the existing admin login behavior

### Requirement: Edit form accepts nullable availability inputs

The system MUST accept optional nullable `starts_at` and `ends_at` `datetime-local` inputs using `Y-m-d\TH:i`. Empty values MUST persist as `null`. Invalid values MUST return to edit with errors and old input.

#### Scenario: Admin submits blank availability values

- GIVEN an authenticated admin edits a raffle
- WHEN the admin submits empty `starts_at` and `ends_at` values
- THEN the system persists both availability fields as `null`

#### Scenario: Admin submits an invalid availability value

- GIVEN an authenticated admin edits a raffle
- WHEN the admin submits a value that does not match `Y-m-d\TH:i` for `starts_at` or `ends_at`
- THEN the system returns to the edit form with validation errors and old input

### Requirement: Successful update persists availability only

The system MUST expose `PATCH /raffles/{raffle}` for authenticated admins. Success MUST persist only submitted `starts_at` and `ends_at`, redirect to `admin.raffles.index`, and be allowed for `draft`, `published`, and `closed` raffles. Future status-based immutability MUST be introduced by a separate spec change. This slice MUST NOT add date ordering, status transitions, lifecycle actions, audit, roles, participants, draws, winners, or schema changes.

#### Scenario: Admin updates availability for any current status

- GIVEN an existing raffle with status `draft`, `published`, or `closed`
- WHEN an authenticated admin submits valid nullable availability values
- THEN the system persists only `starts_at` and `ends_at`
- AND redirects to `admin.raffles.index`
