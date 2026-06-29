# Proposal: Public Raffle Detail Status

## Intent

Expose a first public raffle detail page so users can verify a raffle's current state without enabling discovery or entry flows. This slice closes the gap between admin-managed raffle lifecycle data and the public site while keeping hidden raffles non-discoverable.

## Scope

### In Scope
- Add a public-host route at `GET /raffles/{id}` using the current numeric raffle identifier.
- Reserve the future-compatible ID-first shape `GET /raffles/{id}/{slug?}` without implementing the optional slug segment in this slice.
- Render a read-only Blade page for a single raffle with friendly status/availability copy.
- Return `404` for any raffle not in `published` status.
- Return `404` for non-numeric raffle paths such as `/raffles/not-a-number`.

### Out of Scope
- Public catalog, home-page links, slug-only routing, optional slug decoration in rendered links, registration, ticket intent, and participant entry.
- Changing lifecycle rules or making `starts_at` / `ends_at` participation gates.

## Capabilities

### New Capabilities
- `public-raffle-detail`: Public users can open one published raffle detail page and see lifecycle plus participation availability messaging.

### Modified Capabilities
- `raffle-lifecycle`: Clarify that this first public slice exposes only `published` raffles and treats non-published states as not found on the public detail route.

## Approach

Use a narrow public route/controller/view flow on the public host. Reuse `Raffle::status` as the visibility rule, `Raffle::canAcceptParticipants()` as the only entry-eligibility signal, and treat `starts_at` / `ends_at` as display-only metadata. Keep the URL strategy ID-first now (`/raffles/{id}`), preserve compatibility with a later optional-slug suffix (`/raffles/{id}/{slug?}`), and reject non-numeric leading segments at the route boundary. Map internal lifecycle values to Spanish translation-backed friendly copy instead of exposing raw enum labels.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `routes/web.php` | Modified | Add public detail route under the public host boundary |
| `app/Models/Raffle.php` | Modified | Reuse existing visibility/eligibility rules; add no new lifecycle axis |
| `resources/views/public/` | New/Modified | Add raffle detail Blade template with friendly status messaging |
| `lang/es/*.php` | Modified | Add public-facing copy for lifecycle and availability text |
| `openspec/specs/raffle-lifecycle/spec.md` | Modified | Capture published-only public visibility rule |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Numeric IDs are not user-friendly | Med | Keep as first-slice constraint; defer slugs explicitly |
| Visibility leaks hidden raffles | Med | Specify `404` behavior and test route binding/queries first |
| Friendly copy drifts from domain rules | Low | Derive messages only from `status` and `canAcceptParticipants()` |

## Rollback Plan

Remove the public detail route, view, and translations; restore the public surface to `GET /` only; drop the related spec delta if implementation is abandoned.

## Dependencies

- Existing `raffle-lifecycle` and `raffle-participation-lifecycle` rules remain the source of truth.

## Success Criteria

- [ ] A published raffle is reachable at `/raffles/{id}` on the public host and shows friendly lifecycle/availability copy.
- [ ] Draft and closed raffles return `404` from the public detail route.
- [ ] Non-numeric raffle paths return `404` instead of `500`.
- [ ] The public home page remains non-discovery only for this slice.
- [ ] Participation messaging is derived from `canAcceptParticipants()` and not from raw dates.
