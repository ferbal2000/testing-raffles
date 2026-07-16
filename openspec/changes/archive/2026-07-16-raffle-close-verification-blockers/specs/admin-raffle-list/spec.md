# Delta for Admin Raffle List

## ADDED Requirements

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

## MODIFIED Requirements

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
