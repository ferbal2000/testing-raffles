# Realtime Update Candidate Map Specification

## Purpose

Define the documentation-only map of delivered raffle state changes that should be considered for future realtime or reactive screen updates. This capability SHALL NOT implement runtime broadcasting, listeners, channels, event classes, dispatch wiring, or application behavior changes.

## Requirements

### Requirement: Delivered observable changes are mapped

The system MUST document realtime-update candidates only for delivered behavior. The map SHALL cover admin and public screens affected by raffle publication, raffle closure, participation open/close, guest registration creation, and registration count visibility. Future event names MAY appear only as non-implemented candidate labels.

| Delivered change | Admin screen candidates | Public screen candidates | Future event candidate label |
|---|---|---|---|
| Draft raffle is published | Admin raffle list | Public catalog, public detail | `RafflePublished` (not implemented) |
| Published raffle is closed | Admin raffle list, registration list context | Public catalog, public detail | `RaffleClosed` (not implemented) |
| Participation is opened | Admin raffle list, registration list context | Public detail | `ParticipationOpened` (not implemented) |
| Participation is closed | Admin raffle list, registration list context | Public detail | `ParticipationClosed` (not implemented) |
| Guest registration is created | Admin raffle list counts, admin registration list | Public detail confirmation/count-adjacent copy | `RegistrationCreated` (not implemented) |

#### Scenario: Delivered public visibility change is captured

- GIVEN a draft raffle is published under delivered lifecycle behavior
- WHEN the candidate map is reviewed
- THEN public catalog and detail screens MUST be listed as future update candidates
- AND the event label MUST be marked not implemented

#### Scenario: Undelivered workflow is excluded

- GIVEN a workflow has not been delivered by an existing spec
- WHEN the candidate map is updated
- THEN the workflow MUST NOT be added as a realtime candidate
- AND no future event label SHALL imply implemented runtime behavior

### Requirement: Current request-response behavior is preserved

This capability MUST remain documentation-only. Current admin and public Blade screens SHALL continue to update through normal request, redirect, and page-render cycles until a future runtime realtime slice explicitly changes behavior.

#### Scenario: No runtime transport is introduced

- GIVEN this capability is delivered
- WHEN application behavior is evaluated
- THEN there MUST be no new broadcasting transport, listener, channel, event class, or dispatch wiring
- AND existing screens SHALL keep their current refresh behavior

#### Scenario: Labels are not executable contracts

- GIVEN a future event candidate label appears in the map
- WHEN implementation scope is evaluated
- THEN the label SHALL be treated as planning vocabulary only
- AND no runtime event MUST be assumed to exist

### Requirement: Future interactive slices maintain the map

Every future SDD slice that introduces an observable interactive state change MUST update this candidate map during that slice. After broader product development, the system SHALL require a final product pass to decide whether additional delivered candidates are missing.

#### Scenario: New observable interaction is delivered later

- GIVEN a future SDD slice introduces an observable admin or public state change
- WHEN that slice reaches specification or design work
- THEN it MUST update this candidate map in the same slice

#### Scenario: Final product pass checks completeness

- GIVEN broader raffle product development has progressed
- WHEN the final product pass is performed
- THEN the candidate map SHALL be reviewed against delivered behavior
- AND missing delivered realtime candidates SHOULD be added before runtime implementation planning
