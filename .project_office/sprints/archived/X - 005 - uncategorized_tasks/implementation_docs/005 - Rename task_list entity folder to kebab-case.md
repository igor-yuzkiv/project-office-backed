# 005 - Rename task_list Entity Folder To kebab-case

## What was implemented

Renamed `resources/js/entities/task_list/` → `resources/js/entities/task-list/` using `git mv` to preserve history. Updated all import paths from `@/entities/task_list/...` to `@/entities/task-list/...`.

## Changed files

| File | Change |
|------|--------|
| `resources/js/entities/task-list/` | Renamed from `task_list/` via `git mv` |
| `resources/js/entities/task/types/task.types.ts` | Updated import path to `@/entities/task-list/types` |
| `resources/js/widgets/task-list/lookup-field/ui/TaskListLookupField.vue` | Updated both imports to `@/entities/task-list/...` |
| `resources/js/pages/tasks/edit/TaskEditPage.vue` | Updated import path to `@/entities/task-list/types` |

## Decisions

- Used `git mv` to preserve git history.
- No file contents were changed — only the folder path and corresponding import strings.

## Validation

- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
