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
