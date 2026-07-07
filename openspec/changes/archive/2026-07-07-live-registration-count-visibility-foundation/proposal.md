# Proposal: Live Registration Count Visibility Foundation

## Intent

Link: [Approved issue #37](https://github.com/ferbal2000/testing-raffles/issues/37).

Make persisted registration counts easier to understand without introducing realtime behavior. Public visitors should see friendly social proof only while participation is open; admins should see a clear count summary on the per-raffle registration list while the existing admin index count remains unchanged.

## Scope

### In Scope
- Public raffle detail shows a friendly persisted registration count only when participation is open.
- Admin registration list shows a read-only summary count for the current raffle.
- Existing admin raffle index registration count is preserved.
- Copy avoids implying capacity, odds, eligibility, ranking, or guaranteed benefit.

### Out of Scope
- Runtime realtime: no Reverb/Echo, broadcasting, channels, listeners, event classes, dispatch wiring, JS auto-refresh, or push updates.
- Public catalog count visibility.
- Ticket, number, payment, eligibility, or winner semantics.
- Registration management, export, notification, or mutation controls.

## Capabilities

### New Capabilities
- None.

### Modified Capabilities
- `public-raffle-detail`: add an open-participation-only public count surface.
- `admin-raffle-participation-list`: add a read-only summary count.
- `realtime-update-candidate-map`: later spec/design MUST decide whether and how to update this spec for any new observable count surface.

## Approach

Keep current request/response Blade rendering. Use persisted registration counts already available through normal server-side queries and render neutral count-adjacent copy in existing views. Do not add any client refresh loop or runtime event infrastructure.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Http/Controllers/Public/RaffleController.php` | Modified | Load count data for public detail only if needed by spec/design. |
| `resources/views/public/raffles/show.blade.php` | Modified | Render friendly public count while participation is open. |
| `resources/views/admin/raffles/registrations.blade.php` | Modified | Render per-raffle summary count above/read-adjacent to the list. |
| `resources/views/admin/raffles/index.blade.php` | Preserved | Existing admin index count remains. |
| `openspec/specs/realtime-update-candidate-map/spec.md` | Decision | Spec/design decide if a delta is required. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Copy implies odds/capacity/eligibility | Medium | Keep copy neutral and social-proof-only. |
| Scope expands into catalog/realtime work | Low | Keep public detail only and request/response only. |

## Rollback Plan

Remove the added count rendering/query changes and revert the OpenSpec deltas for this slice; existing registration storage and admin index count remain unaffected.

## Dependencies

- Approved issue #37.
- Existing persisted raffle registrations.

## Success Criteria

- [ ] Public detail shows count only while participation is open.
- [ ] Admin registration list shows a summary count and keeps existing list behavior.
- [ ] Existing admin index count remains available.
- [ ] No runtime realtime infrastructure or auto-refresh behavior is introduced.
