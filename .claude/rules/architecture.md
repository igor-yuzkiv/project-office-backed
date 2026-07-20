---
paths:
  - "app/**"
  - "routes/**"
  - "database/**"
  - "config/**"
  - "bootstrap/providers.php"
---

# Backend architecture

This document describes the architecture established in this repository. Preserve the current
structure of the area being changed; do not reorganize working code to match a generic Laravel
template.

## System context

- Laravel 13 API backend on PHP 8.3.
- `WebApi` serves the Vue application through `/api`.
- `CliApi` exposes a smaller agent-facing API through `/api/cli`.
- PostgreSQL is the primary store. Redis, Scout, Horizon, Reverb, and S3-compatible attachment
  storage provide supporting infrastructure.

## Top-level responsibilities

```text
app/
|- Domains/         business capabilities grouped by entity and operation
|- Http/
|  |- WebApi/       controllers, requests, and resources owned by the SPA API
|  |- CliApi/       controllers, requests, and resources owned by the agent-facing API
|  `- Shared/       HTTP shapes genuinely shared by both APIs
|- Infrastructure/ shared framework integration, providers, DTOs, models, and value objects
|- Libs/            reusable technical modules
`- Support/         small generic utilities
```

- Keep business behavior in its owning domain.
- Keep API-specific transport behavior in the relevant HTTP surface.
- Keep Infrastructure generic; do not move domain behavior into it merely for reuse.
- Do not use Support as a default location for code whose ownership is unclear.

## Domain actions

Business operations live under `app/Domains/{Entity}/Actions/{Verb}{Entity}/` and use:

```text
{Verb}{Entity}Handler.php   operation entry point and business behavior
{Verb}{Entity}Command.php   immutable input data
{Verb}{Entity}DTO.php       optional output or intermediate data
```

- A Handler exposes `handle()` with exactly one Command argument.
- A Command contains input data and no business behavior.
- Even a single-model operation uses a Command rather than accepting the model directly.
- Queries, Services, Events, Jobs, Enums, and ValueObjects may be added within the owning domain
  when their responsibility is real and current.
- Do not introduce a new pattern when the nearest domain already provides a suitable one.

## HTTP boundaries

- WebApi and CliApi own separate controllers. Never share a controller across the surfaces.
- Controllers validate through Form Requests, delegate writes and business operations to domain
  Handlers, and return Resources or explicit JSON responses.
- Read-only list and search composition may remain in controllers where that is the established
  pattern; extract it when complexity or reuse creates a real query boundary.
- Requests and Resources may be surface-owned or live in `Http/Shared` when both APIs genuinely use
  the same shape.
- When one surface needs a variation of a shared Request or Resource, extend or specialize it in
  that surface rather than changing the shared contract for the other consumer.

Treat the CLI API as a public automation contract. Changes to its task workflow, payloads, route
semantics, or status transitions require the Controlled pipeline and focused compatibility tests.

## Cross-cutting entities

Universal entities such as Comment, Tag, and Attachment do not reference their consumers.

- A universal controller operates on the universal entity by its own ID.
- Consumer-scoped operations belong to the consumer's controller and domain flow, such as
  `TaskCommentsController` or `ProjectDocumentAttachmentsController`.
- When a second consumer needs similar behavior, prefer clear consumer ownership over a premature
  shared abstraction.

## Persistence and module wiring

- Eloquent models live under the owning domain's `Models` directory and use the `Model` suffix.
- Create a new migration for every schema change. Existing migrations are append-only.
- Schema and data changes use the Controlled pipeline.
- Bind interfaces, storage implementations, policies, listeners, and commands through the owning
  service provider.
- Register application providers in `bootstrap/providers.php`.
- Prefer constructor injection when practical; avoid introducing service-locator calls into new
  domain code.

## Contract boundaries

Treat these as explicit contracts:

- Web API and CLI API routes, authentication, validation, and payloads;
- Form Request to Command mapping;
- Resources and frontend TypeScript response types;
- task workflow statuses, checkpoint and handoff semantics;
- database schemas and stored representations;
- events, queued payloads, and storage interfaces.

Analyze all known consumers before changing a contract. Contract changes normally require the
Controlled pipeline and focused tests for each affected surface.

## What counts as an architecture decision

An architecture decision introduces or moves a boundary, changes dependency direction, creates a
shared abstraction, changes persistence ownership, or changes how the Web API, CLI API, frontend,
or a domain collaborate. A local implementation choice inside an established pattern is not
automatically architectural.
