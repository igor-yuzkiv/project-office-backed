---

allowed-tools: Bash(git status:*), Bash(git diff:*), Bash(git log:*)
argument-hint: [optional context]
description: Generate a Conventional Commit message from current staged changes without committing
--------------------------------------------------------------------------------------------------

# Generate Commit Message

Generate a well-formatted commit message based on the current staged changes.

User context: $ARGUMENTS

## Current Repository State

* Git status: !`git status --porcelain`
* Current branch: !`git branch --show-current`
* Staged changes: !`git diff --cached --stat`
* Recent commits: !`git log --oneline -5`

## Purpose

This command analyzes the currently staged changes and suggests a commit message in Conventional Commit format.

It must only generate commit message suggestions.

It must not stage files, commit files, amend commits, push changes, or modify the git state in any way.

## Strict Rules

* Do not run `git add`.
* Do not run `git commit`.
* Do not run `git commit --amend`.
* Do not run `git push`.
* Do not modify staged or unstaged files.
* Do not change the repository state.
* Only inspect repository status, staged diff, and recent commits.
* If no files are staged, stop and tell the user that there are no staged changes.
* Do not suggest committing unstaged changes unless the user explicitly asks.
* Generate the message only from staged changes.
* If the provided user context conflicts with the staged diff, prioritize the staged diff.

## Analysis Steps

1. Check whether there are staged changes.
2. Review the staged diff using `git diff --cached`.
3. Identify the main purpose of the change.
4. Determine the appropriate Conventional Commit type.
5. Check whether the staged changes represent one logical change or multiple unrelated changes.
6. Generate a concise commit message.
7. If the changes are not atomic, suggest how to split them into separate commits.

## Conventional Commit Types

Use one of the following types:

* `feat`: new feature or user-facing behavior
* `fix`: bug fix
* `docs`: documentation-only changes
* `style`: formatting or style-only changes without behavior changes
* `refactor`: code restructuring without changing behavior
* `perf`: performance improvement
* `test`: adding or updating tests
* `chore`: tooling, build, configuration, or maintenance changes
* `ci`: CI/CD configuration changes

## Commit Message Rules

* Use Conventional Commit format: `<type>: <description>`
* Use present tense and imperative mood.
* Keep the subject line under 72 characters.
* Use lowercase description after the type.
* Do not end the subject line with a period.
* Prefer clear business or technical intent over file-based descriptions.
* Do not mention implementation details unless they are the main purpose of the change.

## Output Format

Return:

```text
Recommended commit message:
<type>: <description>
```

If useful, also return:

```text
Alternative messages:
- <type>: <description>
- <type>: <description>
```

If the staged changes contain multiple unrelated concerns, return:

```text
Suggested split:
1. <type>: <description>
   Files: <relevant files>

2. <type>: <description>
   Files: <relevant files>
```

## Important Notes

* This command is advisory only.
* The user is responsible for running the actual commit command.
* Never create the commit automatically.
