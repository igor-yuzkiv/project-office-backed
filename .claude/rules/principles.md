# Rule: General principles

Principles for any work in this repository — code, docs, plans, task proposals, even
reorganizing these rules.

## Clarify before acting

Ask first for decisions about product behavior, business logic, or external contracts — not
for small local implementation choices.

Stop and ask when:

- Requirements, acceptance criteria, or expected behavior are missing or ambiguous.
- Multiple reasonable approaches exist and the task does not specify which.
- The task implies business logic, validation rules, or domain behavior that is not stated.
- An architectural decision is needed (new abstraction, new layer, data model change, API contract).
- A DTO structure, API shape, UI state, or data flow is not defined.
- The change could affect behavior outside the explicitly requested scope.
- Existing code contradicts the task description.

Proceed without asking (investigate, decide, implement) when:

- The decision is a low-blast-radius local technical choice (naming, file placement, a small helper extraction, internal control flow).
- The scope is obvious and the change stays within it.
- A project convention already answers the question.

Never:

- Invent business logic, validation, or domain behavior not specified in the task.
- Choose between materially different product/contract approaches without surfacing them.
- Treat an assumption as a fact — surface it explicitly.

## Change strategy

Prefer minimal, surgical changes:

- Default to the smallest change that solves the requested problem.
- Preserve existing architecture, patterns, naming, and conventions unless the task explicitly requests refactoring.
- Avoid opportunistic cleanup or unrelated "while I am here" refactors.
- Minimize file count, diff size, and blast radius.
- Before creating a new abstraction, component, composable, DTO, query, mutation, service, or
  utility: search for an existing implementation and prefer extending it; follow the naming
  and structure already present. Do not introduce a large abstraction until there are at
  least two real use cases, and do not design for hypothetical future needs.
- Do not add layers, services, managers, factories, or helpers just to look "architectural" —
  solve the current problem; prefer explicit flow over generic, configuration-driven behavior.
- Do not rename, move, reorganize, or replace things unless required.
- When larger refactoring seems beneficial, propose it separately instead of doing it automatically.
- Do not silently expand scope after an approved plan or reviewed artifact.

Decision priority: correctness → minimal change → consistency with the codebase →
maintainability → architectural improvements (only when requested).

## KISS / simple-first

Keep implementations simple, direct, and easy to review. Solve the current problem without
speculative architecture, generic abstractions, or future-proofing unless the task requires
it. Prefer boring, readable code over clever code.

- Reuse existing project patterns when they fit.
- Do not add layers, services, managers, factories, or helpers just to look "architectural".
- Extract a function only when it makes the code easier to read, test, or reuse right now.
- Prefer explicit flow over generic, configuration-driven behavior.
- Do not introduce a large abstraction until there are at least two real use cases; do not design a framework around hypothetical future commands.
- Do not prepare for imaginary future requirements — leave the code easy to change later.

A good change should be understandable from the diff without a map, a compass, and a senior
architect.
