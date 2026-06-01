# Decision Making

### Clarify Before Acting

Stop and ask before making changes when any of the following is true:

- Requirements, acceptance criteria, or expected behavior are missing or ambiguous.
- Multiple reasonable implementation approaches exist and the task does not specify which to use.
- The task implies business logic that is not explicitly stated.
- An architectural decision is needed (new abstraction, new layer, data model change, API contract).
- The change could affect behavior outside the explicitly requested scope.
- Existing code contradicts the task description.
- A DTO structure, API shape, UI state, or data flow is not defined.

Never:

- Invent business logic, validation rules, or domain behavior not specified in the task.
- Choose between multiple valid approaches without surfacing the options and asking.
- Treat an assumption as a fact — surface it explicitly.
- Proceed with implementation when scope is unclear, even if a reasonable guess exists.

Examples of situations that require clarification:

- Task says "add filtering" but does not define which fields, operators, or default state.
- Task says "handle errors" but does not specify error types, messages, or fallback behavior.
- Task requires a new API endpoint but does not define the request/response shape.
- Task touches a shared abstraction and it is unclear whether to extend or replace it.
- Task description and existing code disagree on expected behavior.

# Change Strategy

### Prefer Minimal / Surgical Changes

- Default to the smallest possible change that solves the requested problem.
- Preserve existing architecture, patterns, naming, and project conventions unless the task explicitly requests
  refactoring.
- Avoid opportunistic cleanup, unrelated refactors, or "while I am here" improvements.
- Minimize file count, diff size, and blast radius.
- Prefer extending existing abstractions before introducing new layers.
- Do not rename, move, reorganize, or replace components unless required.
- When larger refactoring seems beneficial — propose it separately instead of performing it automatically.

Decision priority:

1. Correctness
2. Minimal change
3. Consistency with existing codebase
4. Maintainability
5. Architectural improvements (only when requested)

# Code Organization

### Prefer Self-Documenting Code

- Add comments only when intent, constraints, or non-obvious business logic cannot be understood from the code itself.
- Prefer expressive naming, extracted functions, and clear structure over explanatory comments.
- Introduce explanatory variables and extracted functions when they improve readability.
- Prefer intention-revealing names over short or generic names.
- Prefer slightly longer names when they make code easier to understand.
- Avoid abbreviations unless they are already established in the project domain.
- Do not add comments that restate what the code already expresses.
- Preserve existing comments unless they are incorrect or obsolete.
- Comments should explain **why**, not **what**.

# Project Context

### Workspace Layout

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
| Project office | `.project_office/` |

### Agent Workflow

Before starting any task, read `.project_office/agent_guides/agent_workflow.md`.

It defines:
- how to locate and read the current milestone and task;
- when to ask before implementing;
- how to handle task documentation and reviews;
- constraints on scope and progression.

# Backend

Laravel 12 application. PHP.

### Architecture

```
app/
├── Domains/          # domain logic, organized by entity
├── Http/             # controllers, form requests, API resources
├── Infrastructure/   # service providers, shared DTOs, model concerns
└── Support/          # generic utilities
```

### Domain Layer

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

- `Handler` is the single entry point for an action. It receives a `Command` and executes business logic.
- `Command` is a plain input DTO — no behavior, only data.
- Controllers delegate to handlers — no business logic in controllers.
- Models extend Eloquent and live in `Models/`. Use `ModelName` suffix (e.g., `ProjectModel`).

### HTTP Layer

```
app/Http/
├── Controllers/{Entity}/   # one controller per entity, grouped by entity
├── Requests/{Entity}/      # form request validation classes
└── Resources/{Entity}/     # API resource transformers
```

Conventions:

- Controllers are thin: validate via `FormRequest`, delegate to domain `Handler`, return `Resource`.
- `FormRequest` classes handle all input validation.
- `Resource` classes transform models into the API response shape.

### Tooling

| Task | Command |
| --- | --- |
| Format code | `./vendor/bin/pint` |
| Static analysis | `./vendor/bin/phpstan analyse` |
| Run all tests | `php artisan test` |
| Run specific test | `php artisan test --filter=TestName` |

Run `./vendor/bin/pint` and `./vendor/bin/phpstan analyse` before considering backend work complete. Run relevant tests after changes.

PHPStan config: `phpstan.neon`. Current level: 5. When adding new models with enum casts, declare the cast types via `@property` PHPDoc on the model class so PHPStan can resolve them.

# Frontend

Vue 3 + TypeScript + Vite. UI: PrimeVue + Tailwind CSS.

### Architecture

The frontend uses a Feature-Sliced Design inspired layered structure under `resources/js/`.

```
resources/js/
├── app/              # app-level setup
├── pages/            # route-level page components
├── widgets/          # complex self-contained UI blocks
├── entities/         # domain entity modules
└── shared/           # cross-cutting code
```

### Layer Reference

**`app/`** — application bootstrap and shell.

```
app/
├── config/           # app-level config constants
├── plugins/          # Vue plugins (PrimeVue, Vue Query, Laravel Echo)
├── router/           # Vue Router setup
├── shell/            # layouts, header, navigation sidebar
└── stores/           # app-level Pinia stores (auth, layout)
```

**`pages/`** — one file per route. Thin: compose widgets and entities, no business logic.

**`widgets/`** — complex UI blocks with own composables and sub-components. Not reused across entities.

```
widgets/{feature}/
├── ui/               # Vue components
├── composables/      # widget-local composables
└── index.ts          # public API
```

**`entities/`** — domain entity modules. Each entity owns its API layer, types, queries, and mutations.

```
entities/{entity}/
├── api/              # API call functions
├── types/            # TypeScript types for the entity
├── queries/          # TanStack Vue Query query composables
├── mutations/        # TanStack Vue Query mutation composables
├── config/           # entity-level constants (query keys, route names, etc.)
└── index.ts          # public API (re-exports)
```

**`shared/`** — truly reusable, entity-agnostic code.

```
shared/
├── api/              # HTTP client, error types
├── components/       # generic UI components (buttons, etc.)
├── composables/      # generic composables (toast, confirm dialog, etc.)
├── types/            # shared TypeScript types (pagination, result, etc.)
└── utils/            # pure utility functions
```

### Component and Library Usage

Prefer components and utilities from already-installed packages over writing custom implementations.

- Always check installed packages first (PrimeVue, VueUse, etc.) before implementing anything from scratch.
- Never install new packages independently — propose them if needed and wait for confirmation.
- Customize existing components to match the design (via props, slots, CSS overrides, PrimeVue `pt` API) rather than recreating them.
- Create a custom component only as a last resort, when no installed package covers the need.

This restriction does not apply to:
- Wrapper components that adapt a library component to project conventions.
- Composition components that combine multiple existing components into a reusable block.

### Key Libraries

| Library | Purpose |
| --- | --- |
| PrimeVue | UI component library |
| Tailwind CSS | Utility-first styling |
| TanStack Vue Query | Server state management (queries, mutations, caching) |
| Pinia | Client state management |
| Zod | Schema validation and type inference |
| Vue Router | Client-side routing |
| VueUse | Composable utilities |
| Axios | HTTP client |

### Tooling

| Task | Command |
| --- | --- |
| Format code | `npm run format` |
| Check formatting | `npm run format:check` |
| Lint and auto-fix | `npm run lint` |
| Check linting | `npm run lint:check` |
| Type check | `npm run types:check` |
| Build | `npm run build` |

Run `npm run format` and `npm run lint` before considering frontend work complete.
Run `npm run types:check` after changes that touch types or interfaces.
