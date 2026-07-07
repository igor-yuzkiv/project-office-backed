# Rule: Development workflow

How to approach changes in this repository. The Git safety rules and the workspace
layout live in `CLAUDE.md` and are not repeated here.

## Workflow phases

Every task moves through the phases below in order. The principles further down
(`Clarify before acting`, `Change strategy`, `Existing code first`, `Incremental
implementation`, `Do not`) govern how each phase is carried out.

### 1. Intake and analysis

Requirements may arrive from a task tracker or directly in chat. Treat both as the
source of what needs to be built.

Before writing code, understand:

- the requested behavior;
- the affected scope (backend, frontend, or both);
- the existing contracts and constraints (API shapes, DTOs, component props);
- the risks and possible side effects.

When requirements are incomplete or ambiguous, resolve them per `Clarify before acting`
before moving on.

### 2. Implementation

Implement the requested change with the minimal necessary edits, following `Change
strategy`, `Existing code first`, and `Incremental implementation`, and respecting
`Do not`. Stay inside the requested scope, no opportunistic refactoring or unrelated
cleanup, and change contracts only when the task requires it.

Prefer incremental changes that are easy to review and verify.

### 3. Validation and review

Run validation proportional to the change (see `Validation gate` below).

After implementation, run an independent code review when the agent environment provides
such a mechanism. The review must be independent from the implementation context: use a
separate subagent, review lane, or equivalent mechanism when available.

After review, report briefly:

- what was changed;
- what was verified;
- important risks or assumptions;
- anything that still needs attention.

At the end of the report, explicitly ask the user whether any corrections are needed. Do
not move to the documentation phase until the user either confirms that no corrections are
needed or provides corrections and they are handled.

### 4. Documentation

Documentation is a separate phase after implementation, validation, review, reporting, and
user correction approval.

Before writing or updating documentation, explicitly ask the user whether documentation is
needed by using `AskUserQuestion`. Do not create or update documentation unless the user
confirms it.

When documentation is requested:

- decide whether to create a new document or update an existing one;
- keep all project documentation under `docs/`;
- do not add documentation outside `docs/` unless the user explicitly asks for it.

## Validation gate

Validation is proportional to the change. Prefer targeted checks over expensive
project-wide validation. The concrete commands live in the scoped rules
(`backend.md`, `frontend.md`).

- Small backend change → Pint + PHPStan.
- Small frontend change → Format + Lint + Type Check.
- Feature work → run the relevant tests when available.

Do not run browser-based verification for frontend changes — the user verifies the UI
manually.

## Principles

### Clarify before acting

Ask first for decisions about product behavior, business logic, or external contracts —
not for small local implementation choices.

Stop and ask when:

- Requirements, acceptance criteria, or expected behavior are missing or ambiguous.
- Multiple reasonable implementation approaches exist and the task does not specify which.
- The task implies business logic, validation rules, or domain behavior that is not stated.
- An architectural decision is needed (new abstraction, new layer, data model change,
  API contract).
- A DTO structure, API shape, UI state, or data flow is not defined.
- The change could affect behavior outside the explicitly requested scope.
- Existing code contradicts the task description.

Proceed without asking (investigate, decide, implement) when:

- The decision is a low-blast-radius local technical choice (naming, file placement, a
  small helper extraction, internal control flow).
- The scope is obvious and the change stays within it.
- A project convention already answers the question.

Never:

- Invent business logic, validation, or domain behavior not specified in the task.
- Choose between materially different product/contract approaches without surfacing them.
- Treat an assumption as a fact — surface it explicitly.

Examples that require clarification:

- Task says "add filtering" but does not define which fields, operators, or default state.
- Task says "handle errors" but does not specify error types, messages, or fallback behavior.
- Task requires a new API endpoint but does not define the request/response shape.
- Task touches a shared abstraction and it is unclear whether to extend or replace it.
- Task description and existing code disagree on expected behavior.

### Change strategy

Prefer minimal, surgical changes:

- Default to the smallest change that solves the requested problem.
- Preserve existing architecture, patterns, naming, and conventions unless the task
  explicitly requests refactoring.
- Avoid opportunistic cleanup or unrelated "while I am here" refactors.
- Minimize file count, diff size, and blast radius.
- Do not rename, move, reorganize, or replace components unless required.
- When larger refactoring seems beneficial, propose it separately instead of doing it
  automatically.

Decision priority: correctness → minimal change → consistency with the codebase →
maintainability → architectural improvements (only when requested).

### Existing code first

Before creating a new abstraction, component, composable, DTO, query, mutation, service,
or utility:

- Search the codebase for an existing implementation.
- Prefer extending existing patterns over creating new ones.
- Follow naming and structure already present in the project.
- Do not introduce a new pattern when an established project pattern already exists.

### Incremental implementation

- Prefer incremental implementation.
- Do not introduce a large abstraction until there are at least two real use cases.
- Do not design a framework around hypothetical future needs.
- If a convention is unclear, choose the simplest option that keeps boundaries clean and
  can be changed later without drama.

### Do not

- Do not add speculative abstractions because they might be useful later.
- Do not silently expand scope after an approved plan or reviewed artifact.
