# Rule: Code conventions

Conventions for writing code, applied the same way on backend and frontend. General work
principles live in `principles.md`; area architecture, tooling, and cross-cutting entity
placement live in `backend.md` / `frontend.md`.

## Code style

Prefer **self-documenting code**: the code should explain itself before comments are needed.

- Prefer intention-revealing names over short or generic ones; a slightly longer name is fine when it aids understanding. Avoid abbreviations unless established in the project domain.
- Introduce explanatory variables and extracted functions when they improve readability.
- Do not add comments that restate what the code already expresses.
- Preserve existing comments unless they are incorrect or obsolete.
- Comments explain **why**, not **what** — non-obvious intent, constraints, trade-offs, external behavior, or a decision that would otherwise look strange.

## Creating abstractions

Before creating a new abstraction, component, composable, DTO, query, mutation, service, or
utility:

- Search the codebase for an existing implementation and prefer extending it.
- Follow naming and structure already present in the project.
- Do not introduce a new pattern when an established project pattern already exists.

Keep abstractions incremental:

- Do not introduce a large abstraction until there are at least two real use cases.
- Do not design a framework around hypothetical future needs.
- Do not add speculative abstractions because they might be useful later.
- If a convention is unclear, choose the simplest option that keeps boundaries clean and can be changed later without drama.
