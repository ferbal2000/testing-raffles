# Public Raffle Catalog Specification

## Purpose

Define the public home page as a discovery catalog for published raffles only.

## Requirements

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

### Requirement: Catalog entries link to numeric raffle detail pages

Each visible catalog entry MUST link to the existing numeric public detail route at `/raffles/{id}`. This slice MUST NOT add search, filters, pagination, slugs, or participation CTAs on the home page.

#### Scenario: Catalog entry opens the existing detail route

- GIVEN a published raffle appears in the catalog
- WHEN a public user selects that entry
- THEN the destination is `/raffles/{id}` for that raffle

#### Scenario: Catalog avoids extra discovery controls

- GIVEN the public catalog is rendered
- WHEN the page content is evaluated
- THEN no search, filters, pagination, slug links, or participation CTA are present

### Requirement: Catalog ordering and empty state are explicit

The system MUST order visible catalog entries by raffle `id` descending for this slice. The system MUST describe or document this as a temporary newest-created-record fallback, not true publication-date ordering. When no visible raffles exist, the system MUST show an explicit empty state.

#### Scenario: Highest raffle ID appears first

- GIVEN multiple published raffles are visible
- WHEN the catalog is rendered
- THEN the raffle with the highest `id` appears before lower `id` values

#### Scenario: Empty catalog is communicated

- GIVEN no publicly visible raffles exist
- WHEN a public user opens `/`
- THEN the page shows an explicit empty state instead of blank results
