---
type: task
status: todo
milestone_number: 1
milestone_name: Initial Backend Foundation
milestone_path: .project_office/1_milestones/1_m_initial
task_number: 007
task_name: Implement Attachments Upload API
task_path: .project_office/1_milestones/1_m_initial/tasks/007 - Implement Attachments Upload API.md
created_at: 2026-05-31
updated_at: 2026-05-31
---

# 007 - Implement Attachments Upload API

## Milestone

- Number: 1
- Name: Initial Backend Foundation
- Path: `.project_office/1_milestones/1_m_initial`
- Plan: `.project_office/1_milestones/1_m_initial/plan.md`

## Task

- Number: 007
- Name: Implement Attachments Upload API
- Status: todo
- Path: `.project_office/1_milestones/1_m_initial/tasks/007 - Implement Attachments Upload API.md`

## Опис

Реалізувати `POST /api/attachments` для завантаження attachments під сутність або без прив'язки до сутності.

Request params:

- `file`: required;
- `entity_type`: nullable string;
- `entity_id`: nullable string;
- `role`: nullable.

Потрібно уточнити перед реалізацією:

- які типи файлів дозволені;
- максимальний розмір файлу;
- response shape після завантаження;
- чи `entity_type` має бути enum/whitelist;
- чи потрібен rename `EntityRef` на `ModuleRef`.

