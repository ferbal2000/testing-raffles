# Delta for Admin Raffle Create

## MODIFIED Requirements

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
