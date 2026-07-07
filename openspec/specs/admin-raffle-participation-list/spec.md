# Admin Raffle Participation List Specification

## Purpose

Define admin-only per-raffle visibility into stored guest registrations without introducing operational participation management.

## Requirements

### Requirement: Protected per-raffle registration visibility

The system MUST expose an admin-host page at `GET /raffles/{raffle}/registrations` for authenticated admins only. The page MUST list stored registrations for that raffle newest-first and MUST show only registration fields already captured by the current model: `name`, normalized `email`, `created_at`, and any existing linked-user signal. The page MUST remain read-only and MUST NOT imply ticket, payment, draw, export, notification, or mutation semantics.

#### Scenario: Authenticated admin opens a raffle registration list

- GIVEN an authenticated admin session on the admin host
- WHEN the admin requests `GET /raffles/{raffle}/registrations` for a raffle with stored registrations
- THEN the system returns a read-only registrations page for that raffle newest-first
- AND each visible row shows only the allowed stored registration fields

#### Scenario: Guest requests a raffle registration list

- GIVEN the request targets the admin host registrations page
- WHEN the requester is not authenticated as an admin
- THEN the system follows the existing admin authentication behavior

### Requirement: Explicit empty and sparse registration states

The system MUST show an explicit empty state when a raffle has no stored registrations. The system MUST also render registrations that lack an existing linked-user signal without inventing account linkage or admin actions.

#### Scenario: Raffle has no registrations

- GIVEN a persisted raffle has no stored registrations
- WHEN an authenticated admin opens its registrations page
- THEN the system shows an explicit empty state instead of an empty table

#### Scenario: Registration has no linked-user signal

- GIVEN a raffle registration exists without a linked user reference
- WHEN an authenticated admin opens the registrations page
- THEN the system still shows the registration row
- AND the page does not invent account linkage or management controls

### Requirement: Read-only current raffle registration summary

The system MUST show a read-only summary count for the current raffle on `GET /raffles/{raffle}/registrations`. The count MUST reflect persisted registrations for that raffle only and MUST NOT imply ticket, capacity, payment, draw, export, notification, ranking, eligibility, or mutation semantics.

#### Scenario: Summary count appears with registrations

- GIVEN an authenticated admin opens a raffle registration list with stored registrations
- WHEN the page is rendered
- THEN a read-only summary count for that raffle is visible
- AND the registration list remains newest-first and read-only

#### Scenario: Summary count appears for empty list

- GIVEN an authenticated admin opens a raffle registration list with no stored registrations
- WHEN the page is rendered
- THEN the summary count reflects zero persisted registrations
- AND the explicit empty state remains visible
