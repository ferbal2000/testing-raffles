# Raffle Lifecycle Specification

## Purpose

Define the first persisted raffle domain slice with a testable `draft -> published -> closed` lifecycle.

## Requirements

### Requirement: Persisted raffle lifecycle record

The system MUST persist a raffle record with lifecycle status and basic availability fields. A new raffle MUST start in `draft`. The persisted lifecycle states for this slice MUST be limited to `draft`, `published`, and `closed`.

#### Scenario: Create a draft raffle record

- GIVEN a new raffle is created with valid raffle data
- WHEN the record is persisted
- THEN the raffle is stored with status `draft`
- AND the persisted record exposes `starts_at` and `ends_at` fields

#### Scenario: Unsupported lifecycle state is rejected

- GIVEN a raffle persistence attempt uses a state outside `draft`, `published`, or `closed`
- WHEN the record is validated or persisted
- THEN the system rejects that unsupported state

### Requirement: Publish from draft only

The system MUST allow a raffle to transition from `draft` to `published`, and it MUST NOT publish a raffle from any other state.

#### Scenario: Publish a draft raffle

- GIVEN a persisted raffle in `draft`
- WHEN the publish action is executed
- THEN the raffle status becomes `published`

#### Scenario: Closed raffle cannot be republished

- GIVEN a persisted raffle in `closed`
- WHEN the publish action is executed
- THEN the system rejects the transition

### Requirement: Close from published only

The system MUST allow a raffle to transition from `published` to `closed`, and it MUST NOT close a raffle directly from `draft`.

#### Scenario: Close a published raffle

- GIVEN a persisted raffle in `published`
- WHEN the close action is executed
- THEN the raffle status becomes `closed`

#### Scenario: Draft raffle cannot close directly

- GIVEN a persisted raffle in `draft`
- WHEN the close action is executed
- THEN the system rejects the transition

### Requirement: Availability fields are basic lifecycle data

The system MAY store `starts_at` and `ends_at` on a raffle as basic lifecycle and availability data, but this slice MUST NOT require automatic scheduling behavior or time-driven state changes.

#### Scenario: Persist explicit availability values

- GIVEN a new raffle includes `starts_at` and `ends_at` values
- WHEN the raffle is persisted
- THEN those values are stored with the raffle record

#### Scenario: Time does not auto-transition lifecycle

- GIVEN a persisted raffle has past or future `starts_at` or `ends_at` values
- WHEN lifecycle behavior is evaluated in this slice
- THEN status changes occur only through explicit publish or close actions

### Requirement: Lifecycle verification uses the canonical test runner

The system MUST prove the supported lifecycle and rejected transitions with automated tests that run through `bin/test`.

#### Scenario: Lifecycle suite runs through repository runner

- GIVEN raffle lifecycle tests exist
- WHEN the verification command is executed
- THEN the lifecycle coverage runs through `bin/test`
