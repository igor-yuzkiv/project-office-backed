# Project Office

## What this is

A task manager: Laravel 13 / PHP 8.3 API behind a Vue SPA. Two HTTP surfaces, not one —
`WebApi` (`/api`) serves the SPA and may change with it; `CliApi` (`/api/cli`) is an
agent-facing public contract whose consumers live outside this repository.

## Architecture

- `app/Domains/` owns business behaviour; `app/Http/{WebApi,CliApi,Shared}` owns transport.
  Full boundaries: `.claude/rules/architecture.md` (path-scoped, loads on `app/**`,
  `routes/**`, `database/**`, `config/**`).
- `resources/js` is FSD — app / pages / widgets / entities / shared. Details:
  `.claude/rules/frontend.md`.
- Backend and frontend contracts are one change: Resources, request shapes, and frontend types
  stay aligned when both sides are in scope.
- Cross-cutting mechanics have their own docs, and changing one without reading it breaks the
  others: `docs/filtering-system.md`, `docs/sorting-system.md`, `docs/include-system.md`,
  `docs/persisted-list-state.md`.

## Gotchas

- Tests run **on the host**, not in a container: `docker compose` here provides only Postgres,
  Redis and MinIO. The backend suite needs the separate `task_manager_test` database —
  `docs/testing.md` has the setup and the recreate script.
- `npm run test:e2e` reseeds the e2e database. Run it only when the user asks.
- `.claude/settings.json` and `.claude/hooks/` enforce mechanical restrictions and automatic
  formatting. Never work around a blocked action — say what was blocked and why it is needed.

## External systems

- **CLI API** — an agent-facing public contract. Its other side is not in this repository, so
  changes to it are review-worthy by default.
- **Project Office** — the task board. When a request is attached to a task, read
  `.project-office/AGENTS.md` and use its CLI for task context, checkpoints, and handoff.

## Conventions this project actually follows

Named exemplar files per layer live in `.claude/composable-pipeline/project-profile.yml`, which
the implementation step reads. Verification policy: `.claude/rules/testing.md`.
