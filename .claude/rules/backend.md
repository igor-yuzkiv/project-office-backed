---
paths:
  - "app/**"
  - "routes/**"
  - "database/**"
  - "tests/**"
---

# Rule: Backend architecture and conventions

Laravel 12 application. PHP. Backend source lives in `app/`.

## Architecture

```
app/
├── Domains/          # domain logic, organized by entity
├── Http/             # controllers, form requests, API resources
├── Infrastructure/   # service providers, shared DTOs, model concerns
└── Support/          # generic utilities
```

## Domain Layer

Domain code lives in `app/Domains/{Entity}/` and is organized by business operation.

```
app/Domains/
└── {Entity}/
    ├── Actions/
    │   └── {Verb}{Entity}/
    │       ├── {Verb}{Entity}Handler.php   # entry point, contains business logic
    │       ├── {Verb}{Entity}Command.php   # input DTO
    │       └── {Verb}{Entity}DTO.php       # output or intermediate DTO (optional)
    ├── Models/
    ├── Queries/
    ├── Services/
    ├── Events/
    ├── Jobs/
    ├── Enums/
    └── ValueObjects/
```

Conventions:

- `Handler` is the single entry point for an action. It receives a `Command` and executes
  business logic.
- `handle()` always takes exactly one `Command` argument — no exceptions. Even a handler
  that only needs a model to delete wraps it in a `Command` (e.g. `DeleteTaskCommand { public
  readonly TaskModel $task }`) instead of accepting the model directly.
- `Command` is a plain input DTO — no behavior, only data.
- Controllers delegate to handlers — no business logic in controllers.
- Models extend Eloquent and live in `Models/`. Use the `ModelName` suffix
  (e.g., `ProjectModel`).

## HTTP Layer

The HTTP layer is split into two independent APIs plus a cross-API shared layer:

- `app/Http/WebApi/` — the web application API (routes: `routes/api.php`).
- `app/Http/CliApi/` — the agent-facing CLI API (routes: `routes/api-cli.php`).
- `app/Http/Shared/` — `Requests` and `Resources` shared across both APIs.

Each API owns its `Controllers`, and may own its `Requests` and `Resources`, grouped by
entity:

```
app/Http/
├── WebApi/
│   ├── Controllers/{Entity}/
│   ├── Requests/{Entity}/
│   └── Resources/{Entity}/
├── CliApi/
│   ├── Controllers/{Entity}/
│   ├── Requests/{Entity}/     # add per API as needed
│   └── Resources/{Entity}/    # add per API as needed
└── Shared/
    ├── Requests/
    └── Resources/
```

### API boundaries

- **Controllers are fully API-owned.** `WebApi` and `CliApi` never share controllers —
  each API has its own, even for the same entity. A controller lives under exactly one
  API namespace.
- **Requests and Resources may be API-owned or shared.** Each API may define its own
  `Requests`/`Resources`, or reuse a shared one from `Http/Shared/`.
- **`Http/Shared/`** holds `Requests` and `Resources` common to both APIs. Put a class
  here only when both APIs genuinely use the same shape.
- **An API may extend a shared class to override it.** When an API needs most of a shared
  `Request`/`Resource` but must change part of it, extend the `Http/Shared/` class in the
  API's own namespace and override only what differs — do not fork the whole class.

Conventions (both APIs):

- Controllers are thin: validate via `FormRequest`, delegate to a domain `Handler`,
  return a `Resource`.
- `FormRequest` classes handle all input validation.
- `Resource` classes transform models into the API response shape.

## Cross-cutting entities

Universal entities (`Comment`, `Tag`, `Attachment`) never reference their consumers — a
universal controller operates on the entity by its own ID only.

- Operations scoped to a consuming entity belong in that entity's controller:
  `GET /tasks/{task}/comments` → `TaskCommentsController`, not `CommentController`.
- A universal controller must not receive a consuming-entity model (no `TaskModel`
  parameter in `CommentController`).
- When another entity needs comments, it gets its own set (`ProjectCommentsController`).
  Duplication across consumers is preferred over a shared abstraction.

## Tooling

| Task | Command |
| --- | --- |
| Format code | `./vendor/bin/pint` |
| Static analysis | `./vendor/bin/phpstan analyse` |
| Run all tests | `php artisan test` |
| Run specific test | `php artisan test --filter=TestName` |

Run `./vendor/bin/phpstan analyse` before considering backend work complete. Pint runs
automatically on edited `app/**` files (PostToolUse hook). Run relevant tests after changes.

PHPStan config: `phpstan.neon`. Current level: 5. When adding new models with enum casts,
declare the cast types via `@property` PHPDoc on the model class so PHPStan can resolve them.
