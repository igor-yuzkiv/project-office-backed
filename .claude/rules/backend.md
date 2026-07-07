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
    │       ├── {Verb}{Entity}Command.php   # input DTO (optional)
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
- `Command` is a plain input DTO — no behavior, only data.
- Controllers delegate to handlers — no business logic in controllers.
- Models extend Eloquent and live in `Models/`. Use the `ModelName` suffix
  (e.g., `ProjectModel`).

## HTTP Layer

```
app/Http/
├── Controllers/{Entity}/   # one controller per entity, grouped by entity
├── Requests/{Entity}/      # form request validation classes
└── Resources/{Entity}/     # API resource transformers
```

Conventions:

- Controllers are thin: validate via `FormRequest`, delegate to a domain `Handler`,
  return a `Resource`.
- `FormRequest` classes handle all input validation.
- `Resource` classes transform models into the API response shape.

## Cross-cutting entities

Universal entities (`Comment`, `Tag`, `Attachment`) must not know about their consumers.
Operations scoped to a consuming entity belong in that entity's controller
(`TaskCommentsController`, not `CommentController`). See `conventions.md` for the full rule.

## Tooling

| Task | Command |
| --- | --- |
| Format code | `./vendor/bin/pint` |
| Static analysis | `./vendor/bin/phpstan analyse` |
| Run all tests | `php artisan test` |
| Run specific test | `php artisan test --filter=TestName` |

Run `./vendor/bin/pint` and `./vendor/bin/phpstan analyse` before considering backend work
complete. Run relevant tests after changes.

PHPStan config: `phpstan.neon`. Current level: 5. When adding new models with enum casts,
declare the cast types via `@property` PHPDoc on the model class so PHPStan can resolve them.
