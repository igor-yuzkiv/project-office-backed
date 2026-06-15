# 003 - Create Task List Entity

## Що реалізовано

Створено сутність `TaskList`: model, migration, factory, JSON resource і frontend type.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Domains/TaskList/Models/TaskListModel.php` |
| Створено | `database/migrations/2026_05_31_120000_create_task_lists_table.php` |
| Створено | `database/factories/TaskListModelFactory.php` |
| Створено | `app/Http/Resources/TaskLists/TaskListResource.php` |
| Створено | `resources/js/entities/task_list/types/task_list.types.ts` |
| Створено | `resources/js/entities/task_list/types/index.ts` |

## Schema

```
task_lists
  id          ulid, primary
  project_id  ulid, FK → projects, cascadeOnDelete
  name        string
  created_by  ulid, FK → users, nullable, nullOnDelete
  updated_by  ulid, FK → users, nullable, nullOnDelete
  created_at  timestamp
  updated_at  timestamp
```

## Resource shape

```json
{
  "id": "01j...",
  "project_id": "01j...",
  "name": "Backlog",
  "created_by": { "id": "01j...", "name": "Igor" },
  "updated_by": { "id": "01j...", "name": "Igor" },
  "created_at": "2026-05-31T...",
  "updated_at": "2026-05-31T..."
}
```

## Рішення

- `project_id` у resource — scalar (не nested object).
- `created_by` / `updated_by` — `UserOverviewResource` через `whenLoaded`.
- `project_id` з `cascadeOnDelete` — при видаленні project видаляються і task lists.
- Frontend: `ITaskList extends IEntity` з `project_id` і `name`.

## Перевірки

- `php -l` — синтаксис чистий.
- `pint --test` — стиль пройдений.

## Commit message

```
feat(task-list): add TaskList entity, migration, factory and resource
```
