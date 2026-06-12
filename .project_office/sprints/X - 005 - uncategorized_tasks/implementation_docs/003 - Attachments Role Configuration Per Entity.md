# 003 - Attachments Role Configuration Per Entity

## What was implemented

Introduced `AttachmentRole` as a plain type alias (`type AttachmentRole = string`) and a per-entity roles config for tasks. Replaced the bare `"task_description"` string literal in `TaskEditPage` with a typed constant.

## Changed files

| File | Change |
|------|--------|
| `resources/js/shared/types/entity.types.ts` | Added `export type AttachmentRole = string` |
| `resources/js/entities/task/config/task-attachment-roles.config.ts` | New file — `TASK_ATTACHMENT_ROLES = { DESCRIPTION: 'task_description' } as const` |
| `resources/js/entities/task/config/index.ts` | Added reexport of `task-attachment-roles.config` |
| `resources/js/entities/attachment/types/attachment.types.ts` | `role` updated to `AttachmentRole | null` / `AttachmentRole` |
| `resources/js/shared/components/md-editor/ui/MarkdownEditor.vue` | `image_role` prop updated to `AttachmentRole` |
| `resources/js/pages/tasks/edit/TaskEditPage.vue` | Replaced `image_role="task_description"` with `:image_role="TASK_ATTACHMENT_ROLES.DESCRIPTION"` |

## Decisions

- `AttachmentRole` is a plain type alias (`string`), not a brand type — consistent with `ModuleName` pattern already in the project.
- `AttachmentRole` placed in `entity.types.ts` (no separate file) — consistent with `ModuleName`.
- No `EntityAttachmentRoles<Entity>` helper introduced — premature with only one consumer.
- No label/icon metadata per role — not needed yet.
- Backend enum not introduced — frontend-only contract per task spec.

## Validation

- `npm run format` — passed
- `npm run lint` (targeted) — passed
- `npm run types:check` — passed
