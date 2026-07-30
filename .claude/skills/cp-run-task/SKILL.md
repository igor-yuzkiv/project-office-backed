---
name: cp-run-task
description: The default cycle for executing a piece of work in a codebase — size the ceremony, gather only needed context, implement through feedback, and close with proportionate evidence. Use when picking up a task, ticket, bug, or change request that is ready to be worked on, at any size from a one-line fix to a multi-part feature. Not for deciding what to build — that is concept work.
---

# Run task

Follow `.claude/blocks/pipelines/run-task.md`. Read it now; it defines the lanes, the steps, and the gates.

This file exists only to bring you there. Everything that governs the work is in the pipeline and the blocks it references.

## The one thing worth repeating here

**Pick the lane before doing anything else, and when unsure pick the lighter one.**

Direct work stays local. Standard work delegates only when orientation is genuinely broad. Wide
work earns parallel explorers, seam verification, and independent review.

State the lane and the reason in one line, so the choice can be corrected rather than silently
repeated. Then execute through the deterministic feedback available; do not add review or
re-verification as ritual.

## Where the pieces are

- Pipeline: `.claude/blocks/pipelines/run-task.md`
- Operations: `.claude/blocks/operations/`
- Rules that apply throughout: `.claude/blocks/rules/`
- Project-specific addresses, exemplars, and verification commands: the project profile

If the project has no profile, the addresses and verification commands are unknown — ask once and record the answers rather than guessing. See `.claude/blocks/rules/artifact-routing.md`.
