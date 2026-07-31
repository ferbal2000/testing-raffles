# Proposal: Resilient Admin Registration List Pagination v2

Approved issue: [#61 — feat(admin): add resilient registration list pagination](https://github.com/ferbal2000/testing-raffles/issues/61)

## Intent

Bound large admin registration lists without weakening server authority, accessibility, or status workflows. Consistency and recovery outrank speed or implementation size.

## Scope

### In Scope
- Canonical server pagination: 25 rows, descending registration ID, whole-raffle counts, and over-last-page requests resolved to the last valid page.
- Real URL/history navigation with Blade full-reload/forms fallback and progressive Vue enhancement.
- Validated authoritative snapshots; non-optimistic mutations; deterministic races; one safe reconciliation GET after an uncertain POST, never a repeated POST.
- Terminal 401/419 behavior preserving confirmed data, blocking interactions, and exposing login; nested route hardening; deterministic PHP/JavaScript coverage.

### Out of Scope
- Search, filtering, export, bulk actions, configurable page size, public pagination, or runtime realtime transport.
- Reuse of v1 identities, artifacts, dependency state, or either historical stash.

## Capabilities Contract

### New Capabilities
None.

### Modified Capabilities
- `admin-raffle-participation-list`: define pagination, fallback, snapshot authority, recovery, expiry, races, accessibility, and route boundaries.
- `realtime-update-candidate-map`: clarify that progressive fetch/reconciliation remains request-response behavior and introduces no transport. **Realtime impact: Candidate**; the existing registration-list candidate remains documentation-only.

## Approach

Build a fresh server-owned snapshot shared by Blade hydration, page GETs, mutation success, and stale conflicts. Progressively enhance validated markup with an explicit Vue state machine that commits confirmed snapshots, updates history only after successful canonical navigation, defers conflicts deterministically, and enters GET-only safe mode after failed reconciliation. Preserve traditional links/forms without JavaScript.

## Affected Areas

| Area | Impact |
|---|---|
| Server HTTP surface | Snapshots, negotiation, mutations, nested constraints |
| Blade/Vue/Vite/copy | Fallback and progressive state machine |
| PHP/JavaScript tests | Deterministic contract coverage |
| Stable specs | Two capability deltas |

## Risks

| Risk | Mitigation |
|---|---|
| State races or duplicate mutation | Explicit transitions, abort/late-response guards, never retry POST |
| Invalid payload or expired session corrupts UI | Validate before commit; retain data; terminal states |
| Likely exceeds 400 changed lines | Plan reviewable work units and resolve ask-on-risk delivery before apply |

## Rollback

Revert to the unpaginated Blade list and synchronous forms; remove frontend/tooling additions and deltas together. No data migration is required.

## Dependencies

- Approved issue #61; completed exploration and product decisions.
- Laravel/Vite stack; select Vue/Vitest versions fresh in design/apply.

## Success Criteria

- [ ] Navigation and status actions work with and without JavaScript using authoritative state.
- [ ] Uncertain, stale, expired, malformed, and racing requests resolve deterministically without repeating POST or discarding confirmed data.
- [ ] Nested routes and deterministic PHP/JavaScript coverage prove the contract; no runtime realtime transport exists.
