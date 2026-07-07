# Tasks: Live Registration Count Visibility Foundation

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 180-260 |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR: tests, minimal count wiring, copy, and docs/archive verification together. |
| Delivery strategy | ask-always / ask before splitting |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Add persisted count visibility on existing public/admin pages. | PR 1 | Keep tests with controller/view/translation changes; no runtime realtime. |
| 2 | Verify docs/archive boundaries for realtime candidate map. | PR 1 | Apply preserves active delta; archive later merges stable source spec. |

## Phase 1: RED Tests

- [x] 1.1 Add failing assertions in `tests/Feature/Raffles/PublicRaffleDetailTest.php` for open non-zero count, open zero-count neutral copy, and closed count hidden.
- [x] 1.2 Add failing assertions in `tests/Feature/Raffles/AdminRaffleRegistrationsTest.php` for non-zero and zero summary counts while newest-first list and empty state remain unchanged.
- [x] 1.3 Add/keep regression coverage in `tests/Feature/Raffles/AdminRaffleIndexTest.php` only if implementation touches `resources/views/admin/raffles/index.blade.php`.

## Phase 2: GREEN Implementation

- [x] 2.1 Update `app/Http/Controllers/Public/RaffleController.php` to load `registrations_count` for public detail only; do not alter catalog queries.
- [x] 2.2 Update `resources/views/public/raffles/show.blade.php` to render count only inside open participation UI, including explicit zero-count copy.
- [x] 2.3 Add public count translations in `lang/es/public-raffles.php` with neutral wording that avoids odds, capacity, eligibility, ranking, ticket quantity, or guaranteed benefit.
- [x] 2.4 Update `app/Http/Controllers/Admin/RaffleController.php` to `loadCount('registrations')` for `registrations()` without changing eager-loaded registration order/select.
- [x] 2.5 Update `resources/views/admin/raffles/registrations.blade.php` and `lang/es/admin-raffles.php` with a read-only current raffle summary count, including zero.

## Phase 3: REFACTOR / Verification

- [x] 3.1 Refactor duplicated count/copy structure only after tests pass; keep behavior request-response rendered.
- [x] 3.2 Verify no realtime runtime was added by file diff/static review: no Echo/Reverb, broadcasting, events, listeners, channels, polling, websocket, auto-refresh, or JS refresh loops.
- [x] 3.3 Confirm `resources/views/admin/raffles/index.blade.php` behavior and existing count/entry point are preserved.

## Phase 4: OpenSpec Apply Boundary

- [x] 4.1 During apply, keep `openspec/changes/2026-07-07-live-registration-count-visibility-foundation/specs/realtime-update-candidate-map/spec.md` as documentation-only active delta.
- [x] 4.2 Do not edit `openspec/specs/realtime-update-candidate-map/spec.md` during apply.

## Phase 5: Verify / Archive Boundary

- [x] 5.1 In verify, record test results and explicit no-realtime-runtime evidence in `verify-report.md`.
- [x] 5.2 During archive only, merge the realtime candidate-map delta into `openspec/specs/realtime-update-candidate-map/spec.md` and archive this change folder.
