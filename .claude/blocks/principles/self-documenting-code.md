---
id: self-documenting-code
title: Self-documenting code
kind: principle
binding: guidance
description: Code carries the mechanics; comments preserve intent, constraints, and surprises the code cannot show.
applies_to: [code]
use_when:
  - writing or changing code
skip_when:
  - editing generated files
---

# Self-documenting code

**Make the code understandable first. Add or retain a comment when removing it would erase
information the code cannot carry.**

There is no comment quota in either direction. Match the surrounding code's density and idiom, but
do not copy noise merely because it is nearby. The test is whether a future maintainer loses
important intent by deleting the comment.

## What belongs in code

- Introduce an explanatory variable when a condition or expression needs a name to be readable.
- Extract a function when a block needs a heading. The function name *is* the heading, and unlike a heading it cannot drift from what follows it.
- Let types, signatures, and tests state behaviour they can express precisely.

## What a comment is for

A comment earns its place when it preserves:

- non-obvious intent — the reason this is done at all;
- a domain rule or deliberate exception that looks wrong without its history;
- a constraint not visible from here — an API's behaviour, a rate limit, an ordering requirement;
- a trade-off that was made deliberately;
- a decision that would otherwise look like a mistake.

## Long comments

When the code needs more than one line of explanation:

1. Lead with the purpose — [[bluf]].
2. Group the surprising rules and exceptions.
3. Keep the constraints and trade-offs somebody might otherwise "fix" incorrectly.
4. Use stable, ordinary wording — [[plain-language]].
5. Remove sections that only narrate mechanics — [[kiss]].

Technical precision stays. [[eli5]] applies only when the actual reader needs it, not automatically
to peer-facing code.

## What does not belong

- `// Added validation here` and `// Updated to handle the new case` narrate the diff. The history
  belongs in the commit message — see [[wrap-up-work]].
- `// Let me know if you prefer X` addresses a reviewer, not a future maintainer.
- A comment that translates the next line into English repeats mechanics the code already states.
- A long catalogue of every branch obscures the two rules that actually need explanation.

## Existing comments

Compressing is not deleting knowledge. Preserve every still-true intent, rule, constraint, and
trade-off; remove repetition and implementation narration. If a comment's purpose is unclear,
establish it before removal — [[chestertons-fence]] applied to prose.

## Check

- Could naming, structure, a type, or a test carry this more reliably?
- What important information disappears if the comment is deleted?
- Does the first sentence tell the reader why the comment exists?
- Are the surprising rules easy to scan?
- Will it still be true after the next change to this function?
