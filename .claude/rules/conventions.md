# Rule: Code conventions

Cross-cutting code conventions that apply to both backend and frontend. Workflow
principles (clarify before acting, change strategy) live in `development-workflow.md`.

## Code style

Prefer **self-documenting code**: the code should explain itself before comments are
needed.

- Prefer intention-revealing names over short or generic ones; a slightly longer name
  is fine when it aids understanding. Avoid abbreviations unless established in the
  project domain.
- Introduce explanatory variables and extracted functions when they improve readability.
- Do not add comments that restate what the code already expresses.
- Preserve existing comments unless they are incorrect or obsolete.
- Comments explain **why**, not **what** — non-obvious intent, constraints, trade-offs,
  external behavior, or a decision that would otherwise look strange.

---

## Cross-Cutting Entities

Some entities are **universal** — they can be attached to any consuming entity:
`Comment`, `Tag`, `Attachment`.

### Rule: universal entities must not know about their consumers

A universal entity module (`entities/comment/`, `entities/tag/`, `entities/attachment/`, backend `CommentController`, etc.) contains only operations on the entity itself — identified by its own ID, not by a consuming entity's ID.

**Allowed in a universal entity:**
- `deleteCommentRequest(commentId)` — operates on a comment by its own ID
- `updateCommentRequest(commentId, data)` — same
- `deleteAttachmentRequest(attachmentId)` — same

**Not allowed in a universal entity:**
- `fetchTaskCommentsRequest(taskId, ...)` — scoped to a task
- `useTaskAttachmentsQuery(taskId)` — scoped to a task
- A controller method that receives a `TaskModel` parameter

### Rule: consuming entity owns its cross-cutting operations

Operations that reference a consuming entity by ID belong in that entity's module.

| What | Where |
| --- | --- |
| `fetchTaskCommentsRequest` | `entities/task/api/` |
| `useTaskCommentsQuery` | `entities/task/queries/` |
| `useCreateTaskCommentMutation` | `entities/task/mutations/` |
| `GET /tasks/{task}/comments` | `TaskCommentsController` |

When another entity (e.g., `Project`) needs comments, it gets its own set:
`fetchProjectCommentsRequest`, `useProjectCommentsQuery`, `ProjectCommentsController`.

Duplication across consuming entities is acceptable and preferred over shared abstractions.
