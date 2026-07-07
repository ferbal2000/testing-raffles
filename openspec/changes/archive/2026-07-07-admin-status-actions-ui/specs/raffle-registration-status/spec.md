# Delta for Raffle Registration Status

## MODIFIED Requirements

### Requirement: Status foundation has no operational side effects

The system MUST preserve public registration behavior while allowing authenticated admins to mutate eligible active registrations to `flagged` or `cancelled`. `flagged` MUST mean retained but requiring review. `cancelled` MUST mean annulled/not valid while retained for traceability. In this slice, `flagged` and `cancelled` MUST be terminal: no restore, reactivate, or further mutation from those states. Feedback SHOULD clearly say marked for review, cancelled, or action no longer available. The system MUST NOT introduce public badges, filters, audit trail, automated analysis, approval/rejection language, ads, credits, tickets, draw logic, payments, or public participation eligibility changes.
(Previously: the foundation introduced no admin UI or actions.)

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
