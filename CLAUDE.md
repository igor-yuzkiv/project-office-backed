# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working in this repository.

> This repository is connected to a **project office** (a Backlog.md workspace for its
> tasks, drafts, milestones and docs). Office rules for this repo: @.project-office/AGENTS.md

## Language

Match the user's language. When the user writes in Ukrainian, respond in Ukrainian — in
chat, in summaries, and in any artifact assembled for the user's review (plans, task
proposals, review-gate documents). Keep code, identifiers, file paths, CLI commands, and
technical terms in their original form; translate the prose around them, not the tokens.

## What this is

A task manager built as a **Laravel 12 API backend** (`app/`) with a **Vue 3 + TypeScript
SPA frontend** (`resources/js/`). The backend owns domain logic and returns JSON; the
frontend consumes it via TanStack Vue Query.

## Workspace Layout

Project root: `/var/www/task-manager/mvp-task-manager`

| Area | Path |
| --- | --- |
| Backend source | `app/` |
| Frontend source | `resources/js/` |
| Routes | `routes/` |
| Database migrations | `database/` |
| Tests | `tests/` |
| Project docs | `docs/` |
| Docker setup | `docker-compose.yml`, `docker/` |

## Git Safety

- Never create commits automatically.
- Never push changes automatically.
- Never perform merge, rebase, or reset operations without explicit user confirmation.

## Validation

Validation is proportional to the change — prefer targeted checks over expensive
project-wide validation. The workflow and the concrete tooling commands live in the rule
files below (`development-workflow.md`, `backend.md`, `frontend.md`).

## Project rules

- `development-workflow.md` — task workflow phases and how to approach changes (always loaded).
- `conventions.md` — code style and cross-cutting entity rules (always loaded).
- `review-gate.md` — review gate for assembled artifacts needing approval (always loaded).
- `backend.md` — Laravel domain/HTTP architecture, conventions, and tooling (scoped to `app/**`, `routes/**`, `database/**`, `tests/**`).
- `frontend.md` — Vue/FSD architecture, conventions, and tooling (scoped to `resources/js/**`).
