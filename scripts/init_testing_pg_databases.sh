#!/usr/bin/env bash

# Recreates the dedicated auxiliary databases inside the running PostgreSQL
# container (drops each if present, then creates it fresh, owned by the app
# user). Manages two databases:
#   - the PHPUnit testing database  (<POSTGRES_DB>_test)
#   - the Playwright e2e database    (<POSTGRES_DB>_e2e)
#
# Config is read from scripts/.env (see _env.sh). DB name resolution:
#   test DB: 1) first CLI argument  2) POSTGRES_TEST_DB  3) "<POSTGRES_DB>_test"
#   e2e DB:  1) second CLI argument 2) POSTGRES_E2E_DB   3) "<POSTGRES_DB>_e2e"
#
# Usage:
#   ./scripts/init_testing_pg_databases.sh
#   ./scripts/init_testing_pg_databases.sh task_manager_test task_manager_e2e

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
prompt_env "POSTGRES_DB_OWNER" "Auxiliary database owner"

POSTGRES_TEST_DB="${1:-${POSTGRES_TEST_DB:-${POSTGRES_DB}_test}}"
POSTGRES_E2E_DB="${2:-${POSTGRES_E2E_DB:-${POSTGRES_DB}_e2e}}"

recreate_database() {
    local db_name="$1"
    local label="$2"

    if ! printf '%s' "$db_name" | grep -qE '^[a-zA-Z_][a-zA-Z0-9_]*$'; then
        echo "ERROR: Invalid $label database name: $db_name" >&2
        echo "Only letters, numbers and underscores are allowed; must not start with a number." >&2
        exit 1
    fi

    if [ "$db_name" = "$POSTGRES_DB" ]; then
        echo "ERROR: $label database name equals the primary database ($POSTGRES_DB) — refusing to drop it." >&2
        exit 1
    fi

    echo "Recreating $label database: $db_name (owner: $POSTGRES_DB_OWNER)"

    docker exec -i "$POSTGRES_CONTAINER" psql -U "$POSTGRES_ADMIN_USER" -d postgres <<EOSQL
SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname = '$db_name' AND pid <> pg_backend_pid();

DROP DATABASE IF EXISTS "$db_name";
CREATE DATABASE "$db_name" OWNER "$POSTGRES_DB_OWNER";
GRANT ALL PRIVILEGES ON DATABASE "$db_name" TO "$POSTGRES_DB_OWNER";
EOSQL

    echo "$label database $db_name created successfully."
}

recreate_database "$POSTGRES_TEST_DB" "test"
recreate_database "$POSTGRES_E2E_DB" "e2e"

echo "Next:"
echo "  php artisan migrate --env=testing"
echo "  php artisan migrate:fresh --seed --seeder=E2eSeeder --env=e2e"
