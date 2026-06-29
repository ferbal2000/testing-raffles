# Delta for raffle-lifecycle

## MODIFIED Requirements

### Requirement: Published status governs publication only

The system SHALL treat raffle `published` status as publication and public visibility state, not as a standalone permission to accept participants. On the public raffle detail route, only `published` raffles SHALL resolve. Raffles in `draft` or `closed` MUST be excluded during query, routing, or model binding rather than loaded and then visually hidden. The public route remains ID-first, and any future optional slug suffix MUST preserve the same published-only resolution contract.
(Previously: Published status defined publication visibility separately from participation, without public detail route resolution rules.)

#### Scenario: Published raffle is visible before participation opens

- GIVEN a raffle has status `published` and a null `participation_opened_at`
- WHEN lifecycle behavior is evaluated
- THEN the raffle remains published
- AND it MUST NOT accept participants yet

#### Scenario: Closed raffle cannot accept participants

- GIVEN a raffle has status `closed`
- WHEN lifecycle behavior is evaluated
- THEN the raffle remains closed
- AND it MUST NOT accept participants

#### Scenario: Published raffle resolves on the public detail route

- GIVEN a raffle has status `published`
- WHEN a public user requests `/raffles/{id}`
- THEN the public detail route resolves that raffle

#### Scenario: Non-published raffle is filtered before rendering

- GIVEN a raffle has status `draft` or `closed`
- WHEN a public user requests `/raffles/{id}`
- THEN the system returns `404`
- AND no hidden raffle detail view is rendered
