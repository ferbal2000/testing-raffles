# Delta for Raffle Registration Status

## MODIFIED Requirements

### Requirement: Status foundation has no operational side effects

The system MUST preserve public registration behavior while allowing authenticated admins to change eligible active registrations to `flagged` or `cancelled`, and eligible flagged registrations back to `active` by clearing review. `flagged` MUST mean retained but requiring review. Restoring `flagged` MUST clear review and return it to `active`. `cancelled` MUST mean annulled/not valid, retained for traceability, and terminal. Feedback SHOULD say marked for review, cancelled, review cleared/restored to active, or action no longer available. The system MUST NOT introduce public badges, filters, audit trail, automated analysis, approval/rejection language, ads, credits, tickets, draw logic, payments, or public eligibility changes.
(Previously: `flagged` and `cancelled` were both terminal with no restore or further mutation.)

#### Scenario: Status does not change public entry eligibility

- GIVEN a raffle can accept participants under the existing participation rules
- WHEN a public visitor submits a valid registration
- THEN the registration follows existing eligibility behavior
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

#### Scenario: Active registration rejects restore

- GIVEN an authenticated admin on the admin host has an active registration for the requested raffle
- WHEN the admin attempts to restore it through that raffle scope
- THEN the system MUST reject the mutation
- AND its status remains `active`
- AND feedback says the action is no longer available

#### Scenario: Flagged registration review is cleared

- GIVEN an authenticated admin on the admin host has a flagged registration for the requested raffle
- WHEN the admin restores it to active through that raffle scope
- THEN its status becomes `active`
- AND feedback says review was cleared/restored to active

#### Scenario: Restore rejects a registration outside the requested raffle

- GIVEN an authenticated admin on the admin host has a flagged registration belonging to another raffle
- WHEN the admin attempts to restore it through the requested raffle scope
- THEN the registration MUST be treated as not found and the request returns `404`
- AND its status remains `flagged`

#### Scenario: Flagged registration rejects non-restore status actions

- GIVEN an authenticated admin on the admin host has a flagged registration for the requested raffle
- WHEN the admin attempts to flag or cancel it through that raffle scope
- THEN the system MUST reject the mutation
- AND its status remains `flagged`
- AND feedback says the action is no longer available

#### Scenario: Cancelled registration rejects every status mutation

- GIVEN an authenticated admin on the admin host has a cancelled registration for the requested raffle
- WHEN the admin attempts any status action or restore through that raffle scope
- THEN the system MUST reject the mutation
- AND its status remains `cancelled`
- AND feedback says the action is no longer available
