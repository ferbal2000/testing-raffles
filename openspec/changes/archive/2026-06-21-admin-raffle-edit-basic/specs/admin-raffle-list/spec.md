# Delta for Admin Raffle List

## MODIFIED Requirements

### Requirement: Admin raffle index provides create and edit entry points with scoped success feedback

The system MUST provide a minimal create entry point from the admin raffle index to `GET /raffles/create`. The system MUST also provide a minimal per-row edit entry point to `GET /raffles/{raffle}/edit` for each persisted raffle shown on the index. After a successful raffle create or update redirect, the index MUST render a scoped success flash for that completed action only. This slice MUST NOT add broader admin navigation or lifecycle actions.

(Previously: The index only required a create entry point and create success flash.)

#### Scenario: Admin uses the create entry point from the index

- GIVEN an authenticated admin opens the raffle index
- WHEN the page is rendered
- THEN the system shows a create entry point linking to `GET /raffles/create`

#### Scenario: Index shows success feedback after create

- GIVEN an admin is redirected to the index after create
- WHEN the index page is rendered for that redirect
- THEN the system shows a minimal success flash for the created raffle

#### Scenario: Admin uses the edit entry point from the index

- GIVEN one or more raffle records already exist
- WHEN an authenticated admin opens the raffle index
- THEN each persisted raffle row shows a minimal edit entry point linking to `GET /raffles/{raffle}/edit`

#### Scenario: Index shows success feedback after update

- GIVEN an authenticated admin is redirected to the raffle index after a successful update
- WHEN the index page is rendered for that redirect
- THEN the system shows a minimal success flash for the updated raffle

#### Scenario: Index does not invent success feedback

- GIVEN an authenticated admin opens the raffle index without a successful create or update redirect
- WHEN the page is rendered
- THEN the system does not show a create or update success flash
