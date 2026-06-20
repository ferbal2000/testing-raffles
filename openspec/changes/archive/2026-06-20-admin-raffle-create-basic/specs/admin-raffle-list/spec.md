# Delta for Admin Raffle List

## ADDED Requirements

### Requirement: Admin raffle index provides create entry and success feedback

The system MUST provide a minimal create entry point from the admin raffle index to `GET /raffles/create`. After a successful raffle create redirect, the index MUST render a scoped success flash for that completed action only. This slice MUST NOT add broader admin navigation or lifecycle actions.

#### Scenario: Admin uses the create entry point from the index

- GIVEN an authenticated admin opens the raffle index
- WHEN the page is rendered
- THEN the system shows a create entry point linking to `GET /raffles/create`

#### Scenario: Index shows success feedback after create

- GIVEN an authenticated admin is redirected to the raffle index after a successful create
- WHEN the index page is rendered for that redirect
- THEN the system shows a minimal success flash for the created raffle

#### Scenario: Index does not invent success feedback

- GIVEN an authenticated admin opens the raffle index without a successful create redirect
- WHEN the page is rendered
- THEN the system does not show a create success flash
