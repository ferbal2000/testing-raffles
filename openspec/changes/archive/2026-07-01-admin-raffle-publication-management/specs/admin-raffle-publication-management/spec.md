# Admin Raffle Publication Management Specification

## Purpose

Define the admin-only action that publishes draft raffles through the existing raffle lifecycle rule.

## Requirements

### Requirement: Admins publish draft raffles only

The system MUST expose an admin-only publish action for persisted draft raffles. The action MUST reuse the existing domain lifecycle transition and MUST NOT add publication-blocking validations beyond current domain behavior.

#### Scenario: Admin publishes a draft raffle

- GIVEN an authenticated admin and a persisted raffle in `draft`
- WHEN the admin confirms and submits the publish action
- THEN the raffle status becomes `published`
- AND the admin is redirected to the raffle index with publish success feedback

#### Scenario: Guest cannot publish a raffle

- GIVEN a persisted raffle in `draft`
- WHEN an unauthenticated requester submits the publish action on the admin host
- THEN the system rejects the request using existing admin authentication behavior
- AND the raffle remains `draft`

#### Scenario: Non-draft publish submission is rejected

- GIVEN a persisted raffle is not in `draft`
- WHEN an authenticated admin submits the publish action
- THEN the system rejects the lifecycle transition with feedback
- AND the raffle status is unchanged

### Requirement: Publishing changes public visibility only

The system SHALL treat successful publication as the existing `draft -> published` lifecycle transition. Publishing MUST NOT open participation, close participation, select winners, create tickets, moderate registrations, or create a reversible published-to-draft workflow.

#### Scenario: Published raffle becomes publicly resolvable

- GIVEN a draft raffle is not publicly resolvable
- WHEN an authenticated admin successfully publishes it
- THEN the raffle becomes publicly resolvable under existing published-only visibility rules

#### Scenario: Publishing does not open participation

- GIVEN a draft raffle has null participation open and close timestamps
- WHEN an authenticated admin successfully publishes it
- THEN participation timestamps remain unchanged
- AND participation acceptance is still governed by the existing participation rule
