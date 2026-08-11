# Delta for Public Raffle Catalog

## MODIFIED Requirements

### Requirement: Published raffle catalog visibility

The system MUST render `GET /` as a public raffle catalog that lists only raffles that are publicly visible for this slice. Each visible entry MUST present its persisted title as the primary identity and its numeric ID as a separate, visible secondary reference. The title MUST render as text so authored markup is escaped rather than interpreted. Both title and ID MUST remain visible when the persisted title is `Sorteo #<ID>`. The catalog MUST exclude `draft` and `closed` raffles. This identity presentation MUST NOT change numeric detail routes, visibility rules, or descending-ID catalog ordering.

(Previously: the catalog defined published-only visibility without requiring persisted title and numeric ID presentation.)

#### Scenario: Published raffles are listed with title and ID

- GIVEN published raffles exist
- WHEN a public user opens `/`
- THEN each visible entry shows its persisted title as primary identity and numeric ID separately

#### Scenario: Non-visible raffles stay hidden

- GIVEN raffles exist in `draft` or `closed` status
- WHEN a public user opens `/`
- THEN those raffles are not shown in the catalog

#### Scenario: Authored markup is not interpreted

- GIVEN a visible raffle title contains HTML or script-like text
- WHEN a public user opens `/`
- THEN the title is visibly rendered as text and no authored markup is interpreted

#### Scenario: Backfilled identity preserves routing and ordering

- GIVEN raffle ID `42` has title `Sorteo #42` among multiple visible raffles
- WHEN the catalog is rendered
- THEN `Sorteo #42` and a separate numeric reference `42` are both visible
- AND its numeric detail link and descending-ID ordering remain unchanged
