# Delta for Realtime Update Candidate Map

## MODIFIED Requirements

### Requirement: Delivered observable changes are mapped

The system MUST document realtime-update candidates only for delivered behavior. The map SHALL cover admin and public screens affected by raffle publication, raffle closure, participation open/close, guest registration creation, persisted registration count visibility, and admin registration status changes. Future event names MAY appear only as non-implemented candidate labels; no runtime realtime behavior is in scope.

| Delivered change | Admin screen candidates | Public screen candidates | Future event candidate label |
|---|---|---|---|
| Draft raffle is published | Admin raffle list | Public catalog, public detail | `RafflePublished` (not implemented) |
| Published raffle is closed | Admin raffle list, registration list context | Public catalog, public detail | `RaffleClosed` (not implemented) |
| Participation is opened | Admin raffle list, registration list context | Public detail registration count visibility | `ParticipationOpened` (not implemented) |
| Participation is closed | Admin raffle list, registration list context | Public detail registration count visibility | `ParticipationClosed` (not implemented) |
| Guest registration is created | Admin raffle list counts, admin registration list count summary | Public detail registration count visibility while participation is open | `RegistrationCreated` (not implemented) |
| Registration is flagged or cancelled | Admin registration list status and totals | None | `RegistrationStatusChanged` (not implemented) |

(Previously: the map did not include admin registration status changes.)

#### Scenario: Delivered public visibility change is captured

- GIVEN a draft raffle is published under delivered lifecycle behavior
- WHEN the candidate map is reviewed
- THEN public catalog and detail screens MUST be listed as future update candidates
- AND the event label MUST be marked not implemented

#### Scenario: Delivered count surfaces are captured

- GIVEN persisted registration count visibility is delivered on public detail and admin registration list screens
- WHEN the candidate map is reviewed
- THEN both count surfaces MUST be listed as future update candidates
- AND no runtime realtime behavior SHALL be implied

#### Scenario: Delivered admin status change is captured as future-only

- GIVEN admin flag/cancel actions are delivered
- WHEN the candidate map is reviewed
- THEN the admin registration list MUST be listed as a future update candidate
- AND no public screen or runtime transport SHALL be implied

#### Scenario: Undelivered workflow is excluded

- GIVEN a workflow has not been delivered by an existing spec
- WHEN the candidate map is updated
- THEN the workflow MUST NOT be added as a realtime candidate
- AND no future event label SHALL imply implemented runtime behavior
