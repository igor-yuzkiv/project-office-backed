# Rule: Development workflow

Work moves through two human checkpoints. Between them the agent works on its own and does not
invent extra gates. Git safety (commit/push/reset/rebase/merge) is enforced mechanically in
`.claude/settings.json`, not by prose here.

## Checkpoint 1 — Plan approval

Before writing code for anything non-trivial, present the plan: the intended change, the
affected scope (backend, frontend, or both), and what must not change (contracts, API shapes,
DTOs, component props). Wait for approval. `Clarify before acting` (`principles.md`) applies
here.

## Implementation (between checkpoints)

Implement within the approved scope, following `principles.md`. If the work must go outside
that scope, stop and report it — do not silently absorb it. Run the minimal relevant cheap
checks for what changed — e.g. a small frontend change → lint + type check; a small backend
change → Pint and/or PHPStan (concrete commands: `backend.md` / `frontend.md`).

## Independent review

Before final review, a separate agent — a different context from the author — reviews the
diff: correctness, scope adherence, regressions, over-engineering. Clear blockers before
handing off. Skip only for trivial changes, and say so.

## Checkpoint 2 — Final diff review

Present what changed — and what the independent review flagged — before it is considered done.
Run the full suite or tests only when asked. Do not run browser-based verification for
frontend changes — the user verifies the UI manually.

## Documentation

Ask whether documentation is needed before creating or updating it (`AskUserQuestion`). Keep
project documentation under `docs/`.

## Corrections

Apply corrections in place, within scope. Re-run only the minimal relevant cheap checks. Do
not restart the flow.
