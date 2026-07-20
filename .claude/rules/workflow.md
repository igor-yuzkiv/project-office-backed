# Development workflow

Choose the pipeline by ambiguity, blast radius, reversibility, and ability to verify the result.
File count or the number of application layers alone does not determine risk. If several
classifications apply, use the highest-risk pipeline.

## Pipeline 1: Trivial

Use for obvious, local, low-risk changes that do not alter business behavior, architecture, stored
data, API contracts, or user interaction.

```text
Inspect -> Implement -> Check -> Handoff
```

1. Inspect the nearest relevant context.
2. Make the local change without requesting plan approval.
3. Run the cheapest relevant checks.
4. Provide the required handoff.

Typical examples include typos, comment or documentation corrections, safe naming cleanup, and
small configuration or styling fixes with an unambiguous result.

## Pipeline 2: Standard

Use for ordinary features, bug fixes, and refactors when expected behavior is clear and no
Controlled trigger applies.

```text
Explore -> Plan internally -> Implement -> Verify -> Self-review -> Handoff
```

### Explore

- Read affected code, callers and consumers, contracts, and nearest tests.
- Identify the owning backend domain or frontend layer and current local pattern.
- For full-stack work, trace the request and response shape across both sides.
- Check the working tree so user changes are not mistaken for task changes.

### Plan internally

Define intended behavior, affected scope, preserved contracts, implementation sequence, and
verification. This is a reasoning step, not a user approval gate.

### Implement

Complete the work within scope. Add or update tests for bug fixes and business-behavior changes by
default. Keep backend Resources and frontend types aligned. Resolve routine local decisions
independently.

### Verify

Run targeted tests and proportional static checks. Fix failures caused by the change. Run broader
checks when shared behavior or risk makes targeted verification insufficient. Browser and visual
verification are not automatic yet; follow `testing.md`.

### Self-review

Review the final diff for correctness, contract drift, missed states and edge cases, unrelated
edits, over-engineering, and incomplete verification. A separate reviewer or subagent is optional,
not a mandatory phase.

### Handoff

Report the result using the handoff contract in `CLAUDE.md`. The user reviews the diff and visually
checks UI work; do not add a final approval checkpoint.

## Pipeline 3: Controlled

Use when the task requires a material user decision or has a high-impact boundary.

```text
Explore -> Proposed plan -> User decision -> Implement -> Verify -> Self-review -> Handoff
```

Before implementation, present:

- understood behavior and relevant evidence;
- the recommended approach and meaningful alternatives, when they exist;
- affected scope and contracts that will change or remain stable;
- risks, assumptions, and unresolved decisions;
- planned automated and manual verification.

Wait for the user's decision. After approval, complete the remaining phases autonomously unless a
new Controlled trigger appears.

### Controlled triggers

Use this pipeline when any material condition applies:

- business behavior, user interaction, or acceptance criteria are ambiguous;
- multiple approaches produce materially different product or contract outcomes;
- the change introduces or moves an architectural boundary or shared abstraction;
- the Web API, CLI API, frontend/backend interface, or another external contract changes;
- agent-facing task workflow semantics change;
- authentication or authorization behavior changes materially;
- a schema migration, data migration, or risky data operation is required;
- the change crosses domains or layers with non-local consequences that are not already specified;
- repository behavior contradicts the task, design, or documentation;
- the result cannot be verified with adequate confidence;
- implementation requires material expansion beyond the understood or approved scope.

Creating a new migration file is allowed after approval. Running migrations remains mechanically
blocked.

## Checkpoints and escalation

A checkpoint is an exception for a decision, not a routine phase boundary. Stop and report when:

- scope must expand materially;
- evidence contradicts the requested behavior;
- a blocked, destructive, or externally consequential operation is required;
- verification exposes an unresolved product or UI decision;
- an external dependency prevents meaningful progress.

Explain the evidence, impact, recommendation, and exact decision needed. Do not search for a way
around mechanical restrictions. After a correction or clarified decision, continue from the
current phase and re-run only affected verification; do not restart the whole pipeline.

## Documentation

Update existing documentation when an implemented public contract or required workflow would
otherwise become incorrect. Create broader product or architecture documentation only when the
user requests it or approves it through the Controlled pipeline. Documentation is not a separate
mandatory phase.

## Subagents

Subagents are optional tools. Use them when isolated context, specialist analysis, independent
verification, or parallel work materially improves the result. Do not dispatch agents merely to
satisfy workflow ceremony, and do not replace the main agent's responsibility for synthesis and
handoff.

## Git and external actions

- Read-only git inspection is allowed.
- Commit and push only after an explicit user request and the resulting permission prompt.
- Never perform destructive git operations or work around a denied command.
- Do not change task status, create pull requests, publish artifacts, or communicate externally
  unless the user requested that action or the linked Project Office workflow explicitly requires
  it for the current task.
