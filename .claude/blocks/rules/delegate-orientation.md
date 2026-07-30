---
id: delegate-orientation
title: Delegate orientation
kind: rule
binding: mandatory
description: Delegate sizeable independent research; keep small targeted reads and exact source text here.
applies_to: [code, text]
use_when:
  - answering a question about a codebase, a body of documents, or how something works
---

# Delegate orientation

**Delegate when isolation or parallelism pays for the handoff. Read here when the source text itself
is needed or a few targeted calls settle the question.**

## Decide from the shape of the work

Delegate when one or more of these are true:

- the question crosses a subsystem or a body of documents large enough to pollute the main context;
- two or three independent questions can be investigated in parallel;
- the result should be a compressed map rather than source text;
- the exploration is sizeable enough that a separate worker can finish it end to end.

Do not delegate work a handful of targeted searches or reads can finish. A subagent has setup,
handoff, and synthesis cost; using one to perform three tool calls makes the task slower and no
clearer.

## Scale with the lane

| Lane | Delegation |
|---|---|
| **direct** | none |
| **standard** | one narrow explorer when the affected flow is not established and orientation is broad |
| **wide** | two or three independent scopes in parallel |

More agents are not stronger evidence. Keep scopes independent, bounded, and useful on their own.
If one worker can do the whole investigation coherently, use one.

## Choose the right boundary

- *What exists here, how does this flow work, what will this change touch* → `code-explorer`.
- *What do these documents state, imply, and contradict* → `requirements-analyst`.

Read in the main context when the text itself is the deliverable or input to transformation:

- the **exemplar** you are about to write code like — a summary of a file cannot carry the shape of it;
- a document whose **exact wording** you are reconciling or quoting;
- a single file whose location and relevance you already know.

## Say what you launched

When you delegate, name the scopes in one line before launching. This makes the split correctable
without narrating every search or every optional step you did not take.

## Why this is a rule and not advice

Repeated real sessions kept broad orientation in the main context because local reading looked
cheaper one call at a time. The rule preserves context isolation on work that benefits from it. Its
lane limits prevent the opposite failure: delegating small work because delegation itself looks like
compliance.
