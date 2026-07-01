# Raffle Participation Lifecycle Specification

## Purpose

Define participation eligibility separately from raffle publication status for this slice.

## Requirements

### Requirement: Canonical participation eligibility rule

The system SHALL evaluate user-entry eligibility only through `Raffle::canAcceptParticipants()`. User-entry checks MUST NOT infer eligibility directly from raw `status`, `participation_opened_at`, `participation_closed_at`, `starts_at`, or `ends_at`.

#### Scenario: Published raffle accepts participants only after manual open

- GIVEN a raffle has status `published`, a non-null `participation_opened_at`, and a null `participation_closed_at`
- WHEN `Raffle::canAcceptParticipants()` is evaluated
- THEN the result is eligible for participation

#### Scenario: Published raffle is still closed before participation opens

- GIVEN a raffle has status `published` and a null `participation_opened_at`
- WHEN `Raffle::canAcceptParticipants()` is evaluated
- THEN the result is not eligible for participation

### Requirement: Valid participation combinations are explicit

The system SHALL treat raffle `status` as the publication axis and participation timestamps as the participation axis. A `draft` raffle MUST NOT accept participants. Any raffle with non-null `participation_closed_at` MUST NOT accept participants. Any raffle with status `closed` MUST NOT accept participants. `starts_at` and `ends_at` MUST remain metadata only in this slice.

#### Scenario: Draft raffle cannot accept participants

- GIVEN a raffle has status `draft`
- WHEN `Raffle::canAcceptParticipants()` is evaluated
- THEN the result is not eligible for participation

#### Scenario: Closed participation blocks entry regardless of publication

- GIVEN a raffle has a non-null `participation_closed_at`
- WHEN `Raffle::canAcceptParticipants()` is evaluated
- THEN the result is not eligible for participation

#### Scenario: Overall closed raffle cannot accept participants

- GIVEN a raffle has status `closed`
- WHEN `Raffle::canAcceptParticipants()` is evaluated
- THEN the result is not eligible for participation

### Requirement: Submission paths revalidate participation eligibility

The system MUST re-check `Raffle::canAcceptParticipants()` for every participation submission path before persisting a public entry. Submission handling MUST NOT rely on the form being hidden in previously rendered pages.

#### Scenario: Stale open page is rejected after close

- GIVEN a raffle detail page was rendered while `canAcceptParticipants()` was true
- AND the raffle later returns false from `canAcceptParticipants()`
- WHEN a public visitor submits the form from the stale page
- THEN the system rejects the submission and stores no participation entry

#### Scenario: Eligible submission may continue

- GIVEN a raffle returns true from `canAcceptParticipants()` at submission time
- WHEN a public visitor submits a valid participation form
- THEN the system may continue participation-entry persistence checks

### Requirement: Admins may manually open and close participation

The system SHALL allow only admins to manually open participation for raffles in `published` status with null `participation_opened_at` and null `participation_closed_at`. The system SHALL allow only admins to manually close participation that is already open. Manual close MUST record `participation_closed_at`, `participation_closed_reason = admin_closed`, and `participation_closed_by_admin_id` when an authenticated admin context exists; the admin reference MAY remain null for future-safe non-admin closures. Reopening, participants, tickets, payments, funding calculations, automatic funding closure, broad lifecycle redesign, and date handling are out of scope.

#### Scenario: Admin opens participation for a published raffle

- GIVEN a published raffle has not opened or closed participation
- WHEN an authenticated admin executes the manual open action
- THEN the raffle records `participation_opened_at`
- AND participation becomes eligible

#### Scenario: Admin closes participation for an opened raffle

- GIVEN a published raffle has a non-null `participation_opened_at` and a null `participation_closed_at`
- WHEN an authenticated admin executes the manual close action
- THEN the raffle records `participation_closed_at` and `participation_closed_reason = admin_closed`
- AND `participation_closed_by_admin_id` is stored when admin identity is available
