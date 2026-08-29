# Running tests

The backend test suite runs against a **dedicated PostgreSQL database**
(`task_manager_test`), separate from the development database (`task_manager`),
so tests never touch dev data.

## Configuration

- **`phpunit.xml`** pins the test connection (`DB_DATABASE=task_manager_test`,
  user/password `task_manager`) and the fast/in-memory drivers used during tests.
  These values take precedence when running through `php artisan test` / PHPUnit.
- **`.env.testing`** provides the rest of the testing environment (app key,
  `APP_ENV=testing`, `array`/`sync` drivers, `s3` attachments provider). Laravel
  loads it automatically whenever `APP_ENV=testing`.

## Where the test database comes from

The test database is created in two places, both already wired up:

- **Fresh container init** — `docker-entrypoint-initdb.d/01-init.sh` creates
  `<APP_DB_NAME>_test` when the Postgres container initializes an empty data
  directory (owned by the app user).
- **On demand** — `scripts/init_testing_pg_databases.sh` (re)creates the test
  database inside the already-running container, without recreating the container
  or wiping the dev database.

## Recreate the test database

Run when you need a clean test database (config is read from `scripts/.env`):

```bash
./scripts/init_testing_pg_databases.sh
# or with an explicit name:
./scripts/init_testing_pg_databases.sh task_manager_test
```

It refuses to run if the resolved name equals the primary database, terminates
open connections, then drops and recreates the database owned by the app user.

## Migrate and run

```bash
# Apply migrations to the test database
php artisan migrate --env=testing

# Run the whole suite
php artisan test

# Run a subset
php artisan test --filter=CliApi
```

`php artisan test` uses `phpunit.xml`, so it always targets `task_manager_test` —
no extra flags needed.

# End-to-end tests

Playwright specs live in `e2e/`, shared helpers in `e2e/support/`, and they run against a
**third database**, `task_manager_e2e`, reseeded on every run — never the dev or the PHPUnit one.
The specs are committed, not throwaway runs.

## What one run does

`npm run test:e2e` is `playwright test`; its `webServer` (see `playwright.config.ts`) builds the
SPA in e2e mode, recreates and seeds the e2e database, and serves the app on `:8100` under
`APP_ENV=e2e` (`.env.e2e`). Because it reseeds, run it deliberately, not as a reflex.

The config calls a bare `php`. On a machine where that is not the project's PHP, run the same
three steps by hand and let Playwright reuse the server:

```bash
npm run build -- --mode e2e
php8.5 artisan migrate:fresh --seed --seeder=E2eSeeder --env=e2e
php8.5 artisan serve --env=e2e --port=8100 &
npx playwright test
```

## Fixtures

`database/seeders/E2eSeeder.php` is the whole dataset: one user (`e2e@example.com` /
`password`, mirrored by `E2E_USER_*` in `.env.e2e`), one project, and three documents:

| Key | Used by |
|---|---|
| `DOC-E2E-1` | `annotations.spec.ts` — markdown covering every block type the anchors distinguish |
| `DOC-E2E-2` | `autosave.spec.ts` |
| `DOC-E2E-3` | `modes.spec.ts`, `sidebar.spec.ts` |

One document **per spec file that writes**: tests run in parallel across workers, and two files
autosaving into the same version would overwrite each other. A file whose tests write is also
marked `test.describe.configure({ mode: 'serial' })` so its own tests do not race. Text a test
types or creates carries a timestamp, so anything left behind by an interrupted run is never
mistaken for the current one.

## Writing a spec

Sign in with `signIn(page)` and open a document with `openDocument(page, DOCUMENTS.x)` from
`e2e/support`; `switchMode`, `editor`, `preview` and `typeAtEnd` cover the content tab. Prefer
roles, labels and `data-testid` over CSS classes — the sidebar drawer, for example, is
`getByTestId('side-tabs-drawer')`. A write is asserted by waiting for its response
(`isVersionContentWrite`), not by sleeping.
