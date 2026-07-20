# Project Office

Guidance for Claude Code when working in this repository.

## Project

- Laravel 13 API backend and Vue 3 + TypeScript single-page application.
- The Web API serves the SPA; a separate CLI API is an agent-facing public contract.
- Backend domain logic lives in `app/Domains/`. Frontend source lives in `resources/js/`.
- PostgreSQL, Redis, Reverb, Horizon, Scout, and S3-compatible attachment storage are part of the
  application environment.

## Working model

- Classify each task using `.claude/rules/workflow.md`, then follow that pipeline autonomously.
- Investigate the nearest implementation and tests before making assumptions or asking the user.
- Ask only when a decision affects business behavior, architecture, data, an external contract,
  user-visible interaction, or the agreed scope.
- Verification is part of implementation. Run relevant checks and fix in-scope failures before
  handing work back.
- The user reviews the final diff and visually verifies UI changes. Do not add a separate final
  approval gate or mandatory agent review.
- Do not commit, push, migrate data, or perform destructive git operations on your own.

## Project rules

- `.claude/rules/principles.md` — decision-making and change discipline; always loaded.
- `.claude/rules/workflow.md` — risk-based pipelines, escalation, and handoff; always loaded.
- `.claude/rules/architecture.md` — Laravel architecture and API boundaries; path-scoped.
- `.claude/rules/frontend.md` — Vue/FSD architecture and conventions; path-scoped.
- `.claude/rules/testing.md` — backend, frontend, and E2E verification policy; path-scoped.

Mechanical restrictions and automatic formatting live in `.claude/settings.json` and
`.claude/hooks/`. Never work around a blocked action. Explain what was blocked and why it is
needed.

## Project Office task workflow

When a request is attached to a Project Office task, read `.project-office/AGENTS.md` and use its
CLI workflow for task context, durable checkpoints, and handoff. Project Office records the work;
the development pipeline in `.claude/rules/workflow.md` governs how the work is performed.

## Required handoff

Every completed task ends with a concise handoff containing:

- what was implemented and the reason for the chosen approach;
- the main files changed;
- tests and checks run, with their results;
- anything not verified and why;
- UI behavior that still needs the user's visual verification, when applicable;
- remaining risks, assumptions, or follow-up work, when relevant.
