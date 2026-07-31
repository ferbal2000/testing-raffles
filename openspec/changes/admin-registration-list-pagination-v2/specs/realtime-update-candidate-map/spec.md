# Delta for Realtime Update Candidate Map

## MODIFIED Requirements

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
