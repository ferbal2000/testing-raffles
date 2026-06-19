# Delta for Admin Identity Boundary

## MODIFIED Requirements

### Requirement: Boundary verification is test-first and host-aware

The system MUST prove identity isolation and real admin session behavior with automated tests run through `bin/test`, and admin auth verification MUST use the admin host plus explicit guest redirect assertions instead of relying on framework-default login-route behavior.
(Previously: Verification proved isolation beyond routing but did not require host-aware admin auth lifecycle coverage or explicit admin guest redirect assertions.)

#### Scenario: Verification proves auth isolation beyond routing

- GIVEN admin and public routes are host-separated
- WHEN boundary verification runs
- THEN tests assert guard, session, and remember-me isolation explicitly

#### Scenario: Verification uses the canonical test runner

- GIVEN the identity boundary slice is implemented
- WHEN the auth verification suite is executed
- THEN it runs through `./bin/test`

#### Scenario: Guest redirect is verified on the admin host

- GIVEN a protected admin page and an unauthenticated request on the admin host
- WHEN boundary verification runs
- THEN tests assert redirect to the admin login route without depending on `route('login')`

#### Scenario: Admin auth flow preserves public isolation

- GIVEN the admin login and logout flow is exercised on the admin host
- WHEN the public boundary is evaluated before or after that flow
- THEN the public boundary remains unauthenticated unless it was independently signed in
