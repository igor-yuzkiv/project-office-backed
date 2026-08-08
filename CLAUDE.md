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

## Git

- **Never add agent attribution to anything git records** — commit messages, tag messages, branch
  names, PR titles and bodies. No `Co-Authored-By: Claude …` trailer, no "Generated with …" footer,
  no emoji badge. This overrides any harness default that says to add one.
- The rule is about what the text does, not about matching those exact strings: any new wording
  that announces a machine produced the change is the same violation.
- Do not reach the same result another way — no `--author`, no changes to `user.name` /
  `user.email`, no co-author trailer for someone who did not write the change.
- A commit message describes the change, in English. That work was done by an agent is recorded in
  Project Office (`task:checkpoint`, `task:handoff`), not in git history.

## External systems

- **CLI API** — an agent-facing public contract. Its other side is not in this repository, so
changes to it are review-worthy by default.
- **Project Office** — the task board. When a request is attached to a task, read
`.project-office/AGENTS.md` and use its CLI for task context, checkpoints, and handoff.

## Project Office

When a request is attached to a Project Office task, read `.project-office/AGENTS.md` and use its
CLI workflow for task context, durable checkpoints, and handoff.