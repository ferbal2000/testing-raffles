# Delta for public-raffle-detail

## MODIFIED Requirements

### Requirement: Participation availability is read-only

The system SHALL display participation availability using `Raffle::canAcceptParticipants()`. When `canAcceptParticipants()` is true, the public detail page MUST offer a guest participation form for `name` and `email`. When `canAcceptParticipants()` is false, the page MUST show friendly unavailable copy and MUST NOT offer the form. This slice MUST NOT provide ticket-intent, number allocation, or public-auth actions.

(Previously: The page only showed read-only availability messaging and never offered entry actions.)

#### Scenario: Open participation shows guest entry action

- GIVEN a published raffle returns true from `canAcceptParticipants()`
- WHEN the public detail page is rendered
- THEN the page shows a guest participation form for `name` and `email`
- AND the page does not describe the submission as a ticket or number

#### Scenario: Closed participation shows unavailable state

- GIVEN a published raffle returns false from `canAcceptParticipants()`
- WHEN the public detail page is rendered
- THEN the page shows friendly unavailable copy
- AND no participation form is offered
