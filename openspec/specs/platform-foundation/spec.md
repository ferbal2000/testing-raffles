# Platform Foundation Specification

## Purpose

Define the verified foundation behavior that makes the raffles application runnable, testable, and ready for later domain slices.

## Invariants

- Local development and verification MUST run through repository wrapper commands instead of requiring host-installed PHP, Composer, or Node.
- Public and admin HTTP entry points MUST remain host-separated.
- Application-facing Spanish copy MUST be rendered through translation keys/files, not inline literals in Blade views.
- The default Laravel `User` / `users` wiring MUST remain the public-site identity, and admin authentication MUST use separate `App\Models\Admin` / `admins` contracts.

## Out of Scope

PostgreSQL-backed domain persistence assertions, raffle lifecycle domain rules, entry submission, draw execution, and audit log behavior.

## Requirements

### Requirement: Canonical containerized runtime

The project MUST provide a repository-native local runtime and test harness through Docker Compose and wrapper scripts.

#### Scenario: Developer runs the canonical test command

- GIVEN a machine with Docker available
- WHEN the developer executes `./bin/test`
- THEN the Laravel test suite runs through the repository container runtime without requiring host PHP or Composer

#### Scenario: Developer inspects the local app runtime

- GIVEN the repository runtime files are present
- WHEN the developer executes wrapper commands such as `./bin/artisan` or `./bin/composer`
- THEN those commands execute inside the configured application container

### Requirement: Host-separated bootstrap surfaces

The application MUST expose separate public and admin bootstrap routes on their configured hosts.

#### Scenario: Public host resolves the public placeholder

- GIVEN the application is configured for `www.raffles.test`
- WHEN a request is made to the public root host
- THEN the public placeholder page is returned

#### Scenario: Admin host resolves the admin placeholder

- GIVEN the application is configured for `admin.raffles.test`
- WHEN a request is made to the admin root host
- THEN the admin placeholder page is returned

#### Scenario: Unknown host is rejected

- GIVEN a root request targets a non-configured host
- WHEN the request reaches the application
- THEN the application returns a not found response for that host

### Requirement: Spanish copy through translation keys

The public and admin placeholder surfaces MUST read their Spanish-facing copy from translation keys/files.

#### Scenario: Placeholder copy is resolved from translations

- GIVEN the public or admin home placeholder is rendered
- WHEN the response content is generated
- THEN the visible Spanish copy comes from stable translation keys backed by `lang/es/*`

### Requirement: Temporary public identity boundary is explicit

The foundation slice MUST document that the default Laravel `User` model and `users` table belong to the public website boundary only, and any admin authentication MUST use `App\Models\Admin` backed by `admins`.

#### Scenario: Future admin identity work starts from an explicit boundary

- GIVEN a developer reads the auth configuration, model documentation, migration comments, or README
- WHEN they inspect the identity setup
- THEN they can see that `User` / `users` is public-only and admin identity is separate

#### Scenario: Public identity source remains stable

- GIVEN a public website authentication flow is reviewed
- WHEN the implemented identity contract is inspected
- THEN the public boundary still resolves through Laravel `User` / `users`

### Requirement: Host separation is not auth isolation

The foundation MUST require explicit auth-boundary verification; separate hosts MAY support routing, but they MUST NOT be treated as sufficient proof of admin/public authentication isolation.

#### Scenario: Host routing alone is insufficient

- GIVEN public and admin hosts resolve correctly
- WHEN auth isolation is evaluated
- THEN guard and session assertions are still required

#### Scenario: Foundation verification uses the canonical runner

- GIVEN auth-boundary tests exist
- WHEN the foundation test workflow is executed
- THEN verification runs through `./bin/test`
