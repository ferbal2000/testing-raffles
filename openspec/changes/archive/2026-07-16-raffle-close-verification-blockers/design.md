# Design: Serialize Raffle Publication with Closure

## Technical Approach

Change only the admin publish command boundary. `RaffleController::publish(Raffle $raffle)` will execute a database transaction, re-fetch by `$raffle->getKey()`, acquire `lockForUpdate()`, and call `Raffle::publish()` on that locked instance. The route-bound instance supplies identity only and MUST NOT be saved. Domain eligibility and mutation remain in `Raffle::canPublish()` / `Raffle::publish()`; redirects, error key/message, and success flash remain unchanged.

## Architecture Decisions

| Decision | Alternatives rejected | Rationale |
|---|---|---|
| Fresh locked publish inside `DB::transaction()` | Save route-bound model; conditional update; version column/service | Matches close and participation commands, prevents stale lost updates, and preserves domain ownership without schema or architecture expansion. |
| Serialize every competing raffle mutation on the raffle row | Lock registrations or add simultaneous-session tests | Publish, overall close, participation open/close, and public participation persistence already converge on this row lock. PostgreSQL tests use `pgsql`; under normal READ COMMITTED semantics, lock acquisition followed by a fresh statement observes the preceding committed state. |
| Catch only `InvalidRaffleTransition` outside the transaction | Translate all exceptions | Laravel rolls back when the domain exception escapes the callback, then existing publish feedback is returned. Unexpected database/programming failures remain observable through normal exception handling. A row deleted after route binding causes `findOrFail()` to escape as not-found, with rollback and no success flash; no delete workflow currently exists. |
| Keep five complete deltas as the archive source | Edit stable specs now; rely on failed change | Every `MODIFIED` block repeats the full replaced requirement and retained scenarios. The deltas also carry forward published-only close, atomic audit, preservation/null, stale-participation rejection, scoped feedback, and documentation-only realtime mapping. |

## Data Flow and Invariants

```text
POST publish -> route-bound key -> transaction -> SELECT raffle FOR UPDATE
             -> lockedRaffle.publish() -> UPDATE -> commit -> success redirect
                                      \-> InvalidRaffleTransition -> rollback -> existing error redirect
```

If publish locks first, close later reads `published` and may close. If close or a participation mutation locks first, publish waits, then reads the committed lifecycle state and revalidates it; `closed` is rejected. No command may derive eligibility from the stale route-bound attributes. Rejection preserves status, availability, participation audit, and registrations; public resolution remains governed by `scopePubliclyVisible()`.

## Affected Symbols and Files

| File | Symbols / action |
|---|---|
| `app/Http/Controllers/Admin/RaffleController.php` | Modify `RaffleController::publish()` only. |
| `tests/Feature/Raffles/AdminRafflePublicationTest.php` | Extend `publishRaffle()` coverage; add deterministic stale-object snapshot/public-resolution regression. |
| `tests/Feature/Raffles/AdminRaffleCloseTest.php` | Extend `locks the current raffle row before each competing admin mutation` dataset with publish. |
| Five files under `openspec/changes/raffle-close-verification-blockers/specs/` | Archive inputs only; no design-phase edits. |

No route, UI, translation, model, schema, service, factory, public controller, or stable-spec change is indicated.

## Strict RED / GREEN / REFACTOR

**RED:** retain a draft object, use a fresh instance to publish then close it, invoke controller publish with the stale object, and prove current code reopens it. Assert existing publish error, missing `admin.raffles.publish_success`, an unchanged closed snapshot (availability, participation audit, registration count), and public detail 404. Add publish to the `DB::listen()` matrix and require raffle `SELECT ... FOR UPDATE` before raffle `UPDATE`.

**GREEN:** add the transaction/fresh lookup/lock and invoke domain `publish()` on the locked model. Preserve current successful draft, guest, and current non-draft HTTP tests plus model-level non-draft coverage.

**REFACTOR:** remove duplication only within touched test helpers if behavior and SQL-order assertions remain explicit. The stale-object test proves the lost-update precondition; the listener proves lock emission/order. Neither is simultaneous-session lock-waiting stress.

Run later with `COMPOSE_PROJECT_NAME=raffles bin/test`; database tests run sequentially.

## Risks, Rollback, and Delivery

- Future direct callers can bypass this controller protocol; mitigate by documenting the command invariant and retaining model guards.
- SQL listeners may capture unrelated statements; match raffle-table lock/update and compare first indexes.
- Archive can destructively replace requirements; verify each complete `MODIFIED` block against stable specs before archive.
- Revert controller/tests and corrective deltas together; no migration or data rollback is required.
- Tasks must forecast the 500-line guard and use stacked-to-main delivery if triggered. Proposal supersession is human SDD intent only; the old failed state remains unchanged.

## Threat Matrix and Blockers

N/A — no routing, shell, subprocess, VCS/PR automation, executable classification, or process-integration boundary changes. No blocker for tasks.
