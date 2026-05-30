#!/usr/bin/env bash

if [ -z "${BASH_VERSION:-}" ]; then
    exec bash "$0" "$@"
fi

set -euo pipefail

basePath="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC1091
source "$basePath/_env.sh"

prompt_env "PROJECT_PATH" "Project path"
prompt_env "LOG_BACKUP_PATH" "Logs backup path" "$PROJECT_PATH/storage/logs/backups"
prompt_env "LOG_FILES" "Log files" "laravel.log"

today="$(date '+%Y_%m_%d')"
logsDir="$PROJECT_PATH/storage/logs"
backupsDir="$LOG_BACKUP_PATH/$today"

mkdir -p "$backupsDir"

for logFile in $LOG_FILES; do
    sourceFile="$logsDir/$logFile"

    if [[ -f "$sourceFile" ]]; then
        cp "$sourceFile" "$backupsDir/$today-$logFile"
        : > "$sourceFile"
    else
        echo "$sourceFile - file does not exist."
    fi
done
