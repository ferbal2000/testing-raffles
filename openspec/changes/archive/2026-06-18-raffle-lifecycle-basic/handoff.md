# Handoff: Raffle Lifecycle Basic

This handoff closes the simplified `raffle-lifecycle-basic` slice and records the next safe continuation point for a future session.

## Change objective

Deliver the smallest useful raffle lifecycle foundation after rolling back the oversized `raffle-lifecycle-core` attempt.

Completed scope:

- Persist a `Raffle` record with the basic lifecycle states `draft`, `published`, and `closed`.
- Enforce the normal lifecycle `draft -> published -> closed`.
- Persist `starts_at` and `ends_at` as plain data without automatic status changes.
- Keep the slice domain-first: no admin HTTP surface, no draw, no cancellation, no audit trail.
- Make the canonical `bin/test` runner use an isolated PostgreSQL test database.

## Decisions made

| Area | Decision |
|------|----------|
| Scope | Restart from a smaller slice after the prior `raffle-lifecycle-core` scope became too broad. |
| States | First slice supports only `draft`, `published`, and `closed`. |
| Initial state | New raffles MUST start in `draft`; mass-assigned non-draft initial states are forced back to `draft`. |
| Transitions | `publish()` requires a persisted `draft`; `close()` requires a persisted `published`. |
| Availability | `starts_at` and `ends_at` are stored only; `isAvailableAt()` is deferred. |
| Architecture | Use plain Laravel conventions for now, not `app/Modules/*`. |
| Test database | Tests use PostgreSQL through isolated `raffles_testing`; dangerous/dev DB names are refused. |
| Out of scope | `drawn`, `cancelled`, audit trail, reopen/rollback, admin HTTP, draw/winners, critical edit policy. |

## Files touched

Implementation and tests:

- `app/Models/Raffle.php` — raffle model, status casting, lifecycle transitions, initial draft invariant.
- `app/Enums/RaffleStatus.php` — supported lifecycle states.
- `app/Exceptions/InvalidRaffleTransition.php` — domain exception for invalid transitions.
- `database/migrations/2026_06_18_160000_create_raffles_table.php` — raffle persistence.
- `database/factories/RaffleFactory.php` — draft defaults plus persisted transition-backed states.
- `tests/Feature/Raffles/RaffleLifecycleTest.php` — lifecycle behavior and regression coverage.
- `tests/Unit/.gitkeep` — keeps PHPUnit's Unit suite path present.

Test runner remediation:

- `bin/test` — forces testing env, creates/uses `raffles_testing`, rejects unsafe DB names.
- `phpunit.xml` — uses explicit PostgreSQL testing configuration.
- `.env.example` — documents `DB_TEST_DATABASE=raffles_testing`.
- `tests/Feature/Tooling/ContainerRuntimeTest.php` — covers runner/database isolation.

OpenSpec:

- `openspec/specs/raffle-lifecycle/spec.md` — promoted source-of-truth spec.
- `openspec/changes/archive/2026-06-18-raffle-lifecycle-basic/` — archived SDD artifact trail.
- `openspec/changes/archive/2026-06-17-admin-public-identity-boundary/handoff.md` — restored handoff from the previous identity-boundary slice; still untracked until committed.

## Pending files / areas

No behavior remains pending for `raffle-lifecycle-basic`; verification is PASS.

Pending workflow items:

- Prepare reviewable commits.
- Include the restored `admin-public-identity-boundary` handoff in a documentation commit when appropriate.
- Re-run a final `git status`, diff review, and fresh review before committing.

Future product areas intentionally deferred:

- Admin HTTP CRUD/lifecycle endpoints.
- Participant entries.
- Draw execution and winners.
- `drawn` and `cancelled` states.
- Audit trail and exceptional transitions.
- Availability helper/policy such as `isAvailableAt()`.

## Commands executed

Successful verification commands after remediation:

```bash
./bin/test --filter=RaffleLifecycleTest
./bin/test tests/Feature/Tooling/ContainerRuntimeTest.php
DB_TEST_DATABASE=raffles ./bin/test
DB_TEST_DATABASE=postgres ./bin/test
./bin/test
docker compose run --rm -T app sh -lc './vendor/bin/pint --test -v tests/Feature/Raffles/RaffleLifecycleTest.php tests/Feature/Tooling/ContainerRuntimeTest.php app/Models/Raffle.php app/Enums/RaffleStatus.php app/Exceptions/InvalidRaffleTransition.php database/factories/RaffleFactory.php database/migrations/2026_06_18_160000_create_raffles_table.php'
```

Final verified results:

```text
RaffleLifecycleTest: 11 passed, 15 assertions
ContainerRuntimeTest: 6 passed, 38 assertions
Full suite: 32 passed, 136 assertions
Pint: PASS
```

## Known risks

- Coverage is not available until `xdebug` or `pcov` is installed; this is currently a suggestion, not a blocker.
- `DB_TEST_DATABASE` must remain isolated from the development database; `bin/test` now guards the common dangerous cases.
- The working tree is intentionally not committed yet, so future sessions must inspect status/diff before committing.

## Recommended next step

Prepare commits in reviewable work units:

1. `feat(raffles): add basic raffle lifecycle`
2. `test(tooling): isolate PostgreSQL test database`
3. `docs(openspec): archive basic raffle lifecycle`

Before committing, run a fresh status/diff review and include the restored identity-boundary handoff in the docs commit if desired.
