---
id: decision-authority
title: Decision authority
kind: rule
binding: mandatory
description: Routine local judgement is delegated; material decisions remain visible to the person.
applies_to: [code, decisions, text]
use_when:
  - any autonomous work
---

# Decision authority

Autonomy has two axes:

- **depth** — how many steps you take without checking in;
- **decision authority** — which classes of choice you may settle yourself.

They move independently. A long investigation can choose nothing; a small implementation can make
dozens of harmless local choices.

## Default

**Make routine, local, reversible choices inside the agreed scope. Escalate material choices.**

Routine means the surrounding project already supplies the answer, or two competent choices would
produce the same observable result. Naming that follows the local idiom, choosing the narrowest
existing check, and arranging private implementation details are routine.

Silence is neither blanket permission nor zero authority. Ask whether reasonable readings would
produce materially different work. If not, proceed. If yes, surface the choice.

## Material decisions

- product direction and what the feature is for;
- observable behaviour or a change to what users and callers receive;
- scope additions or removals, including additions that look free;
- shared contracts, public interfaces, and architectural choices with consequences beyond this task;
- security, authorization, data retention, migration, or destructive actions;
- trade-offs whose cost persists beyond this task;
- final prioritization.

These remain with the person unless the task explicitly delegates that class of decision.

## Escalate properly

A bare question stalls the work and hands back nothing. Escalation means:

1. what the choice is, in one sentence;
2. the real options — two or three, not a survey;
3. what each costs, concretely;
4. your recommendation, with the reason;
5. what is blocked until it is answered, and what is not.

Then continue with everything that does not depend on the answer. Stopping the whole task on one open question is almost never necessary.

Do not escalate preference disguised as a decision. If the choice is local, reversible, and
constrained by an established pattern, make it and continue.

## Recording

A material decision that was made for you belongs in the record — in the spec, the task, or the
decision log, according to the project's routing. Routine implementation choices belong in the
code and diff, not in a decision artifact.
