# Realtime Update Candidate Map Specification

## Purpose

Define the documentation-only map of delivered raffle state changes that should be considered for future realtime or reactive screen updates. This capability SHALL NOT implement runtime broadcasting, listeners, channels, event classes, dispatch wiring, or application behavior changes.

## Requirements

### Requirement: Delivered observable changes are mapped

The system MUST document realtime-update candidates only for delivered behavior. The map SHALL cover screens affected by raffle publication, authenticated-admin overall closure, participation open/close, guest registration creation, persisted count visibility, and admin status changes including review-clearing restore to active. Future event names MAY appear only as non-implemented planning labels; runtime broadcasting, events, listeners, channels, dispatch, and automatic refresh remain out of scope.

| Change | Admin candidates | Public candidates | Future label |
|---|---|---|---|
| Draft raffle published | Admin raffle list | Public catalog/detail | `RafflePublished` (not implemented) |
| Published raffle closed by an authenticated admin | Admin raffle list, registration-list context | Public catalog/detail | `RaffleClosed` (not implemented) |
| Participation opened | Admin raffle list, registration-list context | Public detail count visibility | `ParticipationOpened` (not implemented) |
| Participation closed | Admin raffle list, registration-list context | Public detail count visibility | `ParticipationClosed` (not implemented) |
| Guest registration created | Admin raffle-list counts, registration-list summary | Public detail count visibility while open | `RegistrationCreated` (not implemented) |
| Registration flagged, cancelled, or restored to active from flagged | Admin registration-list status/totals | None | `RegistrationStatusChanged` (not implemented) |

(Previously: raffle closure was mapped generically rather than as the delivered authenticated-admin close workflow.)

#### Scenario: Delivered public visibility change is captured

- GIVEN a draft raffle is published under delivered lifecycle behavior
- WHEN the candidate map is reviewed
- THEN public catalog/detail screens MUST be future update candidates
- AND the event label MUST be marked not implemented

#### Scenario: Delivered admin close is captured as documentation only

- GIVEN authenticated-admin overall raffle closure is delivered
- WHEN the candidate map is reviewed
- THEN affected admin and public screens MUST be future update candidates
- AND no runtime event, transport, listener, channel, dispatch, or auto-refresh SHALL be implied

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

### Requirement: Current request-response behavior is preserved

This capability MUST remain documentation-only. Canonical registration pagination, progressive page fetches, status mutations, stale-conflict snapshots, and reconciliation GETs SHALL remain request-response behavior. They MUST NOT introduce polling, server-sent events, WebSockets, broadcasting, automatic refresh, or cross-session/cross-browser propagation. The admin registration list SHALL remain a **Candidate** for a future realtime slice, not a runtime realtime consumer.
(Previously: only normal request, redirect, and page-render cycles were named explicitly.)

#### Scenario: No runtime transport is introduced

- GIVEN progressive pagination and mutation reconciliation are delivered
- WHEN application behavior is evaluated
- THEN every update MUST result from the initiating browser's request and response
- AND no polling, SSE, WebSocket, broadcast, listener, channel, dispatch, automatic refresh, or cross-session propagation SHALL exist

#### Scenario: Labels are not executable contracts

- GIVEN the registration list or a future event label appears in the candidate map
- WHEN implementation scope is evaluated
- THEN the label SHALL be treated as planning vocabulary only
- AND no runtime event or browser-to-browser update MUST be assumed to exist

#### Scenario: Candidate classification remains documentation-only

- GIVEN pagination, conflict recovery, or reconciliation changes visible registration state
- WHEN the candidate map is reviewed
- THEN the admin registration list MUST remain classified as Candidate
- AND runtime realtime behavior SHALL require a separate future specification

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
