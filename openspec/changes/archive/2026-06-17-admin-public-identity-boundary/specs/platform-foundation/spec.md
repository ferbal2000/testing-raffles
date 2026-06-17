# Delta for Platform Foundation

## ADDED Requirements

### Requirement: Host separation is not auth isolation

The foundation MUST require explicit auth-boundary verification; separate hosts MAY support routing, but they MUST NOT be treated as sufficient proof of admin/public authentication isolation.

#### Scenario: Host routing alone is insufficient

- GIVEN public and admin hosts resolve correctly
- WHEN auth isolation is evaluated
- THEN guard and session assertions are still required

#### Scenario: Foundation verification uses the canonical runner

- GIVEN auth-boundary tests exist
- WHEN the foundation test workflow is executed
- THEN verification runs through `bin/test`

## MODIFIED Requirements

### Requirement: Temporary public identity boundary is explicit

The foundation slice MUST document that the default Laravel `User` model and `users` table belong to the public website boundary only, and any admin authentication MUST use `App\Models\Admin` backed by `admins`.
(Previously: the foundation documented `User` / `users` as public-only while deferring the admin identity contract.)

#### Scenario: Future admin identity work starts from an explicit boundary

- GIVEN a developer reads the auth configuration, model documentation, migration comments, or README
- WHEN they inspect the identity setup
- THEN they can see that `User` / `users` is public-only and admin identity is separate

#### Scenario: Public identity source remains stable

- GIVEN a public website authentication flow is reviewed
- WHEN the implemented identity contract is inspected
- THEN the public boundary still resolves through Laravel `User` / `users`
