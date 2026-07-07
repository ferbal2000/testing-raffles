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

The system MUST preserve public registration behavior while allowing authenticated admins to mutate eligible active registrations to `flagged` or `cancelled`. `flagged` MUST mean retained but requiring review. `cancelled` MUST mean annulled/not valid while retained for traceability. In this slice, `flagged` and `cancelled` MUST be terminal: no restore, reactivate, or further mutation from those states. Feedback SHOULD clearly say marked for review, cancelled, or action no longer available. The system MUST NOT introduce public badges, filters, audit trail, automated analysis, approval/rejection language, ads, credits, tickets, draw logic, payments, or public participation eligibility changes.

#### Scenario: Status does not change public entry eligibility

- GIVEN a raffle can accept participants under the existing participation rules
- WHEN a public visitor submits a valid registration
- THEN the registration flow follows the existing eligibility behavior
- AND status actions do not add public approval steps

#### Scenario: Active registration is marked for review

- GIVEN an active registration exists
- WHEN an authenticated admin flags it
- THEN its status becomes `flagged`
- AND feedback says it was marked for review

#### Scenario: Active registration is cancelled

- GIVEN an active registration exists
- WHEN an authenticated admin cancels it
- THEN its status becomes `cancelled`
- AND feedback says it was cancelled

#### Scenario: Terminal status blocks mutation

- GIVEN a registration is `flagged` or `cancelled`
- WHEN an admin attempts another status action
- THEN the system MUST reject the mutation
- AND feedback says the action is no longer available
