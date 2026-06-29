# Public Raffle Detail Specification

## Purpose

Define the first direct public raffle detail page for published raffles only.

## Requirements

### Requirement: Published raffle detail route

The system MUST expose a public-host `GET /raffles/{id}` detail route for raffles in `published` status only. Public resolution MUST exclude non-published raffles at query, routing, or model-binding time rather than loading them and hiding them in the view. The route contract is ID-first; a future-compatible `GET /raffles/{id}/{slug?}` shape MAY be added later, but the leading segment MUST remain numeric.

#### Scenario: Published raffle detail resolves

- GIVEN a raffle has status `published`
- WHEN a public user requests `/raffles/{id}` on the public host
- THEN the raffle detail page is returned

#### Scenario: Non-published raffle detail is not found

- GIVEN a raffle has status `draft` or `closed`
- WHEN a public user requests `/raffles/{id}` on the public host
- THEN the system returns `404`

### Requirement: Friendly public raffle detail content

The system MUST render core raffle information with friendly public copy backed by Spanish translation keys. The page MUST NOT expose raw internal enum or status values. The system MAY show `starts_at` and `ends_at` as informational metadata only.

#### Scenario: Friendly status copy is shown

- GIVEN a published raffle detail page is rendered
- WHEN lifecycle information is displayed
- THEN visible status text uses friendly public copy

#### Scenario: Availability dates remain informational

- GIVEN a published raffle has `starts_at` or `ends_at` values
- WHEN the public detail page is rendered
- THEN those values do not change lifecycle or participation messaging

### Requirement: Participation availability is read-only

The system SHALL display participation availability using `Raffle::canAcceptParticipants()`. This slice MUST NOT provide registration, ticket-intent, or participant-entry actions.

#### Scenario: Open participation is communicated without entry action

- GIVEN a published raffle returns true from `canAcceptParticipants()`
- WHEN the public detail page is rendered
- THEN the page shows friendly availability copy
- AND no registration or ticket action is offered

#### Scenario: Closed participation is communicated without date inference

- GIVEN a published raffle returns false from `canAcceptParticipants()`
- WHEN the public detail page is rendered
- THEN the page shows friendly unavailable copy

### Requirement: Discovery and alternate routes stay out of scope

The system MUST keep direct numeric detail access as the public raffle detail contract. The public home catalog MAY link to `/raffles/{id}` for discoverable published raffles. The system MUST NOT add slug-only public detail routing. Future optional slug decoration MAY be added only after the numeric ID segment.

#### Scenario: Home page catalog links use numeric detail routes

- GIVEN a public user opens the public home page
- WHEN published raffles are shown in the catalog
- THEN each raffle link targets `/raffles/{id}` for that raffle

#### Scenario: Slug route is unsupported in this slice

- GIVEN a public user attempts a slug-style raffle detail path
- WHEN the request is evaluated
- THEN this slice does not define a matching public raffle detail route
- AND a path like `/raffles/not-a-number` returns `404`
