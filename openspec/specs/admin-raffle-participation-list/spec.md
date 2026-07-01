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
