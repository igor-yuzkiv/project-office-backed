#!/usr/bin/env bash

if [ -z "${BASH_VERSION:-}" ]; then
    exec bash "$0" "$@"
fi

set -euo pipefail

basePath="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC1091
source "$basePath/_env.sh"

require_command "docker"
require_command "gunzip"

prompt_env "POSTGRES_CONTAINER" "PostgreSQL container name"
prompt_env "POSTGRES_USER" "PostgreSQL user"
prompt_env "PG_BACKUP_FILE" "Path to .sql.gz backup file"
prompt_env "PG_DEST_DB" "Destination database"

if [[ ! -f "$PG_BACKUP_FILE" ]]; then
    echo "ERROR: Backup file does not exist: $PG_BACKUP_FILE" >&2
    exit 1
fi

echo "Restoring $PG_BACKUP_FILE to $PG_DEST_DB inside container $POSTGRES_CONTAINER"

gunzip -c "$PG_BACKUP_FILE" | docker exec -i "$POSTGRES_CONTAINER" psql -U "$POSTGRES_USER" -d "$PG_DEST_DB"

echo "PostgreSQL database restored successfully into $PG_DEST_DB."
