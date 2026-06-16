# Raffle Catalog Specification

## Purpose

Define the first raffle lifecycle inside one database while preserving the admin/public identity boundary.

## Invariants

- Admin credentials MUST be required for raffle creation and state changes.
- Public user credentials MUST NOT be accepted for admin raffle commands.
- Lifecycle order MUST remain `draft -> published -> closed -> drawn`.

## Out of Scope

Prize inventory, scheduling workers, and multi-tenant administration.

## Requirements

### Requirement: Raffle lifecycle management

The system MUST store a raffle as a draft, publish it with an entry window, close it, and mark it drawn after winner selection.

#### Scenario: Admin creates and publishes a raffle

- GIVEN an authenticated admin on the admin surface
- WHEN the admin creates a raffle and publishes it with a valid entry window
- THEN the raffle is stored in the same database with status `published`

#### Scenario: Invalid publication is rejected

- GIVEN a draft raffle with an invalid entry window or missing required fields
- WHEN publication is requested
- THEN the raffle remains `draft` and the command is rejected

### Requirement: State transition guardrails

The system SHALL allow only the next valid lifecycle transition and SHALL NOT skip or reverse states.

#### Scenario: Public actor attempts admin transition

- GIVEN a published raffle and a public-site credential
- WHEN that actor requests a close transition
- THEN the command is rejected because the actor is not an admin
