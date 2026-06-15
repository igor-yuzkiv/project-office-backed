---
type: task
status: todo
sprint_number: 1
sprint_name: Initial Backend Foundation
sprint_path: .project_office/1_sprints/1_m_initial
task_number: 005
task_name: Create Task Entity
task_path: .project_office/1_sprints/1_m_initial/tasks/005 - Create Task Entity.md
created_at: 2026-05-31
updated_at: 2026-05-31
---

# 005 - Create Task Entity

## Sprint

- Number: 1
- Name: Initial Backend Foundation
- Path: `.project_office/1_sprints/1_m_initial`
- Plan: `.project_office/1_sprints/1_m_initial/plan.md`

## Task

- Number: 005
- Name: Create Task Entity
- Status: todo
- Path: `.project_office/1_sprints/1_m_initial/tasks/005 - Create Task Entity.md`

## Опис

Створити сутність `Task`: model, migration, factory, JSON resource, enums і frontend type.

Поля:

- `id`: ULID;
- `project_id`;
- `task_list_id`: nullable;
- `key`;
- `sequence_number`;
- `name`;
- `description`: nullable long text;
- `priority`: integer у БД, enum у коді;
- `status`: string у БД, enum у коді;
- auditable columns.

Стартові значення `priority`:

- `low`: `10`;
- `medium`: `50`;
- `high`: `100`.

Стартові значення `status`:

- `open`;
- `in_progress`;
- `completed`;
- `closed`.

