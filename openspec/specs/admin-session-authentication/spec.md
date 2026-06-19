# Admin Session Authentication Specification

## Purpose

Define the admin-host sign-in, sign-out, and protected-page access behavior for real admin pages.

## Requirements

### Requirement: Admin guest entry and redirect handling

The system MUST expose an admin-host login entry point for guests, and protected admin pages MUST redirect unauthenticated requests to that admin login route. The system MUST NOT rely on Laravel's default missing `route('login')` behavior for admin requests.

#### Scenario: Guest opens the admin login page

- GIVEN the request targets the admin host
- WHEN a guest requests the admin login page
- THEN the system returns the admin login form

#### Scenario: Guest requests a protected admin page

- GIVEN the request targets a protected admin page on the admin host
- WHEN the requester is not authenticated as an admin
- THEN the system redirects to the admin login route on the admin host

#### Scenario: Guest redirect is explicitly defined

- GIVEN admin route protection is enabled
- WHEN an unauthenticated admin request is handled
- THEN the redirect resolves without depending on Laravel's default `route('login')`

### Requirement: Admin session creation

The system MUST create an admin session only when valid admin credentials are submitted through the admin-host login flow.

#### Scenario: Valid admin credentials create a session

- GIVEN an existing admin account and the admin host login form
- WHEN valid credentials are submitted
- THEN the admin session is created and access to protected admin pages is granted

#### Scenario: Invalid admin credentials are rejected

- GIVEN the admin host login form
- WHEN invalid credentials are submitted
- THEN no admin session is created and the guest remains unable to access protected admin pages

### Requirement: Admin logout invalidates admin access

The system MUST invalidate the authenticated admin session on logout so later requests to protected admin pages require fresh authentication.

#### Scenario: Authenticated admin logs out

- GIVEN an authenticated admin session on the admin host
- WHEN the admin submits logout
- THEN the admin session is invalidated

#### Scenario: Logged-out admin revisits a protected page

- GIVEN an admin session was previously authenticated and then logged out
- WHEN the same client requests a protected admin page
- THEN the system redirects to the admin login route
