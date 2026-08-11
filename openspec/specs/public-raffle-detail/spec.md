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

The system MUST render the persisted raffle title as the page's primary visible identity and the numeric raffle ID as a separate, visible secondary reference. The title MUST render as text so authored markup is escaped rather than interpreted. Title and ID MUST both remain visible when the persisted title is `Sorteo #<ID>`. The page MUST keep lifecycle status separately visible with friendly public copy backed by Spanish translation keys and MUST NOT expose raw internal enum or status values. The system MAY show `starts_at` and `ends_at` as informational metadata only. This identity presentation MUST NOT change numeric route resolution, lifecycle behavior, visibility, or participation eligibility.

(Previously: detail content required friendly lifecycle copy but did not require the persisted title as primary identity or the numeric ID as a separate reference.)

#### Scenario: Friendly status copy is shown separately

- GIVEN a published raffle detail page is rendered
- WHEN lifecycle information is displayed
- THEN visible status text uses friendly public copy
- AND it remains separate from the primary title and secondary numeric ID

#### Scenario: Availability dates remain informational

- GIVEN a published raffle has `starts_at` or `ends_at` values
- WHEN the public detail page is rendered
- THEN those values do not change lifecycle or participation messaging

#### Scenario: Authored markup is not interpreted

- GIVEN a published raffle title contains HTML or script-like text
- WHEN a public user opens its detail page
- THEN the title is visibly rendered as text and no authored markup is interpreted

#### Scenario: Backfilled identity preserves public behavior

- GIVEN published raffle ID `42` has title `Sorteo #42` and a determined participation eligibility
- WHEN a public user opens `/raffles/42`
- THEN `Sorteo #42`, a separate numeric reference `42`, and separate lifecycle status are visible
- AND route resolution, visibility, lifecycle, and participation eligibility remain unchanged

### Requirement: Participation availability is read-only

The system SHALL display participation availability using `Raffle::canAcceptParticipants()`. When `canAcceptParticipants()` is true, the public detail page MUST offer a guest participation form for `name` and `email`. When `canAcceptParticipants()` is false, the page MUST show friendly unavailable copy and MUST NOT offer the form. This slice MUST NOT provide ticket-intent, number allocation, or public-auth actions.

#### Scenario: Open participation shows guest entry action

- GIVEN a published raffle returns true from `canAcceptParticipants()`
- WHEN the public detail page is rendered
- THEN the page shows a guest participation form for `name` and `email`
- AND the page does not describe the submission as a ticket or number

#### Scenario: Closed participation shows unavailable state

- GIVEN a published raffle returns false from `canAcceptParticipants()`
- WHEN the public detail page is rendered
- THEN the page shows friendly unavailable copy
- AND no participation form is offered

### Requirement: Open participation registration count visibility

The system MUST show a friendly persisted registration count on the public raffle detail page only when `Raffle::canAcceptParticipants()` is true. The copy MUST be neutral social-proof copy, including when the persisted count is zero, and MUST NOT imply capacity, odds, eligibility, ranking, ticket quantity, or guaranteed benefit. The surface MUST remain request-response rendered and MUST NOT introduce auto-refresh or realtime runtime behavior.

#### Scenario: Open participation shows friendly count

- GIVEN a published raffle can accept participants and has stored registrations
- WHEN the public detail page is rendered
- THEN the page shows a friendly persisted registration count
- AND the copy does not imply capacity, odds, eligibility, ranking, ticket quantity, or guaranteed benefit

#### Scenario: Open participation with zero registrations shows neutral count

- GIVEN a published raffle can accept participants and has zero stored registrations
- WHEN the public detail page is rendered
- THEN the page shows a friendly persisted zero-registration count
- AND the copy does not imply odds, capacity, eligibility, ranking, ticket quantity, or guaranteed benefit

#### Scenario: Closed participation hides count

- GIVEN a published raffle cannot accept participants
- WHEN the public detail page is rendered
- THEN no public registration count is shown
- AND the page still follows existing unavailable participation behavior

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
