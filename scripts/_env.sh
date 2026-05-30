#!/usr/bin/env bash

if [ -z "${BASH_VERSION:-}" ]; then
    echo "ERROR: This helper must be sourced from bash." >&2
    return 1 2>/dev/null || exit 1
fi

set -o pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${ENV_FILE:-$SCRIPT_DIR/.env}"

load_env_file() {
    if [[ -f "$ENV_FILE" ]]; then
        set -a
        # shellcheck disable=SC1090
        source "$ENV_FILE"
        set +a
    fi
}

prompt_env() {
    local var_name="$1"
    local prompt="${2:-$var_name}"
    local default_value="${3:-}"
    local current_value="${!var_name:-}"

    if [[ -n "$current_value" ]]; then
        return 0
    fi

    if [[ -n "$default_value" ]]; then
        read -r -p "$prompt [$default_value]: " current_value
        current_value="${current_value:-$default_value}"
    else
        while [[ -z "$current_value" ]]; do
            read -r -p "$prompt: " current_value
        done
    fi

    export "$var_name=$current_value"
}

require_command() {
    local command_name="$1"

    if ! command -v "$command_name" >/dev/null 2>&1; then
        echo "ERROR: Required command '$command_name' is not available in PATH." >&2
        exit 1
    fi
}

load_env_file
