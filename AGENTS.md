# Decision Making

### Clarify Before Acting

- Never assume missing requirements, business rules, naming conventions, expected behavior, or architecture decisions.
- If any part of the task is ambiguous, incomplete, conflicting, or missing critical implementation details — stop and ask clarifying questions before making changes.
- Do not invent requirements, hidden business logic, future requirements, or implementation details.
- Do not silently choose between multiple valid approaches without confirmation.
- Explicitly surface assumptions instead of treating them as facts.

Examples of situations that require clarification:
- Multiple reasonable implementation approaches exist
- Expected behavior is unclear
- Existing code and task description conflict
- Missing API contracts, DTO structure, UI states, acceptance criteria, or business rules
- A change may impact existing flows outside the requested scope


# Change Strategy

### Prefer Minimal / Surgical Changes

- Default to the smallest possible change that solves the requested problem.
- Preserve existing architecture, patterns, naming, and project conventions unless the task explicitly requests refactoring.
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


### Backend Domain Structure

- Backend domain code should be organized by domain entity and business operation.
- Use the following structure as the default convention:

```text
Domain/
└── Entity/
    ├── Actions/
    │   └── CreateEntity/
    │       ├── CreateEntityHandler.php
    │       ├── CreateEntityCommand.php # optional
    │       └── CreateEntityDTO.php     # optional
    ├── Queries/
    ├── Models/
    ├── Events/
    ├── Jobs/
    ├── Enums/
    └── ValueObjects/
```
