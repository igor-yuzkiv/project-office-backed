# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working in this repository.

> Connected to a **project office** — its task workflow and CLI conventions live in
> `.project-office/AGENTS.md` (loaded on demand by the `project-office` skill).

## What this is

A task manager: a **Laravel 12 API backend** (`app/`) owns domain logic and returns JSON; a
**Vue 3 + TypeScript SPA** (`resources/js/`) consumes it via TanStack Vue Query.

## Enforcement

Some actions are blocked mechanically (`.claude/settings.json`) — git writes, schema
migrations, and rewriting existing shared test infrastructure. Pint runs automatically
on edited `app/**` files; do not run it yourself.

When an action is blocked: stop and surface it. Do not look for an alternative route
around the block, and do not redesign the change to avoid it silently — report what you
needed to do and why.

## Project rules

- `principles.md` — general principles for any work: clarify before acting, change strategy,
  abstraction discipline, anti-over-engineering (always loaded).
- `workflow.md` — two human checkpoints (plan approval, final diff review) and the scope
  contract (always loaded).
- `backend.md` — Laravel domain/HTTP architecture, conventions, cross-cutting entities, and tooling (scoped to `app/**`, `routes/**`, `database/**`, `tests/**`).
- `frontend.md` — Vue/FSD architecture, conventions, cross-cutting entities, and tooling (scoped to `resources/js/**`).
