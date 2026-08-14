# Delta for Raffle Lifecycle

## MODIFIED Requirements

### Requirement: Persisted raffle lifecycle record

The system MUST persist a raffle record with a required title, lifecycle status, and basic availability fields. The persisted title MUST contain at most 100 characters, and duplicate titles MUST be allowed. A new raffle MUST start in `draft`. The persisted lifecycle states for this slice MUST be limited to `draft`, `published`, and `closed`. Every legacy raffle without a title MUST receive the compatibility value `Sorteo #<ID>` from its numeric ID before title non-nullability is enforced, and that generated title MUST remain editable under existing edit permissions.

(Previously: persisted raffles had lifecycle and availability data but no required, bounded title or legacy-title compatibility value.)

#### Scenario: Create a draft raffle record

- GIVEN a new raffle is created with valid raffle data and a title
- WHEN the record is persisted
- THEN the raffle is stored with the required title and status `draft`
- AND the persisted record exposes `starts_at` and `ends_at` fields

#### Scenario: Unsupported lifecycle state is rejected

- GIVEN a raffle persistence attempt uses a state outside `draft`, `published`, or `closed`
- WHEN the record is validated or persisted
- THEN the system rejects that unsupported state

#### Scenario: Duplicate persisted titles are accepted

- GIVEN one raffle already has a persisted title
- WHEN another valid raffle is persisted with the same title
- THEN both raffles retain that title under distinct numeric IDs

#### Scenario: Legacy raffles receive editable compatibility titles

- GIVEN legacy raffles have no persisted title
- WHEN required title persistence is established
- THEN each raffle receives `Sorteo #<ID>` before title non-nullability is enforced
- AND the generated value remains editable under existing edit permissions
