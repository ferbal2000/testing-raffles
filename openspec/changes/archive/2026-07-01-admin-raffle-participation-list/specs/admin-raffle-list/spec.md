# Delta for admin-raffle-list

## ADDED Requirements

### Requirement: Admin raffle index provides registration list entry points

The system MUST provide a per-row entry point from the admin raffle index to `GET /raffles/{raffle}/registrations` for each persisted raffle shown on the index. The entry point MUST remain read-only and MUST NOT expose registration management, export, or status controls. The index MAY show a simple registration count, but when shown it MUST reflect persisted registrations for that raffle only.

#### Scenario: Admin uses a registrations entry point from the index

- GIVEN one or more raffle records already exist
- WHEN an authenticated admin opens the raffle index
- THEN each persisted raffle row shows a registrations entry point linking to `GET /raffles/{raffle}/registrations`

#### Scenario: Entry point stays available for zero registrations

- GIVEN a persisted raffle currently has zero stored registrations
- WHEN an authenticated admin opens the raffle index
- THEN that raffle row still shows the registrations entry point
- AND any shown registration count reflects zero persisted registrations
