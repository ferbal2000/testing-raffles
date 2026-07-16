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

The system MUST allow a raffle to transition from `draft` to `published` only after the command revalidates the fresh committed lifecycle state. It MUST NOT publish from `published` or `closed`, and closure MUST remain irreversible: no stale model or later publish submission may reopen a committed `closed` raffle.

(Previously: rejected transitions were defined against persisted state without explicitly requiring fresh committed-state revalidation.)

#### Scenario: Publish a draft raffle

- GIVEN a persisted raffle whose fresh committed status is `draft`
- WHEN the publish action is executed
- THEN the raffle status becomes `published`

#### Scenario: Closed raffle cannot be republished

- GIVEN a persisted raffle whose committed status is `closed`
- WHEN the publish action is executed
- THEN the system rejects the transition
- AND the raffle remains `closed` and unavailable through published-only public lookup

#### Scenario: Stale draft state does not override committed closure

- GIVEN a caller holds an earlier `draft` representation of a raffle now committed as `closed`
- WHEN the publish command revalidates the fresh committed state
- THEN publication is rejected and no raffle business data changes

### Requirement: Close from published only

The system MUST allow an authenticated admin to close a raffle only when its fresh committed lifecycle state is `published`, regardless of whether participation is active, already closed, or never opened. The confirmed close action SHALL serve as temporary manual certification without a separate certification record or workflow. A successful close MUST atomically set `closed`, remove public availability, freeze the participant set, close active participation, and establish conceptual eligibility for a future draw only. Closure MUST NOT create `ready_to_draw` or `drawn` state, perform a draw, or imply revenue automation.

(Previously: published raffles could close, but authenticated operation, committed-state eligibility, irreversibility, participant freezing, and atomic participation closure were unspecified.)

#### Scenario: Close a published raffle

- GIVEN a persisted raffle whose fresh committed status is `published`
- WHEN an authenticated admin submits the confirmed close action
- THEN the raffle becomes `closed`, leaves public availability, and freezes its participant set
- AND any active participation closes in the same atomic outcome

#### Scenario: Draft raffle cannot close directly

- GIVEN a persisted raffle in `draft`
- WHEN the close action is executed
- THEN the system rejects the transition

#### Scenario: Already-closed raffle cannot close again

- GIVEN a persisted raffle whose committed status is `closed`
- WHEN an authenticated admin submits another close action
- THEN the system rejects the transition and mutates no lifecycle or audit data

#### Scenario: Coupled closure cannot partially persist

- GIVEN a `published` raffle whose required active-participation closure cannot complete
- WHEN an authenticated admin attempts overall close
- THEN neither `closed` status nor any participation closure change persists

#### Scenario: Closure creates conceptual future draw eligibility only

- GIVEN a raffle has closed successfully
- WHEN its lifecycle meaning and persisted state are inspected
- THEN it is conceptually eligible for a future draw but remains `closed` without `ready_to_draw` or `drawn` state
- AND no winner selection, payment, advertising, revenue, or draw execution is implied

### Requirement: Availability fields are basic lifecycle data

The system MAY store `starts_at` and `ends_at` on a raffle as basic lifecycle and availability metadata, but this slice MUST NOT require automatic scheduling behavior, time-driven state changes, or direct participation eligibility decisions from those fields. Participation eligibility SHALL be defined separately through the canonical participation domain rule.

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

### Requirement: Published status governs publication only

The system SHALL treat raffle `published` status as publication and public visibility state, not as a standalone permission to accept participants. On the public raffle detail route, only `published` raffles SHALL resolve. Raffles in `draft` or `closed` MUST be excluded during query, routing, or model binding rather than loaded and then visually hidden. The public route remains ID-first, and any future optional slug suffix MUST preserve the same published-only resolution contract.

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

#### Scenario: Published raffle resolves on the public detail route

- GIVEN a raffle has status `published`
- WHEN a public user requests `/raffles/{id}`
- THEN the public detail route resolves that raffle

#### Scenario: Non-published raffle is filtered before rendering

- GIVEN a raffle has status `draft` or `closed`
- WHEN a public user requests `/raffles/{id}`
- THEN the system returns `404`
- AND no hidden raffle detail view is rendered

### Requirement: Lifecycle verification uses the canonical test runner

The system MUST prove the supported lifecycle and rejected transitions with automated tests that run through `bin/test`.

#### Scenario: Lifecycle suite runs through repository runner

- GIVEN raffle lifecycle tests exist
- WHEN the verification command is executed
- THEN the lifecycle coverage runs through `bin/test`
