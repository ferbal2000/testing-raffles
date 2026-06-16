# Raffles Platform

This repository contains the first foundation slice for the raffles platform.

## Identity boundary for this slice

- `app/Models/User.php` and the `users` table currently represent PUBLIC website users only.
- Admin identity is intentionally not implemented in PR 1 / Work Unit 1.
- A later slice will add a separate admin-specific model, table, guard, provider, broker, and session boundary.

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
- `bin/test` — run the Pest/PHPUnit suite inside the app container.
- `bin/dev` — start the local app + PostgreSQL runtime.
- `bin/npm <command>` — optional Node/Vite wrapper for scaffold asset commands.

### First-time setup

```bash
cp .env.example .env
bin/composer install
bin/artisan key:generate
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

### Host-separated smoke tests

The first slice includes smoke coverage for:

- `www.raffles.test`
- `admin.raffles.test`

Those hosts are exercised through Laravel HTTP tests and can be run with `bin/test`.
