# Admin Raffle List Specification

## Purpose

Define the first read-only admin raffle index on the admin host without expanding raffle lifecycle, CRUD, or admin navigation scope.

## Requirements

### Requirement: Protected admin raffle index access

The system MUST expose a conventional admin raffle index at `GET /raffles` on the admin host for authenticated admins. Unauthenticated requests to that route MUST follow the existing admin authentication behavior.

#### Scenario: Authenticated admin opens the raffle index

- GIVEN an authenticated admin session on the admin host
- WHEN the admin requests `GET /raffles`
- THEN the system returns the admin raffle index page

#### Scenario: Guest requests the raffle index

- GIVEN the request targets the admin host raffle index
- WHEN the requester is not authenticated as an admin
- THEN the system redirects using the existing admin login behavior

### Requirement: Minimal persisted raffle rows are visible

The system MUST render persisted raffle records in a minimal index using existing raffle data only. Each visible row MUST include `status`, `starts_at`, `ends_at`, and at least one useful persisted identifier or timestamp such as `id`, `created_at`, or `updated_at`. This slice MUST remain read-only and MUST NOT add lifecycle actions or new business rules.

#### Scenario: Persisted raffles appear in the index

- GIVEN one or more raffle records already exist
- WHEN an authenticated admin opens the raffle index
- THEN the system shows one row per persisted raffle with the required minimal fields

#### Scenario: Sparse raffle values still render safely

- GIVEN a persisted raffle has missing or blank availability values allowed by the current schema
- WHEN an authenticated admin opens the raffle index
- THEN the system still shows the raffle row without inventing new raffle data

### Requirement: Explicit empty state without broader admin restructuring

The system MUST show an explicit empty state when no raffles exist. This slice MUST NOT require broader admin navigation or dashboard restructuring beyond making the direct raffle index page available.

#### Scenario: No raffles exist

- GIVEN no raffle records exist
- WHEN an authenticated admin opens the raffle index
- THEN the system shows an explicit empty state instead of an empty table

#### Scenario: Raffle index stays narrowly scoped

- GIVEN the admin raffle index is available
- WHEN the page is evaluated for this slice
- THEN it exposes read-only listing behavior without requiring wider dashboard or navigation changes

### Requirement: Admin raffle index provides create and edit entry points with scoped success feedback

The system MUST provide a minimal create entry point from the admin raffle index to `GET /raffles/create`. The system MUST also provide a minimal per-row edit entry point to `GET /raffles/{raffle}/edit` for each persisted raffle shown on the index. After a successful raffle create or update redirect, the index MUST render a scoped success flash for that completed action only. This slice MUST NOT add broader admin navigation or lifecycle actions.

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
