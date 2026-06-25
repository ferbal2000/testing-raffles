# Delta for admin-raffle-list

## MODIFIED Requirements

### Requirement: Admin raffle index provides create, edit, and participation lifecycle entry points with scoped success feedback

The system MUST provide a minimal create entry point from the admin raffle index to `GET /raffles/create`. The system MUST also provide a minimal per-row edit entry point to `GET /raffles/{raffle}/edit` for each persisted raffle shown on the index. The system SHALL expose an admin-only manual participation open action for published raffles with null `participation_opened_at` and null `participation_closed_at`, and an admin-only manual participation close action for published raffles with non-null `participation_opened_at` and null `participation_closed_at`. The index MUST NOT expose participation actions for `draft`, overall `closed`, or already participation-closed raffles. After a successful raffle create, update, participation open, or participation close redirect, the index MUST render scoped success feedback for that completed action only.

(Previously: the index only required create and edit entry points with scoped success feedback after create or update, and it explicitly excluded lifecycle actions.)

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

#### Scenario: Index shows manual open action only for eligible raffles

- GIVEN a raffle is published and has neither `participation_opened_at` nor `participation_closed_at`
- WHEN an authenticated admin opens the raffle index
- THEN that raffle row shows a manual participation open action

#### Scenario: Index shows manual close action only for opened participation

- GIVEN a raffle is published, has non-null `participation_opened_at`, and has null `participation_closed_at`
- WHEN an authenticated admin opens the raffle index
- THEN that raffle row shows a manual participation close action

#### Scenario: Index hides participation actions for ineligible states

- GIVEN a raffle is `draft`, `closed`, or already has non-null `participation_closed_at`
- WHEN an authenticated admin opens the raffle index
- THEN that raffle row shows no manual participation action

#### Scenario: Index shows scoped success feedback after participation change

- GIVEN an authenticated admin is redirected to the raffle index after a successful manual participation open or close action
- WHEN the index page is rendered for that redirect
- THEN the system shows a minimal success flash only for that completed participation action

#### Scenario: Index does not invent success feedback

- GIVEN an authenticated admin opens the raffle index without a successful create, update, or participation lifecycle redirect
- WHEN the page is rendered
- THEN the system does not show a create, update, or participation lifecycle success flash
