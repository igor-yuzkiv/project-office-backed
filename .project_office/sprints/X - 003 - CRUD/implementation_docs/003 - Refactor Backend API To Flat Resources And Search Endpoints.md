# 003 - Refactor Backend API To Flat Resources And Search Endpoints

## Що реалізовано

Повний перехід від nested routes до flat API resources для `TaskList` і `Task`. Додано search endpoints для всіх трьох CRUD-сутностей. Оновлено frontend API шар під нові маршрути.

## Змінені файли

### Routes
- `routes/api.php` — nested `projects.task-lists` і `projects.tasks` видалено. Замінено на flat `apiResource('task-lists')` і `apiResource('tasks')` з відповідними `POST /task-lists/search` і `POST /tasks/search`.

### Backend — Models
- `app/Domains/TaskList/Models/TaskListModel.php` — додано `HasFilters`, `allowedFilters()`: `TextFilter` на `['name', 'project_id']`.
- `app/Domains/Task/Models/TaskModel.php` — додано `HasFilters`, `allowedFilters()`: `TextFilter` на `['name', 'description', 'key', 'project_id', 'task_list_id', 'status']`, `IntegerFilter` на `['priority']`.

### Backend — Commands
- `app/Domains/TaskList/Actions/CreateTaskList/CreateTaskListCommand.php` — `ProjectModel $project` замінено на `string $projectId`.
- `app/Domains/TaskList/Actions/CreateTaskList/CreateTaskListHandler.php` — оновлено під `$command->projectId`.
- `app/Domains/Task/Actions/CreateTask/CreateTaskCommand.php` — `ProjectModel $project` замінено на `string $projectId`.
- `app/Domains/Task/Actions/CreateTask/CreateTaskHandler.php` — завантажує `ProjectModel::findOrFail($command->projectId)` всередині handler для `TaskKeyResolver`.

### Backend — Requests
- `app/Http/Requests/TaskLists/StoreTaskListRequest.php` — додано `project_id` required.
- `app/Http/Requests/Tasks/StoreTaskRequest.php` — додано `project_id` required.
- `app/Http/Requests/Shared/SearchRequest.php` — новий універсальний search request для всіх ентітей.
- Видалено: `SearchProjectsRequest`, `SearchTaskListsRequest`, `SearchTasksRequest`.

### Backend — Controllers
- `app/Http/Controllers/TaskLists/TaskListsController.php` — видалено `ProjectModel $project` з усіх методів, додано `search()` з inline Scout логікою.
- `app/Http/Controllers/Tasks/TasksController.php` — видалено `ProjectModel $project` з усіх методів, додано `search()` з inline Scout логікою.
- `app/Http/Controllers/Projects/ProjectsController.php` — `search()` переписано з inline логікою (видалено `SearchProjectsQuery`).

### Backend — Видалені Query класи
- `app/Domains/Project/Queries/SearchProjectsQuery.php`
- `app/Domains/TaskList/Queries/SearchTaskListsQuery.php`
- `app/Domains/Task/Queries/SearchTasksQuery.php`

### Backend — Seeder
- `database/seeders/DatabaseSeeder.php` — додано `TaskModel::factory(10)` для кожного project (150 tasks загалом).

### Frontend
- `resources/js/entities/task_list/types/task_list.types.ts` — `ICreateTaskListInput` тепер містить `project_id: string`.
- `resources/js/entities/task_list/api/task_list.api.ts` — всі функції переведено на flat routes `/task-lists`. `projectId` видалено з параметрів.
- `resources/js/entities/task/types/task.types.ts` — `ICreateTaskInput` тепер містить `project_id: string`.
- `resources/js/entities/task/api/task.api.ts` — всі функції переведено на flat routes `/tasks`. `projectId` видалено з параметрів.
- `resources/js/pages/home/HomePage.vue` — виправлено placeholder під оновлений API.

### Tests
- `tests/Feature/Http/TaskLists/TaskListSearchTest.php` — 8 тестів: pagination, search query, text filter, project_id filter, invalid filter key, field not allowed, pagination, GET index.
- `tests/Feature/Http/Tasks/TaskSearchTest.php` — 10 тестів: pagination, search query, text/project_id/priority/status filters, invalid filter key, field not allowed, pagination, GET index.

### Документація
- `.project_office/project_documentation/filtering-system.md` — оновлено: прибрано Query класи, додано `Shared/SearchRequest`, таблицю allowed filters по моделях, всі три search endpoints.

## Важливі рішення

- **Inline search логіка в controller** — Query класи видалено, логіка Scout search інлайнована безпосередньо в `search()` методах. Це зменшує кількість файлів без втрати читабельності.
- **Shared SearchRequest** — один validation клас для всіх search endpoints замість окремих per-entity.
- **`CreateTaskHandler` завантажує ProjectModel** — `TaskKeyResolver` потребує `$project->prefix` для генерації ключа. Завантаження моделі залишено в handler, щоб зберегти command як простий DTO.
- **Nested routes повністю видалено** — без backward-compatible aliases.

## Перевірки

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — passed (level 5, 0 errors)
- `php artisan test` — 46/46 passed
- `npm run format` — passed
- `npm run types:check` — passed
