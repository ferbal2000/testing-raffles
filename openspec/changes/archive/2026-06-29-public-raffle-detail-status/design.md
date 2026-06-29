# Design: Public Raffle Detail Status

## Technical Approach

Add a public-host `GET /raffles/{id}` endpoint that renders a read-only Blade page for published raffles only. The controller will resolve the record through a visibility-scoped query, then pass the raffle to a dedicated public view that maps lifecycle and participation state to Spanish translation keys. This follows the proposal and the `public-raffle-detail` / `raffle-lifecycle` delta specs while preserving `status` as the visibility axis and `canAcceptParticipants()` as the participation axis. The route contract stays ID-first now and is intentionally compatible with a later `GET /raffles/{id}/{slug?}` extension.

## Architecture Decisions

| Decision | Options | Choice | Rationale |
|---|---|---|---|
| Visibility enforcement | Implicit global route binding; controller query; load-then-hide | Controller query backed by a reusable `publiclyVisible()` raffle scope | Avoids leaking hidden raffles, keeps admin binding unchanged, and gives a reusable visibility rule for later public slices. |
| Public UI composition | Reuse `home.php`; new translation file; raw enum labels | New `lang/es/public-raffles.php` keys | Keeps public raffle copy isolated, testable, and Spanish-facing without exposing internal enum values. |
| Domain reuse | New lifecycle flags; use existing model methods only; add lightweight query helper | Reuse `canAcceptParticipants()` and add only a visibility scope/helper if needed | Preserves current domain decisions and limits model changes to query reuse, not new state logic. |
| Public URL contract | Slug-only detail; opaque UUID; ID-first with optional slug suffix | ID-first route now, future-compatible with `/{slug?}` decoration later | Keeps current direct access simple, preserves the approved public numeric ID contract, and prevents slug-shaped paths from reaching the controller. |

## Data Flow

Public request stays inside the public host boundary and rejects hidden raffles before rendering.

    Browser ──→ routes/web.php (`whereNumber('raffle')`) ──→ Public\RaffleController@show
                                  │
                                  └──→ Raffle::query()->publiclyVisible()->findOrFail($id)
                                                   │
                                                   └──→ resources/views/public/raffles/show.blade.php
                                                                 │
                                                                 └──→ lang/es/public-raffles.php

The view derives two public messages:
- lifecycle copy from the visible raffle status (`published` only in this slice)
- availability copy from `canAcceptParticipants()`

## File Changes

| File | Action | Description |
|---|---|---|
| `routes/web.php` | Modify | Add the public-host detail route, constrain the parameter to digits, and keep admin/public resolution isolated. |
| `app/Http/Controllers/Public/RaffleController.php` | Create | Resolve only published raffles and return the public detail view. |
| `app/Models/Raffle.php` | Modify | Add a small reusable visibility scope/helper for published-only public access; keep `canAcceptParticipants()` unchanged. |
| `resources/views/public/raffles/show.blade.php` | Create | Render friendly lifecycle text, participation availability, and optional date metadata without exposing the numeric ID in the page body. |
| `lang/es/public-raffles.php` | Create | Store Spanish public copy, labels, placeholders, and availability/status messages. |
| `tests/Feature/Raffles/PublicRaffleDetailTest.php` | Create | Cover host routing, published-only visibility, and read-only messaging. |
| `tests/Feature/Routing/PublicRaffleDetailTranslationsTest.php` | Create | Prove the detail page reads translation keys instead of hard-coded copy. |
| `tests/Feature/Routing/HomeTranslationsTest.php` | Modify | Prove the public home remains non-discovery only for this slice. |
| `tests/Feature/Raffles/RaffleLifecycleTest.php` | Modify | Add explicit lifecycle assertions that published and closed states remain unchanged while participation availability varies. |

## Interfaces / Contracts

```php
// intended query contract
Raffle::query()->publiclyVisible()->findOrFail($id);

// current route
GET /raffles/{id}

// future-compatible route shape (not implemented in this slice)
GET /raffles/{id}/{slug?}
```

Public detail remains numeric-id based for now. The first segment MUST stay numeric, non-published records MUST fail with `404`, slug-only paths such as `/raffles/not-a-number` MUST NOT reach the controller, and the page MUST NOT expose registration, ticket, or participant-entry actions.

## Testing Strategy

| Layer | What to Test | Approach |
|---|---|---|
| Unit | Published-only visibility helper/scope | Add focused Pest coverage if a model scope/helper is introduced. |
| Integration | Public host detail resolution and `404` for `draft` / `closed` / non-numeric paths | Write Laravel HTTP tests first using `withServerVariables()` and raffle factory states. |
| Integration | Friendly Spanish copy and boolean availability messaging | Assert translated text for visible status, `canAcceptParticipants()` true/false branches, and no raw enum leakage. |
| Integration | Home remains non-discovery only | Assert the public home renders without raffle catalog/detail links. |
| E2E | N/A | No E2E layer is currently configured in `openspec/config.yaml`. |

## Migration / Rollout

No migration required. Rollout is additive: ship the route, controller, view, and translations together.

## Resolved Follow-up

- Numeric IDs remain URL-only for this slice; the page body does not render them.
- If optional slug decoration is added later, published-only visibility remains the shared resolver contract for both `/raffles/{id}` and `/raffles/{id}/{slug?}`.

## Non-Goals

- No catalog or home-page raffle links.
- No slug-only routing.
- No optional slug decoration rendered or accepted yet.
- No registration, ticket intent, or participant entry UI/actions.
- No lifecycle changes based on `starts_at` / `ends_at`.
