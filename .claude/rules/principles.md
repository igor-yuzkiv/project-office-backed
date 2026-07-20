# General principles

These principles apply to implementation, investigation, planning, tests, and documentation.

## Investigate before asking

First look for the answer in the request, the nearest implementation, its callers and consumers,
related tests, and repository documentation. Do not ask the user to decide low-impact technical
details already answered by local evidence or established project patterns.

Ask when the unresolved choice would materially change business behavior, architecture, stored
data, an API or UI contract, user-visible interaction, or task scope. State what is known, what is
ambiguous, and why the answer changes the implementation.

## Work autonomously within clear boundaries

Once behavior and scope are clear, choose local implementation details, make the change, run
relevant verification, and fix in-scope failures without seeking intermediate approval.

Do not silently expand scope. Stop and surface the decision when implementation requires a
materially broader change, contradicts the request, or crosses a Controlled-pipeline boundary.

## Make the smallest complete change

- Solve the full requested problem with the least unnecessary surface area.
- Include tests and contract documentation when the behavior requires them.
- Keep backend and frontend contracts aligned when both are in scope.
- Preserve existing behavior outside the requested scope.
- Avoid opportunistic cleanup, broad renaming, or unrelated refactoring.
- Improve nearby code only when necessary to make the requested change correct and robust.

Decision priority: correctness, scope, consistency, simplicity, maintainability, then architectural
improvement when architecture is explicitly in scope.

## Follow evidence, not assumptions

- Inspect the closest current implementation and tests before selecting a pattern.
- Treat proximity as evidence, not proof of a universal convention.
- Do not invent business rules, validation, UI behavior, naming conventions, or architecture.
- If code, tests, task text, design, and documentation disagree, identify the conflict instead of
  choosing the most convenient interpretation.
- State consequential assumptions in the plan or handoff.

## Preserve contracts and ownership

Do not casually change routes, request or response shapes, Commands, Resources, frontend types,
component props or emits, task workflow semantics, database representations, or cross-domain
ownership. Analyze known consumers and use the Controlled pipeline when a contract or boundary
must change.

## Prefer simple, proportionate engineering

- Reuse an existing abstraction or installed library when it fits the current need.
- Add an abstraction only when it creates a real boundary, removes meaningful duplication, or
  provides a necessary test seam now.
- Do not add layers, managers, factories, services, composables, repositories, wrappers, or
  configuration indirection for hypothetical future use.
- Prefer explicit, readable control flow over clever or generic machinery.
- Do not avoid a necessary small refactor when the alternative would leave the change fragile.

## Do not weaken verification

Fix root causes. Never make a test less meaningful, suppress a valid failure, lower a quality
threshold, alter shared test infrastructure, or change product behavior merely to obtain a green
result. If the expected behavior itself is unclear, escalate the decision.

## Respect the workspace

- Existing uncommitted changes belong to the user unless proven otherwise.
- Do not overwrite, revert, format, or reorganize unrelated work.
- Use non-destructive inspection before modifying unfamiliar areas.
- Keep generated files and temporary artifacts out of the repository unless they are requested
  deliverables.
- Report blockers and partial verification honestly; never imply a check passed when it was not run.
