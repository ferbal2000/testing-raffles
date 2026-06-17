# Admin Identity Boundary Specification

## Purpose

Define the observable admin authentication boundary while preserving Laravel `users` / `User` as the public identity contract.

## Requirements

### Requirement: Separate admin identity source

The system MUST authenticate admins through `App\Models\Admin`, the `admins` table, and an admin-only auth provider that is separate from the public website identity.

#### Scenario: Admin auth resolves through admin identity

- GIVEN an admin authentication flow is evaluated
- WHEN the application resolves the admin identity source
- THEN it uses `App\Models\Admin` backed by `admins`

#### Scenario: Public auth remains on Laravel defaults

- GIVEN a public website authentication flow is evaluated
- WHEN the application resolves the public identity source
- THEN it uses `App\Models\User` backed by `users`

### Requirement: Guard and session boundaries are isolated

The system MUST isolate admin and public guards, sessions, remember-me state, and authenticated context so one boundary cannot authenticate into the other.

#### Scenario: Admin authentication does not sign in public boundary

- GIVEN an admin identity is authenticated
- WHEN the public guard is evaluated in a later request
- THEN the public boundary remains unauthenticated

#### Scenario: Public remember-me state does not sign in admin boundary

- GIVEN a public identity has persistent login state
- WHEN an admin boundary request is evaluated
- THEN the admin boundary remains unauthenticated

### Requirement: Recovery and provider boundaries are explicit

The system MUST keep password recovery and auth-provider resolution scoped to the matching identity boundary without inventing product UI requirements.

#### Scenario: Public recovery resolves only public identity

- GIVEN public password recovery is configured or invoked
- WHEN the recovery provider is resolved
- THEN it targets only the public identity boundary

#### Scenario: Admin recovery cannot operate on public identity

- GIVEN admin password recovery is configured or invoked
- WHEN the recovery provider is resolved
- THEN it does not issue or accept recovery state for public users

### Requirement: Boundary verification is test-first and host-agnostic

The system MUST prove identity isolation with automated tests run through `bin/test`, and host or domain routing alone MUST NOT be treated as sufficient auth isolation.

#### Scenario: Verification proves auth isolation beyond routing

- GIVEN admin and public routes are host-separated
- WHEN boundary verification runs
- THEN tests assert guard, session, and remember-me isolation explicitly

#### Scenario: Verification uses the canonical test runner

- GIVEN the identity boundary slice is implemented
- WHEN the auth verification suite is executed
- THEN it runs through `bin/test`
