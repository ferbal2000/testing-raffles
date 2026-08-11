# Proposal: Admin Raffle Title V2

## Intent

Complete the remaining presentation scope of [issue #73](https://github.com/ferbal2000/testing-raffles/issues/73) by making each persisted raffle title its primary visible identity. Numeric IDs remain visible secondary references so administrators and public users retain the existing operational context.

## Scope

### In Scope

- Render escaped persisted title plus numeric ID on the admin index, admin registrations, public catalog, and public detail.
- Keep public-detail lifecycle status visible as separate secondary information.
- Add focused regressions for identity, escaping, visibility, ordering, lifecycle behavior, and unchanged registration JSON.

### Out of Scope

- Title persistence, backfill, authoring, validation, or database constraints already delivered by PRs #74 and #75.
- Route, lifecycle, visibility, participation eligibility, catalog-ordering, registration JSON, or unrelated UI changes.
- Slugs, title-based routing, or a shared identity component.

## Capabilities

### New Capabilities

None.

### Modified Capabilities

- `admin-raffle-list`: Require persisted title as primary row identity while retaining the numeric ID.
- `admin-raffle-participation-list`: Require title-plus-ID identity for the current raffle without changing snapshot behavior.
- `public-raffle-catalog`: Replace synthesized identity with escaped persisted title plus numeric ID.
- `public-raffle-detail`: Present title as primary identity, ID as secondary reference, and lifecycle status separately.

## Approach

Render `Raffle::title` through escaped Blade interpolation in each existing template, with small translation adjustments where needed. Keep surface-specific markup instead of adding cross-surface abstraction. Update focused feature tests deliberately where they encode numeric-only or status-only identity.

## Affected Areas

| Area | Impact | Description |
|---|---|---|
| `resources/views/{admin,public}/**/*.blade.php` | Modified | Four existing identity surfaces |
| `lang/es/*.php` | Modified if needed | Identity labels and interpolation |
| `tests/Feature/{Raffles,Routing}/**/*Test.php` | Modified | Focused presentation regressions |

## Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| Authored title renders as markup | Low | Use escaped Blade interpolation and malicious-title tests |
| Existing behavior changes with identity assertions | Medium | Retain explicit lifecycle, visibility, ordering, route, and JSON regressions |
| Backfilled title duplicates visible ID wording | High | Always render title and ID as separate identity elements |

## Rollback Plan

Revert the template, translation, and test changes together; no data or schema rollback is required.

## Dependencies

- Approved issue #73; title persistence/backfill from PR #74; required admin authoring from PR #75.

## Success Criteria

- [ ] All four surfaces show escaped persisted title as primary identity and numeric ID as secondary reference, including `Sorteo #<ID>` titles.
- [ ] Public detail retains separate lifecycle status.
- [ ] Existing routes, lifecycle, visibility, eligibility, ordering, and registration JSON behavior remain unchanged.
