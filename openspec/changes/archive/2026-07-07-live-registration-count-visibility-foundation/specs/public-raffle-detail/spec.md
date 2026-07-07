# Delta for Public Raffle Detail

## ADDED Requirements

### Requirement: Open participation registration count visibility

The system MUST show a friendly persisted registration count on the public raffle detail page only when `Raffle::canAcceptParticipants()` is true. The copy MUST be neutral social-proof copy, including when the persisted count is zero, and MUST NOT imply capacity, odds, eligibility, ranking, ticket quantity, or guaranteed benefit. The surface MUST remain request-response rendered and MUST NOT introduce auto-refresh or realtime runtime behavior.

#### Scenario: Open participation shows friendly count

- GIVEN a published raffle can accept participants and has stored registrations
- WHEN the public detail page is rendered
- THEN the page shows a friendly persisted registration count
- AND the copy does not imply capacity, odds, eligibility, ranking, ticket quantity, or guaranteed benefit

#### Scenario: Open participation with zero registrations shows neutral count

- GIVEN a published raffle can accept participants and has zero stored registrations
- WHEN the public detail page is rendered
- THEN the page shows a friendly persisted zero-registration count
- AND the copy does not imply odds, capacity, eligibility, ranking, ticket quantity, or guaranteed benefit

#### Scenario: Closed participation hides count

- GIVEN a published raffle cannot accept participants
- WHEN the public detail page is rendered
- THEN no public registration count is shown
- AND the page still follows existing unavailable participation behavior
