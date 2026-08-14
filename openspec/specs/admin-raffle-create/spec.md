# Admin Raffle Create Specification

## Purpose

Allow authenticated admins to create raffle drafts from the admin host with optional availability dates only.

## Requirements

### Requirement: Protected admin raffle create form access

The system MUST expose `GET /raffles/create` on the admin host for authenticated admins. Unauthenticated requests to that route MUST follow the existing admin authentication behavior.

#### Scenario: Authenticated admin opens the create form

- GIVEN an authenticated admin session on the admin host
- WHEN the admin requests `GET /raffles/create`
- THEN the system returns the admin raffle create form

#### Scenario: Guest requests the create form

- GIVEN the request targets the admin host create route
- WHEN the requester is not authenticated as an admin
- THEN the system redirects using the existing admin login behavior

### Requirement: Create form accepts nullable availability inputs

The system MUST accept `starts_at` and `ends_at` as optional and nullable `datetime-local` inputs using the `Y-m-d\TH:i` format. Empty values MUST be persisted as `null`. Invalid values MUST return to the create form with validation errors and old input.

#### Scenario: Admin submits blank availability values

- GIVEN an authenticated admin is on the create form
- WHEN the admin submits empty `starts_at` and `ends_at` values
- THEN the system treats both values as `null`

#### Scenario: Admin submits an invalid availability value

- GIVEN an authenticated admin is on the create form
- WHEN the admin submits a value that does not match the `Y-m-d\TH:i` datetime-local format for `starts_at` or `ends_at`
- THEN the system returns to the create form with validation errors and old input

### Requirement: Successful submit creates a draft raffle

The system MUST require a string title when an authenticated admin creates a raffle. It MUST remove surrounding whitespace before validation and persistence, reject a missing or normalized blank title, reject a title longer than 100 characters, and allow duplicate titles. The system MUST create the raffle through the existing domain behavior so the persisted raffle starts in `draft`. After a successful create, the system MUST redirect to `admin.raffles.index`. Validation failure MUST return to the create form with errors and old input.

(Previously: successful creation persisted a draft from valid availability values without defining title input.)

#### Scenario: Admin creates a raffle with valid values

- GIVEN an authenticated admin submits a valid title and nullable raffle availability data in `Y-m-d\TH:i` datetime-local format
- WHEN the create request is processed
- THEN the system persists a new raffle with the normalized title and status `draft`
- AND redirects to `admin.raffles.index`

#### Scenario: Surrounding title whitespace is normalized

- GIVEN an authenticated admin submits the title `  Sorteo de verano  `
- WHEN the create request is processed
- THEN the persisted title is `Sorteo de verano`

#### Scenario: Invalid title is rejected with old input

- GIVEN an authenticated admin submits a missing, whitespace-only, non-string, or over-100-character title
- WHEN the create request is processed
- THEN no raffle is created and the create form returns with a title error
- AND any submitted title is retained as normalized old input

#### Scenario: Duplicate title is accepted

- GIVEN a raffle titled `Sorteo de verano` already exists
- WHEN an authenticated admin creates another raffle with that normalized title
- THEN the second raffle is persisted in `draft` with a distinct numeric ID
