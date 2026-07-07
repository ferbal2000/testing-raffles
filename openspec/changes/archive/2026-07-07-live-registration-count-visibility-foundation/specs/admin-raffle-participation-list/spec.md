# Delta for Admin Raffle Participation List

## ADDED Requirements

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
