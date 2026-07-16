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

The system MUST render persisted raffles in a minimal index using existing data only. Each row MUST include `status`, `starts_at`, `ends_at`, and a useful persisted identifier or timestamp such as `id`, `created_at`, or `updated_at`. This baseline MUST NOT define lifecycle controls or new business rules; dedicated action requirements govern all controls.

(Previously: the requirement declared the index read-only and prohibited lifecycle actions, contradicting dedicated action requirements.)

#### Scenario: Persisted raffles appear in the index

- GIVEN one or more raffle records already exist
- WHEN an authenticated admin opens the raffle index
- THEN the system shows one row per persisted raffle with the required minimal fields

#### Scenario: Sparse raffle values still render safely

- GIVEN a persisted raffle has missing or blank availability values allowed by the current schema
- WHEN an authenticated admin opens the raffle index
- THEN the system still shows the raffle row without inventing new raffle data

### Requirement: Explicit empty state without broader admin restructuring

The system MUST show an explicit empty state when no raffles exist. The index MUST remain narrowly scoped and MUST NOT require broader admin navigation or dashboard restructuring beyond making the direct raffle index and its dedicated row actions available.

(Previously: the narrow-scope scenario required read-only listing behavior and therefore contradicted dedicated row actions.)

#### Scenario: No raffles exist

- GIVEN no raffle records exist
- WHEN an authenticated admin opens the raffle index
- THEN the system shows an explicit empty state instead of an empty table

#### Scenario: Raffle index stays narrowly scoped

- GIVEN the admin raffle index and its dedicated actions are available
- WHEN the page is evaluated for this slice
- THEN it requires no wider dashboard or navigation changes
- AND dedicated action requirements remain authoritative for row controls and feedback

### Requirement: Admin raffle index provides create, edit, publication, and participation lifecycle entry points with scoped feedback

The system MUST provide a minimal create entry point from the admin raffle index to `GET /raffles/create`. The system MUST also provide a minimal per-row edit entry point to `GET /raffles/{raffle}/edit` for each persisted raffle shown on the index. The system SHALL expose a confirmed admin-only publish action for draft raffles only. The system SHALL expose an admin-only manual participation open action for published raffles with null `participation_opened_at` and null `participation_closed_at`, and an admin-only manual participation close action for published raffles with non-null `participation_opened_at` and null `participation_closed_at`. The index MUST NOT expose publish actions for non-draft raffles, and MUST NOT expose participation actions for `draft`, overall `closed`, or already participation-closed raffles. After a successful raffle create, update, publish, participation open, or participation close redirect, the index MUST render scoped success feedback for that completed action only. After a rejected publish submission, the index MUST render scoped rejection feedback without inventing success feedback.

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

#### Scenario: Index shows publish action only for draft raffles

- GIVEN the index includes draft and non-draft raffles
- WHEN an authenticated admin opens the raffle index
- THEN only draft raffle rows show a confirmed publish action

#### Scenario: Index shows scoped feedback after publish submission

- GIVEN an authenticated admin is redirected to the index after a publish submission
- WHEN the index page is rendered for that redirect
- THEN the system shows minimal feedback scoped to the publish result only

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

- GIVEN an authenticated admin opens the raffle index without a successful create, update, publish, or participation lifecycle redirect
- WHEN the page is rendered
- THEN the system does not show create, update, publish, or participation lifecycle success feedback

### Requirement: Admin raffle index provides registration list entry points

The system MUST provide a per-row entry point from the admin raffle index to `GET /raffles/{raffle}/registrations` for each persisted raffle shown on the index. The entry point MUST remain read-only and MUST NOT expose registration management, export, or status controls. The existing simple registration count surface MUST remain available on the index and MUST reflect persisted registrations for that raffle only.

#### Scenario: Admin uses a registrations entry point from the index

- GIVEN one or more raffle records already exist
- WHEN an authenticated admin opens the raffle index
- THEN each persisted raffle row shows a registrations entry point linking to `GET /raffles/{raffle}/registrations`

#### Scenario: Entry point stays available for zero registrations

- GIVEN a persisted raffle currently has zero stored registrations
- WHEN an authenticated admin opens the raffle index
- THEN that raffle row still shows the registrations entry point
- AND any shown registration count reflects zero persisted registrations

### Requirement: Admin raffle index provides confirmed overall close action

The system MUST show the confirmed overall-close control only for `published` rows, whether participation is active, closed, or never opened. It MUST hide the control for `draft` and `closed` rows. Confirmation MUST warn that active participation closes and reopening is unavailable. Success MUST produce contextual translated close-success feedback. A stale, duplicate, `draft`, or `closed` submission MUST mutate nothing, produce contextual translated rejection feedback, and no close-success feedback. Existing admin authentication behavior MUST remain unchanged. UI redesign, bulk action, reopen, and unpublish controls remain out of scope.

#### Scenario: Published rows expose the confirmed close control

- GIVEN published raffles have active, already closed, or never-opened participation
- WHEN an authenticated admin opens the raffle index
- THEN each published row shows the confirmed overall-close control
- AND confirmation warns that active participation closes and reopening is unavailable

#### Scenario: Ineligible rows hide the close control

- GIVEN the raffle index contains `draft` and `closed` raffles
- WHEN an authenticated admin opens the raffle index
- THEN those rows show no overall-close control

#### Scenario: Confirmed close reports scoped success

- GIVEN a raffle remains committed as `published` when its confirmed close is submitted
- WHEN the authenticated admin submits that action
- THEN the raffle closes without creating a separate certification record or workflow
- AND the index shows contextual translated close-success feedback only

#### Scenario: Rejected close reports no success

- GIVEN a close submission is stale, duplicate, or targets a committed `draft` or `closed` raffle
- WHEN the system revalidates the submission
- THEN no raffle, participation-audit, or registration data is mutated
- AND the index shows contextual translated rejection feedback and no close-success feedback

#### Scenario: Guest close retains admin authentication behavior

- GIVEN an unauthenticated request submits an overall-close action
- WHEN the request reaches the protected admin surface
- THEN existing admin authentication behavior applies
- AND no raffle or participation data is mutated
