# 002 - Implement Project CRUD API

## Що реалізовано

CRUD API для сутності `Project` з controller, routes, form requests і domain actions.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Domains/Project/Actions/CreateProject/CreateProjectCommand.php` |
| Створено | `app/Domains/Project/Actions/CreateProject/CreateProjectHandler.php` |
| Створено | `app/Domains/Project/Actions/UpdateProject/UpdateProjectCommand.php` |
| Створено | `app/Domains/Project/Actions/UpdateProject/UpdateProjectHandler.php` |
| Створено | `app/Domains/Project/Actions/DeleteProject/DeleteProjectHandler.php` |
| Створено | `app/Http/Requests/Projects/StoreProjectRequest.php` |
| Створено | `app/Http/Requests/Projects/UpdateProjectRequest.php` |
| Створено | `app/Http/Controllers/Projects/ProjectsController.php` |
| Оновлено | `routes/api.php` |

## Routes

Всі routes під `auth:sanctum` middleware.

| Method | Path | Action | Response |
| --- | --- | --- | --- |
| GET | `/api/projects` | index | Paginated `ProjectResource` collection |
| POST | `/api/projects` | store | `ProjectResource` 201 |
| GET | `/api/projects/{project}` | show | `ProjectResource` |
| PUT/PATCH | `/api/projects/{project}` | update | `ProjectResource` |
| DELETE | `/api/projects/{project}` | destroy | `{ "message": "Project deleted." }` 200 |

## Рішення

- Pagination: стандартний Laravel `paginate()` через `ProjectResource::collection()`.
- Sorting: `sort_by` / `sort_order` query params через `getSortParams()` з base Controller.
- Update — PATCH-like: тільки передані поля оновлюються (`sometimes` rules + `array_filter` в handler).
- `created_by` / `updated_by` eager-load у controller перед поверненням resource.
- Handlers інжектуються через constructor DI у controller.
- `DeleteProjectHandler` — немає окремого Command, приймає `ProjectModel` напряму.

## Перевірки

- `php -l` — синтаксис чистий на всіх файлах.
- `pint --test` — стиль пройдений.
- `php artisan route:list` — всі 5 routes зареєстровано коректно.

## Для наступного агента

- `ProjectResource` потребує eager-load `['createdBy', 'updatedBy']` — controller це робить.
- `UpdateProjectCommand` використовує `null` для позначення "поле не передано" — `array_filter` у handler ігнорує `null`.
- `prefix` auto-генерується в `ProjectModel::saving()` якщо порожній при create.
- Наступна task: `003 - Create Task List Entity`.

## Commit message

```
feat(project): implement Project CRUD API with actions and routes
```
