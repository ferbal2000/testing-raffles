# Delta for raffle-lifecycle

## ADDED Requirements

### Requirement: Published status governs publication only

The system SHALL treat raffle `published` status as publication and visibility state, not as a standalone permission to accept participants.

#### Scenario: Published raffle is visible before participation opens

- GIVEN a raffle has status `published` and a null `participation_opened_at`
- WHEN lifecycle behavior is evaluated
- THEN the raffle remains published
- AND it MUST NOT accept participants yet

#### Scenario: Closed raffle cannot accept participants

- GIVEN a raffle has status `closed`
- WHEN lifecycle behavior is evaluated
- THEN the raffle remains closed
- AND it MUST NOT accept participants

## MODIFIED Requirements

### Requirement: Availability fields are basic lifecycle data

The system MAY store `starts_at` and `ends_at` on a raffle as basic lifecycle and availability metadata, but this slice MUST NOT require automatic scheduling behavior, time-driven state changes, or direct participation eligibility decisions from those fields. Participation eligibility SHALL be defined separately through the canonical participation domain rule.

(Previously: `starts_at` and `ends_at` were described only as basic lifecycle data without explicitly separating them from participation eligibility.)

#### Scenario: Persist explicit availability values

- GIVEN a new raffle includes `starts_at` and `ends_at` values
- WHEN the raffle is persisted
- THEN those values are stored with the raffle record

#### Scenario: Time does not auto-transition lifecycle

- GIVEN a persisted raffle has past or future `starts_at` or `ends_at` values
- WHEN lifecycle behavior is evaluated in this slice
- THEN status changes occur only through explicit publish or close actions

#### Scenario: Availability dates do not open participation

- GIVEN a published raffle has `starts_at` or `ends_at` values and a null `participation_opened_at`
- WHEN participation eligibility is evaluated
- THEN those dates do not make the raffle accept participants
