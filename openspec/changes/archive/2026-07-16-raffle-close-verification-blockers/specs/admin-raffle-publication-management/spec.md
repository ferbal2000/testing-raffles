# Delta for Admin Raffle Publication Management

## MODIFIED Requirements

### Requirement: Admins publish draft raffles only

The system MUST expose an admin-only publish action for persisted draft raffles. Within one transaction, the action MUST re-fetch the targeted raffle, acquire `lockForUpdate()`, and revalidate the fresh committed lifecycle state before reusing the existing domain publish transition. It MUST NOT add publication-blocking validations beyond current domain behavior. A non-draft committed state MUST produce the existing invalid-publish feedback, no publish-success flash, and no business-data mutation.

(Previously: publication trusted the route-bound model state and did not require locked revalidation of committed state.)

#### Scenario: Admin publishes a draft raffle

- GIVEN an authenticated admin and a raffle whose committed status is `draft`
- WHEN the admin confirms and submits the publish action
- THEN the locked fresh raffle transitions to `published`
- AND the admin is redirected to the raffle index with publish success feedback

#### Scenario: Guest cannot publish a raffle

- GIVEN a persisted raffle in `draft`
- WHEN an unauthenticated requester submits the publish action on the admin host
- THEN the system rejects the request using existing admin authentication behavior
- AND the raffle remains `draft`

#### Scenario: Non-draft publish submission is rejected

- GIVEN a persisted raffle's committed status is not `draft`
- WHEN an authenticated admin submits the publish action
- THEN the system rejects the lifecycle transition with existing invalid-publish feedback and no publish-success flash
- AND the raffle status and other business data are unchanged

#### Scenario: Stale draft-bound publish cannot reopen a closed raffle

- GIVEN the submitted route-bound model still says `draft` after the raffle was committed as `closed`
- WHEN an authenticated admin submits the publish action
- THEN fresh locked revalidation rejects publication with existing invalid-publish feedback and no publish-success flash
- AND status, availability fields, participation audit fields, and registrations remain unchanged
- AND the raffle remains unresolved by existing public published-only lookup

#### Scenario: Publish emits deterministic lock-before-update evidence

- GIVEN SQL statements for a successful publish command are recorded in execution order
- WHEN the recorded statements are inspected
- THEN a raffle-row `SELECT ... FOR UPDATE` appears before the raffle update
- AND this ordering is evidence of the command protocol, not proof of simultaneous-session lock waiting
