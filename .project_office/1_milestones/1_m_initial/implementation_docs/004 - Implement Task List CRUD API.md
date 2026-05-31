# 004 - Implement Task List CRUD API

## Що реалізовано

CRUD API для `TaskList` з вкладеними routes під `projects`, actions/handlers і response через `TaskListResource`.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Domains/TaskList/Actions/CreateTaskList/CreateTaskListCommand.php` |
| Створено | `app/Domains/TaskList/Actions/CreateTaskList/CreateTaskListHandler.php` |
| Створено | `app/Domains/TaskList/Actions/UpdateTaskList/UpdateTaskListCommand.php` |
| Створено | `app/Domains/TaskList/Actions/UpdateTaskList/UpdateTaskListHandler.php` |
| Створено | `app/Domains/TaskList/Actions/DeleteTaskList/DeleteTaskListHandler.php` |
| Створено | `app/Http/Requests/TaskLists/StoreTaskListRequest.php` |
| Створено | `app/Http/Requests/TaskLists/UpdateTaskListRequest.php` |
| Створено | `app/Http/Controllers/TaskLists/TaskListsController.php` |
| Оновлено | `routes/api.php` |

## Routes

Вкладені під `{project}`, middleware `auth:sanctum`.

| Method | Path | Action | Response |
| --- | --- | --- | --- |
| GET | `/api/projects/{project}/task-lists` | index | Paginated `TaskListResource` |
| POST | `/api/projects/{project}/task-lists` | store | `TaskListResource` 201 |
| GET | `/api/projects/{project}/task-lists/{task_list}` | show | `TaskListResource` |
| PUT/PATCH | `/api/projects/{project}/task-lists/{task_list}` | update | `TaskListResource` |
| DELETE | `/api/projects/{project}/task-lists/{task_list}` | destroy | `{ "message": "Task list deleted." }` |

## Рішення

- Routes вкладені через `Route::apiResource('projects.task-lists', ...)`.
- `index` фільтрує по `project_id` з route binding.
- `store` отримує `project` з route binding, передає у `CreateTaskListCommand`.
- Update — PATCH-like: `name` з `sometimes` rule, `array_filter` у handler.
- `{project}` у `show`, `update`, `destroy` присутній в URL але не використовується для scoping (MVP).
- `routes/api.php` доповнено користувачем: додано `AuthController` з `login`, `logout`, `user` endpoints.

## Перевірки

- `php -l` — синтаксис чистий.
- `pint --test` — стиль пройдений.
- `php artisan route:list` — 10 routes зареєстровано коректно.

## Commit message

```
feat(task-list): implement TaskList CRUD API with nested routes
```
