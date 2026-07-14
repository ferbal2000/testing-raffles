# Delta for Realtime Update Candidate Map

## MODIFIED Requirements

### Requirement: Delivered observable changes are mapped

The system MUST document realtime-update candidates only for delivered behavior. The map SHALL cover screens affected by raffle publication/closure, participation open/close, guest registration creation, persisted count visibility, and admin status changes including review-clearing restore to active. Future event names MAY appear only as non-implemented labels; runtime realtime behavior is out of scope.

| Change | Admin candidates | Public candidates | Future label |
|---|---|---|---|
| Draft raffle published | Admin raffle list | Public catalog/detail | `RafflePublished` (not implemented) |
| Published raffle closed | Admin raffle list, registration-list context | Public catalog/detail | `RaffleClosed` (not implemented) |
| Participation opened | Admin raffle list, registration-list context | Public detail count visibility | `ParticipationOpened` (not implemented) |
| Participation closed | Admin raffle list, registration-list context | Public detail count visibility | `ParticipationClosed` (not implemented) |
| Guest registration created | Admin raffle-list counts, registration-list summary | Public detail count visibility while open | `RegistrationCreated` (not implemented) |
| Registration flagged, cancelled, or restored to active from flagged | Admin registration-list status/totals | None | `RegistrationStatusChanged` (not implemented) |

(Previously: the map included flag/cancel status changes but not delivered restore-to-active status changes.)

#### Scenario: Delivered public visibility change is captured

- GIVEN a draft raffle is published under delivered lifecycle behavior
- WHEN the candidate map is reviewed
- THEN public catalog/detail screens MUST be future update candidates
- AND the event label MUST be marked not implemented

#### Scenario: Delivered count surfaces are captured

- GIVEN persisted registration count visibility is delivered on public detail and admin registration list screens
- WHEN the candidate map is reviewed
- THEN both count surfaces MUST be future update candidates
- AND no runtime realtime behavior SHALL be implied

#### Scenario: Delivered admin status change is captured as future-only

- GIVEN admin flag, cancel, or flagged-to-active restore is delivered
- WHEN the candidate map is reviewed
- THEN the admin registration list MUST be a future update candidate
- AND no public screen or runtime transport SHALL be implied

#### Scenario: Undelivered workflow is excluded

- GIVEN a workflow has not been delivered by an existing spec
- WHEN the candidate map is updated
- THEN the workflow MUST NOT be added as a realtime candidate
- AND no future event label SHALL imply implemented runtime behavior
