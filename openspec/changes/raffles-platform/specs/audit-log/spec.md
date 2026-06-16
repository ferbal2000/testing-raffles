# Audit Log Specification

## Purpose

Capture immutable evidence for sensitive first-slice actions.

## Invariants

- Audit events MUST be append-only.
- Each event MUST include actor type, actor identifier, action, target entity, outcome, and timestamp.

## Out of Scope

External compliance exports and public audit views.

## Requirements

### Requirement: Sensitive action audit trail

The system MUST record audit events for raffle publish, raffle close, accepted entry submission, successful draw, and rejected sensitive draw attempts.

#### Scenario: Successful actions are recorded

- GIVEN a valid publish, entry acceptance, or draw command
- WHEN the command succeeds
- THEN one audit event is appended for that action with outcome `succeeded`

#### Scenario: Rejected draw attempt is recorded

- GIVEN a draw command that fails guardrails or authorization
- WHEN the command is rejected
- THEN one audit event is appended with outcome `rejected`

### Requirement: Immutable audit history

The system SHALL NOT update or delete existing audit events through normal application flows.

#### Scenario: Existing event remains unchanged

- GIVEN a stored audit event
- WHEN later raffle or draw activity occurs
- THEN the existing event remains unchanged and new events are appended
