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
