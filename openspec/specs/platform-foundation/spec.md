# Platform Foundation Specification

## Purpose

Define the verified foundation behavior that makes the raffles application runnable, testable, and ready for later domain slices.

## Invariants

- Local development and verification MUST run through repository wrapper commands instead of requiring host-installed PHP, Composer, or Node.
- Public and admin HTTP entry points MUST remain host-separated.
- Application-facing Spanish copy MUST be rendered through translation keys/files, not inline literals in Blade views.
- The default Laravel `User` / `users` wiring MUST be treated as the public-site identity only until a separate admin identity is implemented.

## Out of Scope

Admin identity implementation, PostgreSQL-backed domain persistence assertions, raffle lifecycle domain rules, entry submission, draw execution, and audit log behavior.

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

The foundation slice MUST document that the default Laravel `User` model and `users` table belong to the public website boundary only.

#### Scenario: Future admin identity work starts from an explicit boundary

- GIVEN a developer reads the auth configuration, model documentation, migration comments, or README
- WHEN they inspect the current identity setup
- THEN they can see that admin identity is intentionally deferred and the default Laravel user wiring is public-only in this slice
