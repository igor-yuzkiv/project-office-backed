# 005 - Create Task Entity

## Що реалізовано

Створено сутність `Task`: enums, model, migration, factory, JSON resource і frontend types.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Domains/Task/Enums/TaskPriority.php` |
| Створено | `app/Domains/Task/Enums/TaskStatus.php` |
| Створено | `app/Domains/Task/Models/TaskModel.php` |
| Створено | `database/migrations/2026_05_31_130000_create_tasks_table.php` |
| Створено | `database/factories/TaskModelFactory.php` |
| Створено | `app/Http/Resources/Tasks/TaskResource.php` |
| Створено | `resources/js/entities/task/types/task.types.ts` |
| Створено | `resources/js/entities/task/types/index.ts` |

## Schema

```
tasks
  id              ulid, primary
  project_id      ulid, FK → projects, cascadeOnDelete
  task_list_id    ulid, FK → task_lists, nullable, nullOnDelete
  key             string, unique
  sequence_number unsignedInteger
  unique(project_id, sequence_number)
  name            string
  description     longText, nullable
  priority        unsignedInteger (10 / 50 / 100)
  status          string ('open' / 'in_progress' / 'completed' / 'closed')
  created_by      ulid, FK → users, nullable, nullOnDelete
  updated_by      ulid, FK → users, nullable, nullOnDelete
  created_at      timestamp
  updated_at      timestamp
```

## Enums

`TaskPriority` (int-backed): `Low = 10`, `Medium = 50`, `High = 100`.

`TaskStatus` (string-backed): `Open`, `InProgress`, `Completed`, `Closed`.

## Resource shape

```json
{
  "id": "01j...",
  "project_id": "01j...",
  "task_list_id": null,
  "key": "PROJ-1",
  "sequence_number": 1,
  "name": "Fix login bug",
  "description": null,
  "priority": { "value": 50, "name": "Medium" },
  "status": "open",
  "created_by": { "id": "01j...", "name": "Igor" },
  "updated_by": { "id": "01j...", "name": "Igor" },
  "created_at": "2026-05-31T...",
  "updated_at": "2026-05-31T..."
}
```

## Рішення

- `priority` у resource — об'єкт `{ value, name }`, де `name` береться з `$this->priority->name` (PHP enum case name: `Low`, `Medium`, `High`).
- `status` у resource — scalar string value enum.
- `task_list_id` — `nullOnDelete`: при видаленні Task List tasks залишаються, `task_list_id` стає `null`.
- `project_id` — `cascadeOnDelete`: при видаленні Project tasks видаляються.
- Factory не включає `project_id` — передається явно в тестах (аналогічно `TaskListModelFactory`).

## Перевірки

- `php -l` — синтаксис чистий.
- `pint --test` — стиль пройдений.

## Commit message

```
feat(task): add Task entity, migration, factory and resource
```
