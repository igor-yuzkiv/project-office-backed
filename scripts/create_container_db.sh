#!/usr/bin/env bash

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
prompt_env "POSTGRES_DB_OWNER" "Database owner"
prompt_env "CREATE_POSTGRES_DB" "Database name to create"

echo "Creating database: $CREATE_POSTGRES_DB"

docker exec -i "$POSTGRES_CONTAINER" psql -U "$POSTGRES_ADMIN_USER" postgres <<EOSQL
DROP DATABASE IF EXISTS "$CREATE_POSTGRES_DB";
CREATE DATABASE "$CREATE_POSTGRES_DB";
GRANT ALL PRIVILEGES ON DATABASE "$CREATE_POSTGRES_DB" TO "$POSTGRES_DB_OWNER";
ALTER DATABASE "$CREATE_POSTGRES_DB" OWNER TO "$POSTGRES_DB_OWNER";
EOSQL

read -r -p "Enable PostGIS extension? [y/N]: " enablePostgis

if [[ "$enablePostgis" =~ ^[Yy]$ ]]; then
    echo "Enabling PostGIS extension."
    docker exec -i "$POSTGRES_CONTAINER" psql -U "$POSTGRES_ADMIN_USER" "$CREATE_POSTGRES_DB" <<EOSQL
CREATE EXTENSION IF NOT EXISTS postgis;
EOSQL
fi

echo "Database $CREATE_POSTGRES_DB created successfully."
