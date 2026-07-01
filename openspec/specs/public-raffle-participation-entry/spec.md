# Public Raffle Participation Entry Specification

## Purpose

Define guest participation registration from the public raffle detail page without introducing ticket, number, or authentication semantics.

## Requirements

### Requirement: Guest participation entry submission

The system MUST accept a public guest participation submission for a published raffle only when `Raffle::canAcceptParticipants()` is true. The submission MUST capture `name` and normalized `email` only. The stored record MUST represent registration/contact only and MUST NOT represent a ticket, chance, draw number, or quantity. The record MAY keep a nullable `user_id` or equivalent future link.

#### Scenario: Eligible guest submission is accepted

- GIVEN a published raffle returns true from `canAcceptParticipants()`
- WHEN a public visitor submits valid `name` and `email`
- THEN one participation entry is stored for that raffle
- AND the response confirms the registration in friendly public copy

#### Scenario: Closed raffle submission is rejected

- GIVEN a raffle returns false from `canAcceptParticipants()`
- WHEN a public visitor submits valid `name` and `email`
- THEN no participation entry is stored
- AND the response explains that participation is unavailable

### Requirement: Per-raffle email uniqueness

The system MUST allow at most one participation entry per raffle per normalized email. Duplicate attempts MUST NOT create another entry and MUST return a friendly non-technical response.

#### Scenario: Duplicate email is handled safely

- GIVEN a raffle already has a participation entry for a normalized email
- WHEN a public visitor submits the same email for that raffle
- THEN the system does not create another participation entry
- AND the response explains that the registration already exists
