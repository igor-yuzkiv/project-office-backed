#!/usr/bin/env bash

# Recreates the dedicated testing database inside the running PostgreSQL
# container (drops it if present, then creates it fresh, owned by the app user).
#
# Config is read from scripts/.env (see _env.sh). Test DB name resolution order:
#   1) first CLI argument
#   2) POSTGRES_TEST_DB from the environment / scripts/.env
#   3) "<POSTGRES_DB>_test" as a fallback
#
# Usage:
#   ./scripts/init_testing_pg_database.sh
#   ./scripts/init_testing_pg_database.sh task_manager_test

if [ -z "${BASH_VERSION:-}" ]; then
    exec bash "$0" "$@"
fi

set -euo pipefail

basePath="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC1091
source "$basePath/_env.sh"

require_command "docker"

prompt_env "POSTGRES_CONTAINER" "PostgreSQL container name"
prompt_env "POSTGRES_ADMIN_USER" "PostgreSQL admin user" "root"
prompt_env "POSTGRES_DB" "Primary (non-test) database name"
prompt_env "POSTGRES_DB_OWNER" "Test database owner"

POSTGRES_TEST_DB="${1:-${POSTGRES_TEST_DB:-${POSTGRES_DB}_test}}"

if ! printf '%s' "$POSTGRES_TEST_DB" | grep -qE '^[a-zA-Z_][a-zA-Z0-9_]*$'; then
    echo "ERROR: Invalid test database name: $POSTGRES_TEST_DB" >&2
    echo "Only letters, numbers and underscores are allowed; must not start with a number." >&2
    exit 1
fi

if [ "$POSTGRES_TEST_DB" = "$POSTGRES_DB" ]; then
    echo "ERROR: Test database name equals the primary database ($POSTGRES_DB) — refusing to drop it." >&2
    exit 1
fi

echo "Recreating test database: $POSTGRES_TEST_DB (owner: $POSTGRES_DB_OWNER)"

docker exec -i "$POSTGRES_CONTAINER" psql -U "$POSTGRES_ADMIN_USER" -d postgres <<EOSQL
SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname = '$POSTGRES_TEST_DB' AND pid <> pg_backend_pid();

DROP DATABASE IF EXISTS "$POSTGRES_TEST_DB";
CREATE DATABASE "$POSTGRES_TEST_DB" OWNER "$POSTGRES_DB_OWNER";
GRANT ALL PRIVILEGES ON DATABASE "$POSTGRES_TEST_DB" TO "$POSTGRES_DB_OWNER";
EOSQL

echo "Test database $POSTGRES_TEST_DB created successfully."
echo "Next: php artisan migrate --env=testing"
