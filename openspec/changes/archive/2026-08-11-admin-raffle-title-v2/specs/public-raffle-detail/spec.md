# Delta for Public Raffle Detail

## MODIFIED Requirements

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
