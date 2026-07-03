# Raffle Registration Status Specification

## Purpose

Define the persisted status foundation for raffle registrations so normal public participation remains valid by default while future admin exception handling can distinguish flagged or cancelled registrations.

## Requirements

### Requirement: Persisted registration status vocabulary

The system MUST persist exactly one status for each raffle registration. The allowed status vocabulary SHALL be `active`, `flagged`, and `cancelled`. The status MUST describe only registration exception state and MUST NOT imply approval, rejection, payment, credit, ticket, draw, or eligibility workflow semantics.

#### Scenario: Registration stores an allowed status

- GIVEN a raffle registration is persisted with status `flagged`
- WHEN the registration is read from storage
- THEN the stored status is `flagged`
- AND it remains within the allowed vocabulary

#### Scenario: Unsupported status is rejected

- GIVEN the allowed vocabulary is `active`, `flagged`, and `cancelled`
- WHEN a registration is persisted with any other status
- THEN the system rejects the unsupported status
- AND no invalid registration status is stored

### Requirement: Registrations default to active

The system MUST default newly created raffle registrations to `active` when no explicit status is provided. Existing MVP registrations MUST be treated as `active` by default after this foundation is introduced.

#### Scenario: Public registration uses default active status

- GIVEN a public visitor submits a valid participation entry
- WHEN the registration is stored without an explicit status
- THEN the registration status is `active`
- AND the participation remains valid by default

#### Scenario: Existing registrations receive active status

- GIVEN registrations persisted before this status foundation exist
- WHEN the status foundation is available
- THEN those registrations have effective status `active`
- AND no admin action is required to keep them valid

### Requirement: Status foundation has no operational side effects

The system MUST NOT introduce admin status actions, public badges, filters, audit trail, automated analysis, approval/rejection language, ads, credits, tickets, draw logic, payments, or participation eligibility changes as part of this status foundation.

#### Scenario: Status does not change public entry eligibility

- GIVEN a raffle can accept participants under the existing participation rules
- WHEN a public visitor submits a valid registration
- THEN the registration flow follows the existing eligibility behavior
- AND the new status foundation does not add extra public approval steps

#### Scenario: Exception statuses remain future-facing

- GIVEN `flagged` and `cancelled` are allowed status values
- WHEN this slice is delivered
- THEN no admin UI or action is introduced to manage them
- AND the values remain available for future exception-handling slices
