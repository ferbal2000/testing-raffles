# Draw Control Specification

## Purpose

Allow an authorized draw exactly once with clear guardrails and a persisted winner.

## Invariants

- Only admin actors on the admin surface MAY trigger a draw.
- A raffle MUST have at least one accepted entry before a draw succeeds.
- A drawn raffle SHALL NOT produce another winner.

## Out of Scope

Public fairness proofs, approval workflows, and multiple winners.

## Requirements

### Requirement: Exactly-once winner draw

The system MUST execute winner selection only for a closed raffle, persist one winner, and prevent repeated draw execution.

#### Scenario: Closed raffle is drawn successfully

- GIVEN a closed raffle with accepted entries and an authenticated admin
- WHEN the admin executes the draw command
- THEN exactly one winner is persisted and the raffle becomes `drawn`

#### Scenario: Repeated draw is blocked

- GIVEN a raffle already marked `drawn`
- WHEN any admin retries the draw command
- THEN the command is rejected and no second winner is persisted

#### Scenario: Empty raffle cannot be drawn

- GIVEN a closed raffle with no accepted entries
- WHEN an admin executes the draw command
- THEN the command is rejected
