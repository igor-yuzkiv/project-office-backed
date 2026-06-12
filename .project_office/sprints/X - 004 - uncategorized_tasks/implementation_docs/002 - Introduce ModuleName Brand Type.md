# 002 - Introduce ModuleName Brand Type

## What was implemented

Introduced `ModuleName` as a simple type alias (`type ModuleName = string`) to replace bare string literals used for `entity_type` binding. Added `TASK_MODULE_NAME`, `PROJECT_MODULE_NAME`, and `TASK_LIST_MODULE_NAME` constants per entity config. Updated existing consumers to use the constant instead of the literal.

## Changed files

| File | Change |
|------|--------|
| `resources/js/shared/types/entity.types.ts` | Added `export type ModuleName = string` |
| `resources/js/entities/task/config/task-module.config.ts` | New file — `TASK_MODULE_NAME: ModuleName = 'tasks'` |
| `resources/js/entities/task/config/index.ts` | Added reexport of `task-module.config` |
| `resources/js/entities/project/config/project-module.config.ts` | New file — `PROJECT_MODULE_NAME: ModuleName = 'projects'` |
| `resources/js/entities/project/config/index.ts` | Added reexport of `project-module.config` |
| `resources/js/entities/task_list/config/task-list-module.config.ts` | New file — `TASK_LIST_MODULE_NAME: ModuleName = 'task_lists'` |
| `resources/js/entities/task_list/config/index.ts` | Added reexport of `task-list-module.config` |
| `resources/js/pages/tasks/edit/TaskEditPage.vue` | Replaced literal `"tasks"` with `:image_entity_type="TASK_MODULE_NAME"` |
| `resources/js/entities/attachment/types/attachment.types.ts` | `entity_type` updated to `ModuleName \| null` / `ModuleName` |

## Decisions

- `ModuleName` is a plain type alias (`string`), not a brand type — per user preference. Provides documentation value without type-casting overhead.
- `ModuleName` placed in `entity.types.ts` (no separate file) — per user direction.
- Added `PROJECT_MODULE_NAME` and `TASK_LIST_MODULE_NAME` proactively — user confirmed this was desired.

## Validation

- `npm run format` — passed
- `npm run lint` (targeted) — passed
- `npm run types:check` — passed
