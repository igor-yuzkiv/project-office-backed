# 006 - Implement Task CRUD API

## Що реалізовано

CRUD API для `Task` з вкладеними routes під `projects`, actions/handlers і response через `TaskResource`.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Domains/Task/ValueObjects/TaskKey.php` |
| Створено | `app/Domains/Task/TaskKeyResolver.php` |
| Створено | `app/Domains/Task/Actions/CreateTask/CreateTaskCommand.php` |
| Створено | `app/Domains/Task/Actions/CreateTask/CreateTaskHandler.php` |
| Створено | `app/Domains/Task/Actions/UpdateTask/UpdateTaskCommand.php` |
| Створено | `app/Domains/Task/Actions/UpdateTask/UpdateTaskHandler.php` |
| Створено | `app/Domains/Task/Actions/DeleteTask/DeleteTaskHandler.php` |
| Створено | `app/Http/Requests/Tasks/StoreTaskRequest.php` |
| Створено | `app/Http/Requests/Tasks/UpdateTaskRequest.php` |
| Створено | `app/Http/Controllers/Tasks/TasksController.php` |
| Оновлено | `routes/api.php` |

## Routes

Вкладені під `{project}`, middleware `auth:sanctum`.

| Method | Path | Action | Response |
| --- | --- | --- | --- |
| GET | `/api/projects/{project}/tasks` | index | Paginated `TaskResource` |
| POST | `/api/projects/{project}/tasks` | store | `TaskResource` 201 |
| GET | `/api/projects/{project}/tasks/{task}` | show | `TaskResource` |
| PUT/PATCH | `/api/projects/{project}/tasks/{task}` | update | `TaskResource` |
| DELETE | `/api/projects/{project}/tasks/{task}` | destroy | `{ "message": "Task deleted." }` |

## Рішення

- `TaskKeyResolver` — окремий клас на рівні домену (`app/Domains/Task/TaskKeyResolver.php`), метод `resolve(ProjectModel): TaskKey`. Містить TODO про database lock для race conditions.
- `TaskKey` — Value Object з `sequenceNumber` (int) і `value` (string, наприклад `PROJ-1`).
- `CreateTaskHandler` інжектить `TaskKeyResolver`, `status` встановлюється як `TaskStatus::Open` автоматично.
- `priority` в `StoreTaskRequest` — integer, валідація через `Rule::in(array_column(TaskPriority::cases(), 'value'))` (значення: 10, 50, 100).
- `status` в `UpdateTaskRequest` — string, валідація через `Rule::enum(TaskStatus::class)`.
- Update — PATCH-like: `array_filter` пропускає null. **Обмеження:** очистити `task_list_id` через update не можна (MVP).
- `{project}` в `show`, `update`, `destroy` присутній в URL, але не використовується для scoping (MVP, аналогічно task-lists).

## Перевірки

- `php -l` — синтаксис чистий.
- `pint --test` — стиль пройдений.
- `php artisan route:list` — 5 tasks routes зареєстровано коректно.

## Commit message

```
feat(task): implement Task CRUD API with nested routes
```
