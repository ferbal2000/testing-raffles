# Exploration: Raffle Close Verification Blockers

Issue: [#52](https://github.com/ferbal2000/testing-raffles/issues/52)

## Recommendation

Repair publication at the same command boundary used by the other competing raffle mutations: open a database transaction, re-fetch the route-bound raffle by key with `lockForUpdate()`, and invoke `publish()` only on that fresh locked model. In the same corrective change, replace the contradictory read-only clauses in the stable admin-list capability through complete `MODIFIED` requirement blocks and carry forward the still-unarchived close contracts needed to make the stable specifications match merged runtime.

This is a new corrective lineage. It should declare that it **supersedes** `admin-raffle-close-action` as the authoritative delivery change, but only after it re-states the accepted close contracts. Supersession does not archive, repair, approve, or otherwise change the terminal failed state of the old change.

## Current State

- The isolated branch `fix/raffle-close-verification-blockers` is clean at `7cc840ba837c353bfd73a8ff64307a701adba18a`, equal to `main` and `origin/main`.
- `RaffleController::close()`, participation open/close, and public participation persistence each use `DB::transaction()`, a fresh raffle query, `lockForUpdate()`, and domain revalidation.
- `RaffleController::publish()` instead calls `publish()` on the route-bound model without a transaction, fresh query, or row lock.
- `Raffle::publish()` correctly rejects a currently loaded non-draft model, but it cannot detect that its in-memory draft status has become stale. Its save writes `published` and can overwrite a committed `closed` value.
- Stable `raffle-lifecycle` already says a closed raffle cannot be republished. Stable `admin-raffle-publication-management` requires non-draft submissions to leave status unchanged.
- The merged close runtime and UI are present, but the failed `admin-raffle-close-action` deltas were never archived into stable specs.
- Stable `admin-raffle-list` is internally contradictory: the minimal-row and narrow-scope requirements still require read-only behavior and prohibit lifecycle actions, while a later stable requirement already requires publish and participation actions. The merged overall-close action makes that contradiction impossible to ignore.
- No tests were run during exploration, as required.

## Concrete Stale-Publish Interleaving

1. Request A is route-bound while raffle `#42` is `draft`; its PHP model retains `status = draft`.
2. Request A pauses before `RaffleController::publish()` calls the model transition.
3. Request B publishes `#42`, then an authenticated admin closes it through the locked close command. PostgreSQL commits `status = closed` and any active-participation closure audit.
4. Request A resumes. Its stale model still passes `canPublish()` because that check reads the old in-memory `draft` value.
5. `forceFill(status = published)->save()` updates the row by primary key without checking the current status, overwriting `closed` with `published`.
6. `scopePubliclyVisible()` exposes the raffle again. Participation audit fields may still indicate closure, but the irreversible overall lifecycle result has been lost.

The smallest consistent correction serializes publish with close and participation mutations on the same raffle row. If close commits first, publish acquires the lock afterward, reads `closed`, and rejects. If publish locks first, it commits `published`; close then reads `published` and may validly close it. Both orderings preserve the lifecycle graph.

## Affected Areas

- `app/Http/Controllers/Admin/RaffleController.php` — wrap publish in the established fresh locked transaction boundary.
- `tests/Feature/Raffles/AdminRafflePublicationTest.php` — prove endpoint behavior for a stale draft-bound model after committed close.
- `tests/Feature/Raffles/AdminRaffleCloseTest.php` — extend deterministic lock-before-update evidence to publish, or move the shared mutation matrix if test ownership is clearer.
- `tests/Feature/Raffles/RaffleLifecycleTest.php` — likely unchanged; existing current-model non-draft rejection remains valid model-level evidence.
- `openspec/changes/raffle-close-verification-blockers/specs/raffle-lifecycle/spec.md` — later carry forward the irreversible close/no-republish contract with fresh-state semantics.
- `openspec/changes/raffle-close-verification-blockers/specs/admin-raffle-publication-management/spec.md` — later require fresh locked revalidation and unchanged closed state.
- `openspec/changes/raffle-close-verification-blockers/specs/admin-raffle-list/spec.md` — later remove contradictory read-only clauses and encode the close control coherently.
- `openspec/changes/raffle-close-verification-blockers/specs/raffle-participation-lifecycle/spec.md` — later carry forward the accepted atomic audit policy because the old delta was not archived.
- `openspec/changes/raffle-close-verification-blockers/specs/realtime-update-candidate-map/spec.md` — later carry forward the delivered admin-close wording without implying runtime realtime behavior.

No route, model, schema, factory, Blade, translation, public controller, or Gentle AI change is currently indicated by the stale-publish repair itself.

## Strict-TDD Regression Surface

### Required deterministic evidence

1. **Stale draft-bound publish after close is rejected**
   - Keep a draft `Raffle` instance as the route-bound stale object.
   - Through a fresh instance, publish and then close the row.
   - Invoke the controller publish command with the stale object.
   - Assert publish rejection feedback, no publish-success flash, a fully unchanged closed business snapshot, and public non-resolution.
   - This test fails against merged runtime because the stale object reopens the row.

2. **Publish locks before update**
   - Extend the existing `DB::listen()` mutation matrix with the publish endpoint.
   - Assert a raffle `FOR UPDATE` query appears before the raffle update.
   - This is deterministic SQL-order evidence, not concurrency stress.

3. **Current non-draft HTTP behavior remains unchanged**
   - Preserve the existing published submission rejection and add/triangulate a closed endpoint submission if the stale-interleaving scenario does not already cover its response contract.

### Evidence limits

The stale-object scenario deterministically reproduces the lost-update precondition and proves fresh revalidation. The SQL-listener test proves lock emission and ordering. Neither test proves lock waiting under simultaneous database sessions.

A true concurrency stress test would require independent processes or database connections, synchronization barriers, transaction timing, and timeout control. The repository has no such harness. Adding one would increase flakiness and scope; it is not required for this first corrective slice. It may be considered later as infrastructure hardening, but MUST NOT be represented by the deterministic tests above.

## Delta-Spec Treatment

Do not edit `openspec/specs/admin-raffle-list/spec.md` during exploration.

At spec phase, use complete `MODIFIED Requirements` blocks for both stable requirements that still assert read-only behavior:

1. `Minimal persisted raffle rows are visible` — preserve the field and sparse-value scenarios, but replace the slice-local prohibition with wording that the minimal-row baseline does not itself define controls and that dedicated action requirements govern lifecycle commands.
2. `Explicit empty state without broader admin restructuring` — preserve empty-state and navigation boundaries, but replace the “read-only listing behavior” scenario wording with a narrow-index statement that does not prohibit already-delivered row actions.

Then specify the overall-close action explicitly. A focused `ADDED` close requirement is less error-prone than replacing the very large existing combined create/edit/publish/participation requirement, provided verification checks that the modified baseline blocks and the added close block are mutually coherent.

Because `admin-raffle-close-action` failed before archive, this corrective change must also carry forward the accepted close semantics required for stable-spec truth: published-only admin close, atomic active-participation audit, preservation/null rules, stale participant rejection, scoped close UI feedback, and documentation-only realtime mapping. It must not assume those old deltas are already stable.

## Approaches and Tradeoffs

1. **Fresh row lock in the publish controller — recommended**
   - Pros: Matches all existing competing command paths; smallest runtime diff; serializes publish versus close; keeps domain transition ownership in `Raffle`.
   - Cons: Direct callers can still misuse a stale model outside the command boundary.
   - Effort: Low.

2. **Conditional atomic update in `Raffle::publish()`**
   - Pros: Protects every caller by updating only where persisted status is `draft`; can detect zero affected rows.
   - Cons: Introduces a different concurrency pattern from close/participation, complicates model state/events, and still needs careful transaction semantics.
   - Effort: Medium.

3. **Optimistic version column or generalized lifecycle service**
   - Pros: Broad lost-update protection across future mutations.
   - Cons: Migration and architecture expansion are disproportionate to two known blockers.
   - Effort: High.

The controller lock is the narrowest correct repair. A future generalized command/service may be justified only when more lifecycle mutations exist.

## Relationship Recommendation

Declare `supersedes: admin-raffle-close-action`, with an explicit note that supersession applies to the authoritative SDD delivery lineage and delta-spec intent, not to Git history or Gentle AI state. This is preferable to `amends` because the old change cannot proceed to remediation/archive and its deltas are absent from stable specs.

Use `amends` only if project tooling defines it as a standalone replacement lineage that can archive without the amended change; no evidence of that convention exists in this repository. A neutral `remediates` or `related_to` relationship is historically accurate but too weak if the new change is expected to become the sole archiveable specification source. If the new proposal does not carry forward all accepted old deltas, use `remediates` instead and do not claim full supersession.

The old failed change remains failed/blocked until an explicit later disposition. The new change MUST NOT claim that declaring a relationship archives, abandons, or repairs it automatically.

## Scope and Non-Goals

### In scope

- Fresh locked publish revalidation against committed lifecycle state.
- Deterministic stale-bound regression and lock-order evidence.
- Archive-safe correction of the admin-list normative contradiction.
- Carry-forward of only the close contracts necessary for stable-spec coherence.

### Out of scope

- Draw execution, winners, payments, revenue automation, or `ready_to_draw`/`drawn` persistence.
- Reopen, unpublish, bulk lifecycle commands, broader admin actions, or UI redesign.
- New schema/version columns, generalized workflow services, or concurrency-test infrastructure.
- Gentle AI source, binary, lifecycle, review lineage, or terminal-state changes.
- Editing stable specs, implementing code, running tests, delivery, issue/PR creation, or archive during exploration.

## Risks

- A controller-only repair assumes all production publish entry points continue through this command boundary; future direct callers need the same protocol.
- Tests that invoke the controller directly must configure the admin guard/feedback accurately and avoid accidentally testing only model behavior.
- SQL-listener evidence can be mislabeled as concurrency proof; verification must preserve the distinction.
- Partial `MODIFIED` blocks would delete stable scenarios at archive. Each modified requirement must be copied in full.
- Supersession language can overstate lifecycle effects unless the proposal explicitly limits it and carries forward all accepted old contracts.
- The complete corrective OpenSpec audit trail may push the review over budget even though the runtime patch is small.

## Preliminary Review Workload

| Work area | Preliminary authored change |
|---|---:|
| Controller repair | 8-15 lines |
| Focused regression and lock evidence | 45-90 lines |
| Delta specs carried forward and reconciled | 150-240 lines |
| Remaining proposal/design/tasks/verify/archive artifacts | 140-240 lines |
| **Likely total** | **343-585 lines** |

A single PR below the configured 500-line budget is plausible only at the low end and is not reliable once the full hybrid SDD audit trail is included. Preliminary budget risk is **Medium**. The later tasks phase should make the final forecast; with `auto-chain`/forced chaining, it should plan stacked-to-main review units if the concrete forecast exceeds 500 lines. This is not a final tasks or chain decision.

## Ready for Proposal

Yes: the repair, specification correction, test boundary, and non-goals are clear. Corrective issue [#52](https://github.com/ferbal2000/testing-raffles/issues/52) is maintainer-approved, persisted in Engram, and referenced by this OpenSpec context.
