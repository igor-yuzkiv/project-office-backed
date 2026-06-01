# Project Workspace

Project root: `/var/www/task-manager/mvp-task-manager`

Office root: `.project_office/`

---

## Agent Guides

| Path | Purpose |
| --- | --- |
| `.project_office/agent_guides/project_workspace.md` | Структура workspace. Цей файл. |
| `.project_office/agent_guides/developer_agent_workflow.md` | Workflow Developer Agent. |
| `.project_office/agent_guides/planning_agent_workflow.md` | Workflow Planning Agent. |

## Context Files

| Path | Purpose |
| --- | --- |
| `.project_office/roadmap.md` | Особисті нотатки автора — high-level напрямок. Тільки фоновий контекст, не джерело вимог. |
| `.project_office/backlog.md` | Особисті нотатки автора — ідеї та майбутні задачі. Тільки фоновий контекст, не джерело вимог. |

## Sprints

| Path | Purpose |
| --- | --- |
| `.project_office/sprints/` | Папки спринтів. |
| `.project_office/sprints/<sprint>/plan.md` | Загальний план sprint. |
| `.project_office/sprints/<sprint>/tasks/` | Task-файли sprint. |
| `.project_office/sprints/<sprint>/implementation_docs/` | Документація завершених tasks. |
| `.project_office/sprints/<sprint>/review.md` | Фінальне review sprint (тільки за запитом). |

## Other Directories

| Path | Purpose |
| --- | --- |
| `.project_office/design/` | Скріншоти, референси та дизайн-матеріали. |
| `.project_office/templates/` | Шаблони для sprint-планів і tasks. |
| `docs/` | Фінальна документація проєкту. |

## Templates

| Path | Purpose |
| --- | --- |
| `.project_office/templates/sprint_plan.md` | Шаблон sprint-плану. |
| `.project_office/templates/task_template.md` | Шаблон task-документу. |

## Naming Conventions

| Type | Active | Completed |
| --- | --- | --- |
| Sprint (папка) | `001 - Short Description` | `X - 001 - Short Description` |
| Task (файл) | `001 - Short Description.md` | `X - 001 - Short Description.md` |
