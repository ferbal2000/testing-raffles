# Delta for Raffle Participation Lifecycle

## ADDED Requirements

### Requirement: Overall raffle closure preserves participation audit integrity

When an authenticated admin closes a raffle whose fresh committed lifecycle state is `published`, the system MUST atomically close participation only when it is active. Eligibility MUST NOT depend on participation being active, already closed, or never opened. Active participation MUST receive a closure timestamp, `participation_closed_reason = raffle_closed`, and the authenticated admin identity. Existing closure metadata MUST be preserved; never-opened participation timestamps MUST remain null; registrations MUST NOT be added, removed, or changed. Reopen and future revenue-triggered initiation remain out of scope.

#### Scenario: Overall close ends active participation

- GIVEN a published raffle has opened participation that is not yet closed
- WHEN an authenticated admin successfully closes the raffle
- THEN participation receives reason `raffle_closed`, a closure timestamp, and that admin identity
- AND raffle and participation closures become observable atomically

#### Scenario: Prior participation closure audit remains unchanged

- GIVEN a published raffle already has participation closure reason, timestamp, and admin metadata
- WHEN an authenticated admin successfully closes the raffle
- THEN all existing participation closure metadata remains unchanged

#### Scenario: Never-opened participation keeps null timestamps

- GIVEN a published raffle has never opened participation
- WHEN an authenticated admin successfully closes the raffle
- THEN `participation_opened_at` and `participation_closed_at` remain null
- AND no participation closure audit is fabricated

#### Scenario: Stale participation submission stores nothing

- GIVEN a participation form was rendered while the raffle accepted participants
- WHEN the visitor submits it after committed overall closure
- THEN fresh submission-time eligibility rejects it
- AND zero registrations are persisted

#### Scenario: Participation audit failure rolls back overall closure

- GIVEN active participation requires closure metadata during overall close
- WHEN that metadata cannot be recorded
- THEN neither raffle closure nor partial participation audit changes persist
