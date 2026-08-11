# Delta for Admin Raffle List

## MODIFIED Requirements

### Requirement: Minimal persisted raffle rows are visible

The system MUST render persisted raffles in a minimal index using existing data only. Each row MUST present the persisted raffle title as its primary visible identity and the numeric raffle ID as a separate, visible secondary reference. The title MUST render as text so authored markup is escaped rather than interpreted. Both title and ID MUST remain visible when the persisted title is the backfilled value `Sorteo #<ID>`. Each row MUST also include `status`, `starts_at`, and `ends_at`. This identity presentation MUST NOT change existing row routes, lifecycle state, action visibility, registration counts, or ordering, and MUST NOT define new business rules.

(Previously: rows required status, availability dates, and a useful identifier or timestamp without making the persisted title primary or requiring a separate numeric ID.)

#### Scenario: Persisted raffles appear with title and ID

- GIVEN one or more raffle records already exist
- WHEN an authenticated admin opens the raffle index
- THEN each row shows its persisted title as the primary identity and its numeric ID as a visible secondary reference
- AND the row retains its existing fields, routes, lifecycle state, actions, registration count, and ordering

#### Scenario: Sparse raffle values still render safely

- GIVEN a persisted raffle has missing or blank availability values allowed by the current schema
- WHEN an authenticated admin opens the raffle index
- THEN the system still shows its persisted title and numeric ID without inventing raffle data

#### Scenario: Authored markup is not interpreted

- GIVEN a persisted raffle title contains HTML or script-like text
- WHEN an authenticated admin opens the raffle index
- THEN the title is visibly rendered as text and no authored markup is interpreted

#### Scenario: Backfilled title does not replace the ID reference

- GIVEN raffle ID `42` has the persisted title `Sorteo #42`
- WHEN an authenticated admin opens the raffle index
- THEN the row shows `Sorteo #42` as the title and `42` as a separate secondary reference
