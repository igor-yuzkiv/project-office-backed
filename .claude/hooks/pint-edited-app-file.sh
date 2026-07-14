#!/usr/bin/env bash
#
# PostToolUse hook (Edit|Write): auto-format the edited file with Pint.
#
# Runs ./vendor/bin/pint on the single edited file, but only when it is a .php file under the
# project's app/ directory. PostToolUse runs after the edit, so it never blocks the agent; it
# stays quiet and never fails the flow (a missing binary or a pint error is a no-op).

set -uo pipefail

payload="$(cat)"
file_path="$(printf '%s' "$payload" | jq -r '.tool_input.file_path // empty')"

# Nothing to format.
[ -n "$file_path" ] || exit 0

# Only PHP files.
case "$file_path" in
  *.php) ;;
  *) exit 0 ;;
esac

project_dir="${CLAUDE_PROJECT_DIR:-$PWD}"

# Only files under the project's app/ directory (absolute or relative form).
[[ "$file_path" == "$project_dir/app/"* || "$file_path" == app/* ]] || exit 0

pint="$project_dir/vendor/bin/pint"

# No pint binary → quietly do nothing.
[ -x "$pint" ] || exit 0

# Format just this file; never fail the flow or emit noise.
"$pint" "$file_path" >/dev/null 2>&1 || true
exit 0
