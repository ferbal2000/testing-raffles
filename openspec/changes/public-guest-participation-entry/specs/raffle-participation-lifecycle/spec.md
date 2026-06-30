# Delta for raffle-participation-lifecycle

## ADDED Requirements

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
