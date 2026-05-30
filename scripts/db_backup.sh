#!/usr/bin/env bash

if [ -z "${BASH_VERSION:-}" ]; then
    exec bash "$0" "$@"
fi

set -euo pipefail

basePath="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC1091
source "$basePath/_env.sh"

require_command "docker"
require_command "gzip"

prompt_env "BACKUPS_DIR" "Backups directory"
prompt_env "BACKUP_RETENTION_DAYS" "Backup retention days" "7"
prompt_env "POSTGRES_CONTAINER" "PostgreSQL container name"
prompt_env "POSTGRES_USER" "PostgreSQL user"
prompt_env "POSTGRES_DB" "PostgreSQL database"

today="$(date '+%Y_%m_%d')"
backupsDir="$BACKUPS_DIR/$today"

echo "Creating backups directory: $backupsDir"
mkdir -p "$backupsDir"
cd "$backupsDir"

echo "Backing up PostgreSQL database: $POSTGRES_DB"
docker exec -i "$POSTGRES_CONTAINER" pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB" | gzip -9 > "$POSTGRES_DB.sql.gz"

if [[ -n "${MONGO_CONTAINER:-}" || -n "${MONGO_DB:-}" ]]; then
    prompt_env "MONGO_CONTAINER" "MongoDB container name"
    prompt_env "MONGO_DB" "MongoDB database"

    mongoExcludeArgs=()
    if [[ -n "${MONGO_EXCLUDE_COLLECTIONS:-}" ]]; then
        for collection in $MONGO_EXCLUDE_COLLECTIONS; do
            mongoExcludeArgs+=(--excludeCollection "$collection")
        done
    fi

    echo "Backing up MongoDB database: $MONGO_DB"
    docker exec -i "$MONGO_CONTAINER" mongodump --db "$MONGO_DB" "${mongoExcludeArgs[@]}" --gzip --archive > "$MONGO_DB.gz"
fi

echo "Removing backups older than $BACKUP_RETENTION_DAYS days"
find "$BACKUPS_DIR" -mindepth 1 -maxdepth 1 -type d -mtime +"$BACKUP_RETENTION_DAYS" -exec rm -rf {} \;

if [[ -n "${RCLONE_REMOTE_NAME:-}" || -n "${REMOTE_DRIVE_FOLDER_NAME:-}" ]]; then
    require_command "rclone"
    prompt_env "RCLONE_REMOTE_NAME" "Rclone remote name"
    prompt_env "REMOTE_DRIVE_FOLDER_NAME" "Remote drive folder name"

    remoteDriveDir="$RCLONE_REMOTE_NAME:$REMOTE_DRIVE_FOLDER_NAME/$today"
    echo "Syncing backups to: $remoteDriveDir"
    rclone copy "$backupsDir" "$remoteDriveDir"
fi

echo "Database backups completed successfully."
