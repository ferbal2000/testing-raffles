# Proposal: Resolve Raffle Close Verification Blockers

## Intent

Issue [#52](https://github.com/ferbal2000/testing-raffles/issues/52) corrects two verified blockers. During admin publication, a stale route-bound draft can overwrite committed `closed` state. Stable admin-list requirements also contradict delivered actions and omit accepted close contracts. Passing tests covered current model state, so they missed the committed stale-state overwrite.

Admins need irreversible close behavior with existing feedback; maintainers need an archive-safe specification source without new UX.

## Scope

### In Scope
- Repair publish at its command boundary with a transaction, fresh row lookup, `lockForUpdate()`, and fresh domain revalidation before `publish()`.
- Add strict-TDD evidence for a stale-bound model after committed close, lock-before-update SQL ordering, and unchanged closed/public state. This is deterministic evidence, not true concurrency stress.
- Repair both contradictory admin-list requirements using complete `MODIFIED` blocks and carry forward accepted close contracts: published-only close, atomic active-participation audit, preservation/null rules, stale participant rejection, scoped feedback, and documentation-only realtime mapping.

### Out of Scope
- Draw flow, winners, payments, advertising/revenue automation, `ready_to_draw`, reopen/unpublish, bulk actions, or UI redesign.
- Schema/version columns, generalized lifecycle services, concurrency-test infrastructure, or Gentle AI changes.

## Capabilities

### New Capabilities
None.

### Modified Capabilities
- `admin-raffle-publication-management`: require fresh locked publish revalidation and preserve invalid-publish behavior.
- `raffle-lifecycle`: preserve irreversible close/no-republish semantics against committed state.
- `admin-raffle-list`: remove read-only contradictions and coherently specify close controls and feedback.
- `raffle-participation-lifecycle`: carry forward atomic close audit, preservation/null, and stale-submission contracts.
- `realtime-update-candidate-map`: carry forward admin-close mapping as documentation only.

## Approach and Impact

Reuse the established controller transaction/row-lock pattern while retaining domain transition ownership. Runtime impact is limited to admin publish serialization; specification deltas affect the five capabilities above.

## Risks

- Direct future publish callers could bypass the controller protocol.
- Partial `MODIFIED` blocks could delete stable scenarios during archive; copy each requirement in full.
- SQL ordering could be overstated as concurrency proof; verification must retain the disclaimer.
- Total delivery may exceed 500 review lines; tasks must apply the configured stacked-to-main guard if forecast requires it.

## Rollback

Revert the controller/test work and this change's deltas together. Stable specs remain untouched until archive, so pre-archive rollback is isolated.

## Dependencies and Relationships

- Requires approved issue #52; parent #47 remains open and approved.
- `supersedes: admin-raffle-close-action` for authoritative SDD delivery/spec intent only. This does not archive, approve, repair, or mutate the old failed Gentle AI state.

## Acceptance Outcomes

- [ ] A stale draft-bound publish after committed close is rejected with existing invalid-publish feedback and no success flash.
- [ ] Closed business state remains unchanged and publicly unresolved; SQL evidence places `FOR UPDATE` before update.
- [ ] Complete deltas archive without losing stable scenarios or accepted close contracts.
