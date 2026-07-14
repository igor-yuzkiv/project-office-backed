#!/usr/bin/env bash
#
# PreToolUse hook (Edit|Write): deny rewriting shared test infrastructure.
#
# Guards the shared "definition of correct" that an agent could otherwise weaken to force a
# green test: the PHPUnit runner config, the base TestCase, and existing migrations. Only edits
# and overwrites of EXISTING files are blocked — creating a NEW file is allowed, so the agent
# can still scaffold new migrations and new test bases as normal feature work.
#
# Factories are intentionally NOT guarded: extending a factory is routine feature work, and
# the precise per-task control is the allowed-scope contract (a later stage), not this floor.
#
# Contract: exit 2 blocks the tool call (stderr is shown to the agent); exit 0 allows.
# Exit 1 does NOT block, so it is never used for denial.

set -uo pipefail

payload="$(cat)"
file_path="$(printf '%s' "$payload" | jq -r '.tool_input.file_path // empty')"

# Not a file-targeting edit → nothing to guard.
[ -n "$file_path" ] || exit 0

# Normalise to a leading slash so the segment globs below match both absolute and relative
# paths.
path="$file_path"
[[ "$path" == /* ]] || path="/$path"
base="${path##*/}"

# Decide whether this path is guarded shared infrastructure, and why.
reason=""
case "$base" in
  phpunit.xml | phpunit.xml.dist) reason="PHPUnit runner config" ;;
esac
if [ -z "$reason" ] && [[ "$path" == */tests/* && "$base" == *TestCase.php ]]; then
  reason="base TestCase"
fi
if [ -z "$reason" ] && [[ "$path" == */database/migrations/* ]]; then
  reason="existing migration (append-only — add a new migration instead)"
fi

# Not guarded → allow (this includes database/factories/** and leaf *Test.php files).
[ -n "$reason" ] || exit 0

# Guarded, but creating a NEW file is allowed — only block edits/overwrites of an existing one.
[ -e "$file_path" ] || exit 0

printf 'Blocked rewrite of shared test infrastructure: %s\nReason: %s.\nThis file already exists; the wall blocks rewriting it. Create a new file instead, or change it by hand.\n' \
  "$file_path" "$reason" >&2
exit 2
