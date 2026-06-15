# 002 - Add Start Date and Due Date to Tasks Module

## Backend

### Migration
- `database/migrations/2026_05_31_130000_create_tasks_table.php` — added `start_date` (date, nullable) and `due_date` (date, nullable) before the `priority` column.

### Model
- `app/Domains/Task/Models/TaskModel.php` — added `start_date`, `due_date` to `$fillable`; casts `start_date`, `due_date` as `'date'`; PHPDoc `@property Carbon|null $start_date`, `@property Carbon|null $due_date`.

### Requests — validation + toCommand()
- `StoreTaskRequest` — added `start_date` / `due_date` rules; added `toCommand(): CreateTaskCommand` (Carbon::parse, priority coercion).
- `UpdateTaskRequest` — same rules; added `toCommand(TaskModel $task): UpdateTaskCommand` (existing-priority fallback logic moved from controller).

### Commands & Handlers
- `CreateTaskCommand` / `UpdateTaskCommand` — added `?Carbon $startDate`, `?Carbon $dueDate`.
- `CreateTaskHandler` / `UpdateTaskHandler` — pass `start_date`, `due_date` to `create()` / `update()`.

### Controller
- `TasksController` — `store()` and `update()` simplified to `$request->toCommand()` / `$request->toCommand($task)`. Unused imports removed.

### Resources
- `TaskResource` — added `start_date` (`?->toDateString()`), `due_date`.
- `TaskOverviewResource` — same additions.

## Frontend

### Types
- `ITask` — added `start_date: string | null`, `due_date: string | null`.
- `TaskOverviewDto` — added `start_date`, `due_date` to `Pick<>`.
- `ICreateTaskInput`, `IUpdateTaskInput` — added `start_date?: string | null`, `due_date?: string | null`.

### Task Edit Page
- `pages/tasks/edit/TaskEditPage.vue` — added `DatePicker` (primevue/datepicker); `start_date: Date | null`, `due_date: Date | null` in `TaskEditFormData`; `formatDateForApi()` helper; initialized from task in watcher; passed to submit payload; validation errors bound.

### Task Overview Tab
- `pages/tasks/details/tabs/TaskOverviewPage.vue` — added `<DisplayDate>` for Start Date and Due Date (`?? undefined` for null coercion).

### Tasks Table View
- `widgets/tasks/views/table/ui/TasksTableView.vue` — added `start_date` and `due_date` columns (`min-width: 10rem`) with `DisplayDate` slots. Table already has `scrollable` on DataTable — horizontal scroll kicks in automatically as columns grow.

## Also Completed in This Session

### ProjectOverviewResource → used in index/search
- `ProjectOverviewResource` expanded to match `ProjectResource` relations: `createdBy`, `updatedBy`, `archivedBy`, `tags`, `tasks`, `taskLists`; `status` fixed to `->value`.
- `ProjectsController::index()` and `search()` switched from `ProjectResource` to `ProjectOverviewResource`.
- Frontend `ProjectOverviewDto` expanded to include `created_at`, `updated_at`, and all optional relation fields.
- `fetchProjectsRequest`, `searchProjectsRequest` typed as `ProjectOverviewDto`.
- `ProjectsTableView`, `ProjectsPage`, `ProjectLookupField`, `use.task-create-dialog.ts`, `use.task-list-upsert-dialog.ts` all updated from `IProject` to `ProjectOverviewDto`.

### Factories
- `ProjectModelFactory` — added `description`, `start_date`, `end_date` with realistic random generation.
- `TaskModelFactory` — added `start_date`, `due_date` with realistic random generation.
