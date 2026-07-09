# Project Office Backend

Laravel backend and Vue single-page application for Project Office, a project and task
management system designed for both human users and agent-facing CLI workflows.

This repository owns:

- The web application served by Laravel and Vite.
- The authenticated web API used by the Vue application.
- A smaller authenticated CLI API used by `project-office-cli`.
- Domain models for projects, task lists, tasks, comments, tags, attachments, task
  ownership, users, and project documents.

## Stack

- PHP 8.3+
- Laravel
- PostgreSQL
- Redis
- Laravel Sanctum
- Laravel Horizon
- Laravel Reverb support
- Laravel Scout
- Vue 3
- TypeScript
- Vite
- PrimeVue
- MinIO/S3-compatible attachment storage

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- Docker, for the local PostgreSQL, Redis, and MinIO services

## Local Setup

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the Laravel environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Start local infrastructure:

```bash
docker compose up -d
```

Review `.env` before running migrations. The provided `.env.example` defaults are aligned
with `docker-compose.yml` for PostgreSQL, Redis, and MinIO local development.

Run migrations:

```bash
php artisan migrate
```

Start the development processes:

```bash
composer run dev
```

That script runs the Laravel server, queue listener, log tailing, and Vite dev server
together.

## Common Commands

```bash
# Backend server only
php artisan serve

# Frontend dev server only
npm run dev

# Build frontend assets
npm run build

# Run the test suite
php artisan test

# Run PHP static analysis
vendor/bin/phpstan analyse

# Format PHP code
vendor/bin/pint

# Check frontend types
npm run types:check

# Check frontend linting
npm run lint:check
```

## HTTP Surfaces

### Web Application

The root route serves the Vue SPA:

```txt
GET /
```

The SPA includes authenticated pages for projects, tasks, project documents, user
profile, API tokens, comments, attachments, filters, sorting, and related management
workflows.

### Web API

The main JSON API is mounted under Laravel's standard API prefix:

```txt
/api
```

It is protected with Sanctum where required and includes endpoints for:

- Authentication and current user lookup
- Projects and project search
- Task lists and task-list search
- Tasks and task search
- Project documents and project-document trees
- Tags
- Attachments
- Task comments
- Task owners
- Users
- API tokens
- Generic comments

### CLI API

The agent-facing CLI API is mounted at:

```txt
/api/cli
```

It is intentionally smaller than the web API and supports the workflows needed by
`project-office-cli`:

- Current user lookup
- Project lookup
- Task list/read/create/update
- Task comment list/create/update

CLI requests are authenticated with Sanctum tokens.

## Domain Structure

Backend domain code lives under `app/Domains/`:

```txt
app/Domains/
  Attachment/
  Comment/
  Project/
  ProjectDocument/
  Shared/
  Tag/
  Task/
  TaskList/
  User/
```

HTTP controllers, requests, and resources are split by surface:

```txt
app/Http/WebApi/
app/Http/CliApi/
app/Http/Shared/
```

Frontend code lives under `resources/js/` and follows the same broad separation:

```txt
resources/js/app/
resources/js/entities/
resources/js/pages/
resources/js/shared/
resources/js/widgets/
```

## Testing

The test suite uses a dedicated PostgreSQL database named `task_manager_test`.

To recreate it inside the running PostgreSQL container:

```bash
./scripts/init_testing_pg_database.sh
```

Then migrate and run tests:

```bash
php artisan migrate --env=testing
php artisan test
```

See [Testing](./docs/testing.md) for more details.

## Documentation

Project documentation lives in [`docs/`](./docs):

- [Filtering system](./docs/filtering-system.md)
- [Include system](./docs/include-system.md)
- [Project documents](./docs/project-documents.md)
- [Sorting system](./docs/sorting-system.md)
- [Testing](./docs/testing.md)

Some deeper technical documents still need English cleanup before publication.

## Configuration Notes

- `.env` must never be committed.
- API tokens are managed through the user profile UI and authenticated via Sanctum.
- Attachments use the `attachments` filesystem disk, backed by S3-compatible storage.
- Local development can use MinIO from `docker-compose.yml`.
- Generated build output, Docker data, local IDE files, and Project Office repo settings
  are ignored by git.

## Related Repository

`project-office-cli` is the companion CLI that agents use to access a controlled subset
of this backend through `/api/cli`.
