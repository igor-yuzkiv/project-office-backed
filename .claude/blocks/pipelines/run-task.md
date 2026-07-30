---
id: run-task
title: Run task
kind: pipeline
description: The default operational cycle — size the work, gather only needed context, execute through feedback, and close.
applies_to: [code]
use_when:
  - any piece of work that is ready to be executed
skip_when:
  - the approach is undecided — that is concept work, not execution

requires:
  - a task, ticket, or agreed outcome
produces:
  - change with verification evidence proportionate to its risk, plus whatever update is owed outward
completion_criteria:
  - the outcome is met or the gap is stated
  - available deterministic feedback covering the change was acted on
  - unfixed findings and open questions are recorded, not dropped

autonomy:
  depth: high
  decisions: routine
escalates:
  - any operation in the cycle escalates
---

# Run task

Read the project profile before sizing. Use its language now; use its relevant exemplars,
verification commands, and artifact addresses when their step arrives.

References such as [[implement-change]] are load boundaries, not labels. When a non-trivial step
applies, open its named operation before doing that step. Do not preload later or skipped steps.

## Pick the lane first

**The most common way this pipeline fails is by being too heavy.** A validation tweak dragged through the full cycle twice teaches you to stop invoking it, and then none of this exists.

So the first act is sizing, and the tie-breaker is fixed: **when unsure, go one lane lighter.** An under-ceremonied task costs one extra step later; an over-ceremonied one costs your willingness to use the system at all.

| Lane | When | Steps |
|---|---|---|
| **direct** | one file, behaviour already established, no unknowns | implement ↔ feedback |
| **standard** | a normal task: a few files, some unknowns | orient? → clarify? → implement ↔ feedback → wrap-up |
| **wide** | large enough to split, or spanning areas | orient ⇉ → clarify → decompose → implement ⇉ → verify seams → review → wrap-up |

Say which lane you picked and why, in one line. That line is what lets the choice be corrected instead of silently repeated.

Re-size when the work grows across a new domain, shared contract, persistence boundary, or external
integration. Keep the current lane when the new detail does not change risk or coordination; name
the new lane only when it changes.

## The cycle

**1. Orient** — skipped in the direct lane and conditional in standard.

Two different questions live here, and they are answered differently. Conflating them is the most common way this step goes wrong.

| Question | How |
|---|---|
| Where does this live, how does it flow, what will it affect | [[orient-in-codebase]] via subagent — returns a map |
| What shape should the new code take | **read the exemplar directly**, in this context |

A subagent returns a compressed map, and a map is the wrong output for "write a file like this
one". The profile's `exemplars` names those files; open the relevant exemplar here, fully and
deliberately.

In the standard lane, use one explorer when the affected flow is unfamiliar and establishing it
would require broad reading across a subsystem. Keep the work here when a handful of targeted
searches or reads answers the question. Give the explorer one decision-driving question. Use its
answer before searching again; do not repeat research it already resolved.

In the wide lane, fan out two or three narrow, independent map scopes. Several focused maps are
useful; a crowd of agents all reading the same area is duplicated cost.

Name the scopes you launch in one line — see [[delegate-orientation]].

**2. Clarify** — [[clarify-task]], only where ambiguity can materially change the work.

Routine, reversible choices stay with the executor. Material blocking questions go to the human,
carrying options and a recommendation. Non-blocking ones are recorded and the work continues.

**▣ Human gate** — only if something blocks.

**3. Decompose** — [[decompose-into-workstreams]], wide lane only.

Shared contracts are settled here, before any fan-out. A contract left open gets invented once per
executor. If settling one needs a material design decision, that is an escalation, not a routine
judgement call.

**▣ Human gate** — if a shared contract requires a decision.

**4. Implement** — [[implement-change]].

Parallel only where write sets are disjoint or executors are isolated in separate worktrees. Otherwise serial. Each unit carries its own spec, its exclusive file list, and the shared contracts as settled.

**5. Close the feedback loop.**

Run the targeted deterministic checks available for the changed path and act on their output while
implementing. Do not add a generic second self-check after the same feedback has already been
consumed.

Load [[verify-change]] when acceptance criteria need explicit evidence, when manual checks matter,
or when project-specific verification has more than one step. In the wide lane, verify each unit and
then the integration seam. A unit that cannot be verified on its own was decomposed wrong.

**6. Review — conditional.**

Use [[review-change]] when the user asks for an independent review, the change crosses a trust
boundary or carries material risk, or the wide lane reaches its integration seam. Do not spawn a
reviewer merely to double-check small work.

**7. Wrap up** — [[wrap-up-work]], in standard and wide lanes or whenever an artifact/update is owed.

## What travels through

Each step's output feeds the next. What does not survive the handoff does not exist: an unfixed finding noticed at implement time is lost unless it reaches wrap-up.

Three things must arrive at the end intact:

- **unfixed findings** — surfaced and deliberately left alone;
- **open questions** — asked and unanswered;
- **unverified claims** — believed but not checked.

Dropping any of them turns a partial result into a report of a complete one.

## Skipping steps

The lane already declares which steps are optional. Report a meaningful deviation from the chosen
lane; do not narrate every optional step that correctly did not apply.

During execution, update the user only for a phase change, blocker, material decision, meaningful
deviation, or verification outcome. Individual files and routine tool calls are not progress
events.
