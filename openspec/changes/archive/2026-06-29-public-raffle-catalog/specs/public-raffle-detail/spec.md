# Delta for Public Raffle Detail

## MODIFIED Requirements

### Requirement: Discovery and alternate routes stay out of scope

The system MUST keep direct numeric detail access as the public raffle detail contract. The public home catalog MAY link to `/raffles/{id}` for discoverable published raffles. The system MUST NOT add slug-only public detail routing. Future optional slug decoration MAY be added only after the numeric ID segment.
(Previously: home-page raffle discovery was fully out of scope for this capability.)

#### Scenario: Home page catalog links use numeric detail routes

- GIVEN a public user opens the public home page
- WHEN published raffles are shown in the catalog
- THEN each raffle link targets `/raffles/{id}` for that raffle

#### Scenario: Slug route is unsupported in this slice

- GIVEN a public user attempts a slug-style raffle detail path
- WHEN the request is evaluated
- THEN this slice does not define a matching public raffle detail route
- AND a path like `/raffles/not-a-number` returns `404`
