---
id: orient-in-codebase
title: Orient in codebase
kind: operation
description: Return a compressed map of the code relevant to one narrow question.
applies_to: [code]
use_when:
  - the change touches code you have not established the shape of
skip_when:
  - the relevant files and their behaviour are already established in this session
  - the question is "what shape should this code take" — read the exemplar directly instead; a map is the wrong output for that

requires:
  - a decision-driving scope question — one area, one flow, one concern
produces:
  - direct answer, reuse candidates, and a context map with entry points, flow, rules, tests, risks, and unknowns
completion_criteria:
  - every claim carries a file:line that was actually opened
  - what could not be determined is listed rather than omitted
  - the map answers the scope question and does not wander past it
  - implementation-shaping facts appear before the supporting map

autonomy:
  depth: high
  decisions: none
escalates:
  - the scope question turns out to span areas that should be separate runs

applies_rules:
  - output-discipline
  - evidence-and-claims
---

# Orient in codebase

## Goal

Answer one decision-driving question about the codebase with a map compact enough to reason from.

This operation does not propose implementations and does not change code. Its entire value is that reading forty files happens somewhere other than the main context.

**It is not how the caller gets an exemplar.** If the question is what shape the new code should take, the answer is the exemplar's text, and a compressed map cannot carry it — the caller should open that file directly rather than send you for a summary of it.

## Scope narrowly, scale deliberately

One agent told to "understand the project" returns a shallow survey of everything and a confident
tone. A bundle of questions is the same failure in list form. One scope answers one choice the
caller needs to make; wide work may use two or three independent scopes:

```text
orient: which existing entry point should a manual payroll recalculation reuse
orient: whether contractor portal already exposes tariff data this change can reuse
orient: which export implementation matches the requested file format
orient: which tests define the tariff behaviour this change must preserve
```

Do not launch all four examples by default. Do not group them merely because they concern the same
feature. Parallelize only independent questions — see [[delegate-orientation]]. Reads do not
conflict, but every handoff still has cost.

## Steps

1. State the answer the caller needs, or state that it could not be established.
2. Find reusable code or existing behavior that changes the likely implementation choice.
3. Find the entry points for the scope — routes, commands, jobs, events, UI actions.
4. Follow the flow far enough to support the answer, and no further.
5. Note business rules as you meet them: conditions, guards, special cases.
6. Find the tests that cover the area.
7. Record what you could not determine.

## Output

```yaml
answer: ""              # direct answer to the supplied question
reuse_candidates: []    # file:line — existing code or behavior the caller should not reinvent
implementation_facts: [] # fact — file:line — which expected choice or assumption this changes
entry_points: []       # file:line — what starts this flow
components: []         # file — role in one line
execution_flow: []     # ordered steps, each with file:line
business_rules: []     # rule — file:line where it is enforced
tests: []              # file — what it covers
risks: []              # what a change here could break, with evidence
unknowns: []           # what you could not establish, and what would settle it
```

Point, do not quote. The caller can open any of these; they cannot reclaim the context you spent pasting them.
