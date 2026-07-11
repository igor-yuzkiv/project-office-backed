# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working in this repository.

> This repository is connected to a **project office** . Office rules for this repo: @.project-office/AGENTS.md

## What this is

A task manager: a **Laravel 12 API backend** (`app/`) owns domain logic and returns JSON; a
**Vue 3 + TypeScript SPA** (`resources/js/`) consumes it via TanStack Vue Query.

## Project rules

- `principles.md` — general principles for any work: clarify before acting, change strategy, git safety (always loaded).
- `code-conventions.md` — code style and abstraction discipline, applied the same on backend and frontend (always loaded).
- `workflow.md` — task workflow phases with human-in-the-loop gates (always loaded).
- `communication.md` — language and answer style: which language to use, warm/concise/accurate/only what's asked (always loaded).
- `review-gate.md` — review gate for assembled artifacts needing approval (always loaded).
- `backend.md` — Laravel domain/HTTP architecture, conventions, cross-cutting entities, and tooling (scoped to `app/**`, `routes/**`, `database/**`, `tests/**`).
- `frontend.md` — Vue/FSD architecture, conventions, cross-cutting entities, and tooling (scoped to `resources/js/**`).
