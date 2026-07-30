# Project Office

Guidance for Claude Code when working in this repository.

<!-- composable-pipeline:begin -->
## How work is done here

Route the work before loading detail:

- executable change → read `.claude/blocks/pipelines/run-task.md` before the first edit, including
  when planning turns into execution in the same session;
- unsettled approach or later execution → `.claude/blocks/pipelines/plan-work.md`;
- review-only request → `/cp-review`; executable work stays in `run-task`, which decides whether
  its risk warrants an independent review.

Across all three:

- deliver the requested scope; report adjacent work instead of silently adding it;
- make routine local decisions, but surface choices that materially change behaviour, scope,
  contracts, security, data, or lasting trade-offs;
- distinguish verified facts from inference;
- report partial or unverified work as partial or unverified.

When the active pipeline does not already name what applies, use `.claude/blocks/index.yaml` to
select additional rules, principles, coverage lenses, or operations. Open only the selected blocks.
When an opened operation names `applies_rules`, open them; open its named principles only when they
materially shape the work.

Project-specific artifact destinations, exemplars, verification commands, and language are in
`.claude/project-profile.yml`.
<!-- composable-pipeline:end -->

## Project

- Laravel 13 API backend and Vue 3 + TypeScript single-page application.
- The Web API serves the SPA; a separate CLI API is an agent-facing public contract.
- Backend domain logic lives in `app/Domains/`. Frontend source lives in `resources/js/`.
- PostgreSQL, Redis, Reverb, Horizon, Scout, and S3-compatible attachment storage are part of the
  application environment.

## Working model

- Do not commit, push, migrate data, or perform destructive git operations on your own.
- The user reviews the final diff and visually verifies UI changes. Do not add a separate final
  approval gate or mandatory agent review.
- Backend and frontend contracts are one change: keep Resources, request shapes, and frontend
  types aligned when both sides are in scope.

## Project rules

- `.claude/rules/architecture.md` — Laravel architecture and API boundaries; path-scoped.
- `.claude/rules/frontend.md` — Vue/FSD architecture and conventions; path-scoped.
- `.claude/rules/testing.md` — backend, frontend, and E2E verification policy; path-scoped.

Mechanical restrictions and automatic formatting live in `.claude/settings.json` and
`.claude/hooks/`. Never work around a blocked action. Explain what was blocked and why it is
needed.

## Project Office task workflow

When a request is attached to a Project Office task, read `.project-office/AGENTS.md` and use its
CLI workflow for task context, durable checkpoints, and handoff. Project Office records the work;
the pipeline in `.claude/blocks/pipelines/run-task.md` governs how the work is performed.
