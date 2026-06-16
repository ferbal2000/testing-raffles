# Raffle Entries Specification

## Purpose

Accept participant entries safely for a published raffle without weakening the separate identity model.

## Invariants

- Entries MUST reference one published raffle and one public user account.
- Admin accounts MUST NOT be valid participants unless a separate public user account exists.

## Out of Scope

Paid entries, advanced eligibility rules, and notifications.

## Requirements

### Requirement: Entry acceptance with idempotency

The system MUST accept an entry only for a published raffle within its entry window and MUST enforce idempotency for repeated submission attempts.

#### Scenario: First valid entry is accepted

- GIVEN a published raffle inside its entry window and an authenticated public user
- WHEN the user submits an entry with a new idempotency key
- THEN one accepted entry is stored in the same database for that raffle and user

#### Scenario: Duplicate submission is collapsed

- GIVEN an accepted entry for a raffle, user, and idempotency key
- WHEN the same submission is retried
- THEN no second entry is created and the original acceptance result is returned

#### Scenario: Admin identity cannot enter

- GIVEN an admin credential without a separate public user account
- WHEN an entry submission is attempted on the public flow
- THEN the submission is rejected
