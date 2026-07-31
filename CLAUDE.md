# Project Office

Guidance for Claude Code when working in this repository.

<!-- composable-pipeline:begin -->
## How work is done here

Route the work before loading detail:

- executable change → invoke `/composable-pipeline:run-task` before the first edit, including when
  planning turns into execution in the same session;
- unsettled approach or later execution → `/composable-pipeline:plan-work`;
- review-only request → `/composable-pipeline:review`; executable work stays in `run-task`, which
  decides whether its risk warrants an independent review.

Across all three:

- deliver the requested scope; report adjacent work instead of silently adding it;
- make routine local decisions, but surface choices that materially change behaviour, scope,
  contracts, security, data, or lasting trade-offs;
- distinguish verified facts from inference;
- report partial or unverified work as partial or unverified.

The rules, principles, coverage lenses, and operations those pipelines draw on ship with the
composable-pipeline plugin; this repository does not keep its own copy.

Project-specific artifact destinations, exemplars, verification commands, and language are in
`.claude/composable-pipeline/project-profile.yml`.
<!-- composable-pipeline:end -->

## Project

- The Web API serves the SPA; a separate CLI API is an agent-facing public contract.

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
`/composable-pipeline:run-task` governs how the work is performed.
