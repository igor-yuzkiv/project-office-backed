---
type: task
status: draft
---

# 004 - Backend Record Tags Endpoints

## Goal

Реалізувати nested endpoints для повного списку тегів конкретної сутності: `GET /api/tasks/{id}/tags` і `GET /api/projects/{id}/tags`.

## Context

`TaskResource` і `ProjectResource` повертають тільки перші 4 теги. Frontend `ViewAllTagsDialog` потребує endpoint для повного списку тегів сутності, без пагінації, без лічильника використання.

## Scope

- Додати action `tags` у `TasksController` → `GET /api/tasks/{id}/tags`.
- Додати action `tags` у `ProjectsController` → `GET /api/projects/{id}/tags`.
- Обидва endpoints повертають колекцію `TagResource` — повний список тегів сутності.
- Порядок — `taggables.created_at ASC` (узгоджено з task 003).
- Якщо сутність не знайдена — `404`.
- Eager load для уникнення N+1.

## Out Of Scope

- Лічильник використання тегів.
- Пагінація.
- Search / filter тегів усередині цього endpoint.
- Global tags listing endpoint (поза scope sprint).
- Frontend.

## Expected Behavior

Request:

```
GET /api/tasks/{id}/tags
```

Response:

```json
{
    "data": [
        {"id": "...", "name": "bug", "color": "#ff0000"},
        {"id": "...", "name": "ui", "color": "#00ff00"}
    ]
}
```

- Сортування — порядок прив'язки.
- Без обмеження кількості (повний список).
- `404` якщо Task/Project не існує.

## Technical Notes

- Реалізувати через окремі actions у відповідних existing controllers, не вводити новий controller.
- Endpoints мають бути зареєстровані у тому ж routes файлі, де вже route group для `tasks` і `projects`.
- Authorization policies, якщо вони є на parent resource, мають застосовуватися аналогічно для цих endpoints.

## Acceptance Criteria

- [ ] `GET /api/tasks/{id}/tags` повертає всі теги Task.
- [ ] `GET /api/projects/{id}/tags` повертає всі теги Project.
- [ ] Порядок — `taggables.created_at ASC`.
- [ ] Неіснуючий ID повертає `404`.
- [ ] Pint і PHPStan проходять без нових помилок.

## Open Questions

- N/A

## Notes For Developer Agent

- Не дублювати TagResource — повторно використати існуючий з task 002.
- Не додавати POST/DELETE на ці endpoints — прив'язка тільки через Task/Project Update (task 003).
