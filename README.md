# Raffles Platform

This repository contains a Laravel raffle platform with separated public and admin hosts.

## Quick path

Use the project wrappers for local development:

```bash
bin/dev
bin/artisan migrate
bin/test
```

Then open:

- Public site: `http://www.raffles.test:8000`
- Admin site: `http://admin.raffles.test:8000`

Do not use bare `localhost` for browser verification. Routes are intentionally host-scoped.

## Identity boundary

- `app/Models/User.php` and the `users` table currently represent PUBLIC website users only.
- Admin identity uses a separate admin-specific model, table, guard, provider, broker, and session boundary.

## Local development runtime

Local development defaults to Docker Compose. Host PHP, Composer, and PostgreSQL are not required.

### Domain model by environment

| Environment | Public host | Admin host |
|-------------|-------------|------------|
| Local | `www.raffles.test` | `admin.raffles.test` |
| Staging example | `staging.raffles.com` | `admin.staging.raffles.com` |
| Production example | `www.raffles.com` | `admin.raffles.com` |

- Local development uses the `.test` convention.
- Staging and production use real DNS owned by the deployment environment.

### Wrapper commands

- `bin/composer install` — install PHP dependencies inside the app container.
- `bin/artisan key:generate` — generate the Laravel app key after the first install.
- `bin/artisan migrate` — apply database migrations inside the app container.
- `bin/test` — run the Pest/PHPUnit suite inside the app container.
- `bin/dev` — start the local app + PostgreSQL runtime.
- `bin/npm <command>` — optional Node/Vite wrapper for scaffold asset commands.

### Data persistence

Local PostgreSQL data is stored in the Docker volume `postgres-data`.

| Command | Database data |
|---------|---------------|
| `bin/dev` | Preserved |
| `docker compose up` | Preserved |
| `docker compose down` | Preserved |
| `docker compose down -v` | Deleted |

`bin/dev` rebuilds the app image when needed, but it does not delete database records.

### First-time setup

```bash
cp .env.example .env
bin/composer install
bin/artisan key:generate
bin/artisan migrate
bin/test
```

Add local host entries before opening the app in a browser:

```text
127.0.0.1 www.raffles.test
127.0.0.1 admin.raffles.test
```

### Running the app

```bash
bin/dev
```

The application container is published on port `8000`, but the HTTP surface is host-scoped.

- Public site: `http://www.raffles.test:8000`
- Admin site: `http://admin.raffles.test:8000`
- PostgreSQL: `localhost:5432`

Do not use bare `localhost` for browser verification of the public/admin surfaces. Route resolution is intentionally bound to the configured hosts.

### Frontend build

Install frontend dependencies and verify the Vite/Tailwind build with:

```bash
bin/npm install
bin/npm run build
```

`package-lock.json` is committed so frontend installs are reproducible. If `vite` is missing, run `bin/npm install` before retrying the build.

## Browser verification

### Admin raffle participation lifecycle

The admin can manually open and close participation for published raffles. This lifecycle is separate from raffle publication.

1. Start the app:

   ```bash
   bin/dev
   ```

2. Apply migrations if needed:

   ```bash
   bin/artisan migrate
   ```

3. Open the admin host:

   ```text
   http://admin.raffles.test:8000
   ```

4. Sign in as an admin and go to the raffle list.

5. Use an existing published raffle, or create/publish one through the admin flow.

6. From the raffle row, use the participation actions:

   - Open participation.
   - Close participation.

Expected behavior:

- A draft raffle cannot accept participants.
- A published raffle does not accept participants until an admin opens participation.
- An opened raffle can accept participants through the domain rule `canAcceptParticipants()`.
- A closed raffle no longer accepts participants.
- Invalid transitions, such as double-submit or stale-tab actions, show feedback on the admin list.

Out of scope for the current browser flow:

- Public participant registration.
- Ticket purchases.
- Payments.
- Automatic closure when the funding target is reached.
- Reopening participation.

### Host-separated smoke tests

The first slice includes smoke coverage for:

- `www.raffles.test`
- `admin.raffles.test`

Those hosts are exercised through Laravel HTTP tests and can be run with `bin/test`.
