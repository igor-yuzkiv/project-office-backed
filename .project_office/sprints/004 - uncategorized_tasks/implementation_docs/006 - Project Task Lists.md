# 006 - Project Task Lists

## What was implemented

Implemented the Project Task Lists tab with a table, upsert dialog (create + edit), and context menus. Also added context menus and search to `ProjectTasksPage` and `TasksPage` as part of the same review cycle.

## Changed files

### Backend

| File | Change |
|------|--------|
| `app/Domains/TaskList/Models/TaskListModel.php` | Added `tasks(): HasMany` relation |
| `app/Http/Controllers/TaskLists/TaskListsController.php` | Added `->withCount('tasks')` in `index` and `search` |
| `app/Http/Resources/TaskLists/TaskListResource.php` | Added `tasks_count` via `whenCounted('tasks')` |

### Frontend — entities

| File | Change |
|------|--------|
| `resources/js/entities/task-list/types/task_list.types.ts` | Added `tasks_count?: number` to `ITaskList` |
| `resources/js/entities/task-list/mutations/use.create-task-list.mutation.ts` | New — creates task list, invalidates `TaskListQueryKey.all` |
| `resources/js/entities/task-list/mutations/use.update-task-list.mutation.ts` | New — updates task list, invalidates `TaskListQueryKey.all` |
| `resources/js/entities/task-list/mutations/use.delete-task-list.mutation.ts` | New — deletes with confirm dialog, invalidates `TaskListQueryKey.all` |
| `resources/js/entities/task-list/mutations/index.ts` | New — reexports all three mutations |
| `resources/js/entities/task/mutations/use.delete-task.mutation.ts` | New — deletes with confirm dialog, invalidates `TaskQueryKey.all` |
| `resources/js/entities/task/mutations/index.ts` | Added `useDeleteTaskMutation` reexport |

### Frontend — widgets

| File | Change |
|------|--------|
| `resources/js/widgets/task-list/lists-table/ui/TaskListsTable.vue` | New — DataTable with Name, Tasks Count, Created columns and `actions` slot |
| `resources/js/widgets/task-list/lists-table/index.ts` | New — reexport |
| `resources/js/widgets/task-list/upsert-dialog/composables/use.task-list-upsert-dialog.ts` | New — composable supporting create and edit modes |
| `resources/js/widgets/task-list/upsert-dialog/ui/UpsertTaskListDialog.vue` | New — dialog with Name + Project (disabled) fields |
| `resources/js/widgets/task-list/upsert-dialog/index.ts` | New — reexport |
| `resources/js/widgets/tasks/tasks-table/ui/TasksTable.vue` | Removed `menuClick` emit, added `actions` slot column |
| `resources/js/widgets/attachments/attachments-table/ui/AttachmentsTable.vue` | Added `actions` slot column |
| `resources/js/widgets/projects/lookup-field/ui/ProjectLookupField.vue` | Added explicit `disabled` prop |

### Frontend — pages

| File | Change |
|------|--------|
| `resources/js/pages/projects/details/tabs/ProjectTaskListsPage.vue` | Full implementation: search, New Task List button, table, context menu (Edit/Delete), upsert dialog |
| `resources/js/pages/tasks/list/TasksPage.vue` | Added context menu (Edit/Delete) with `IconButton` in `#actions` slot |
| `resources/js/pages/projects/list/ProjectsPage.vue` | Replaced custom `<button>` with `IconButton` |
| `resources/js/pages/projects/details/tabs/ProjectTasksPage.vue` | Added search, New Task button, context menu (Edit/Delete), switched to `TasksTable` widget |

## Decisions

- Upsert dialog (create + edit) instead of create-only — requested during review to unify both actions in one dialog.
- `actions` slot pattern for all tables instead of emitting `menuClick` — keeps tables presentational and menus in page scope.
- `IconButton icon="material-symbols-light:more-vert"` used everywhere for consistency.
- No `app-card` wrapper on toolbar or table container within project detail tabs — consistent with `ProjectTaskListsPage`.
- Single root element on all page components to avoid router transition animation issues.

## Validation

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — passed
- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
