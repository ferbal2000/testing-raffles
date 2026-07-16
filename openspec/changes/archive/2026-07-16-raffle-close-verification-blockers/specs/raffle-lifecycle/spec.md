# Delta for Raffle Lifecycle

## MODIFIED Requirements

### Requirement: Publish from draft only

The system MUST allow a raffle to transition from `draft` to `published` only after the command revalidates the fresh committed lifecycle state. It MUST NOT publish from `published` or `closed`, and closure MUST remain irreversible: no stale model or later publish submission may reopen a committed `closed` raffle.

(Previously: rejected transitions were defined against persisted state without explicitly requiring fresh committed-state revalidation.)

#### Scenario: Publish a draft raffle

- GIVEN a persisted raffle whose fresh committed status is `draft`
- WHEN the publish action is executed
- THEN the raffle status becomes `published`

#### Scenario: Closed raffle cannot be republished

- GIVEN a persisted raffle whose committed status is `closed`
- WHEN the publish action is executed
- THEN the system rejects the transition
- AND the raffle remains `closed` and unavailable through published-only public lookup

#### Scenario: Stale draft state does not override committed closure

- GIVEN a caller holds an earlier `draft` representation of a raffle now committed as `closed`
- WHEN the publish command revalidates the fresh committed state
- THEN publication is rejected and no raffle business data changes

### Requirement: Close from published only

The system MUST allow an authenticated admin to close a raffle only when its fresh committed lifecycle state is `published`, regardless of whether participation is active, already closed, or never opened. The confirmed close action SHALL serve as temporary manual certification without a separate certification record or workflow. A successful close MUST atomically set `closed`, remove public availability, freeze the participant set, close active participation, and establish conceptual eligibility for a future draw only. Closure MUST NOT create `ready_to_draw` or `drawn` state, perform a draw, or imply revenue automation.

(Previously: published raffles could close, but authenticated operation, committed-state eligibility, irreversibility, participant freezing, and atomic participation closure were unspecified.)

#### Scenario: Close a published raffle

- GIVEN a persisted raffle whose fresh committed status is `published`
- WHEN an authenticated admin submits the confirmed close action
- THEN the raffle becomes `closed`, leaves public availability, and freezes its participant set
- AND any active participation closes in the same atomic outcome

#### Scenario: Draft raffle cannot close directly

- GIVEN a persisted raffle in `draft`
- WHEN the close action is executed
- THEN the system rejects the transition

#### Scenario: Already-closed raffle cannot close again

- GIVEN a persisted raffle whose committed status is `closed`
- WHEN an authenticated admin submits another close action
- THEN the system rejects the transition and mutates no lifecycle or audit data

#### Scenario: Coupled closure cannot partially persist

- GIVEN a `published` raffle whose required active-participation closure cannot complete
- WHEN an authenticated admin attempts overall close
- THEN neither `closed` status nor any participation closure change persists

#### Scenario: Closure creates conceptual future draw eligibility only

- GIVEN a raffle has closed successfully
- WHEN its lifecycle meaning and persisted state are inspected
- THEN it is conceptually eligible for a future draw but remains `closed` without `ready_to_draw` or `drawn` state
- AND no winner selection, payment, advertising, revenue, or draw execution is implied
