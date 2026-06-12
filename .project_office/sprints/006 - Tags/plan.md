---
type: sprint
status: draft
---

# Sprint 006 - Tags

## Goal

Додати систему тегів для Task і Project з можливістю створення, прив'язки до сутностей, перегляду та фільтрації.

## Expected Outcome

- Користувач може створити новий тег з власним або дефолтним рандомним кольором.
- Користувач може прив'язати теги до Task і Project через multi-select з пошуком.
- Список Task і Project можна фільтрувати за тегами.
- На сторінках Task і Project показуються перші 4 теги, повний список доступний через окремий dialog.

## Scope

Backend:
- модель `TagModel`, polymorphic pivot `taggables`;
- endpoint створення тега з нормалізацією `name`;
- nested endpoints `GET /api/tasks/{id}/tags` і `GET /api/projects/{id}/tags`;
- прийом `tag_ids` у Create/Update handlers Task і Project з `sync` прив'язок;
- фільтр за тегами (OR semantics) на list endpoints Task і Project;
- поле `tags` у Task та Project API resources, обмежене першими 4 за порядком прив'язки.

Frontend:
- entity layer `entities/tag/`;
- компоненти `TagBadge`, `TagList`, `CreateTagDialog`, `TagInput`, `ViewAllTagsDialog`;
- інтеграція `TagInput` у `EditTaskPage` і `EditProjectPage`;
- фільтр за тегами на сторінках списків Task і Project.

## Out Of Scope

- Окрема Tag Management page.
- Редагування `name` і `color` існуючого тега.
- Видалення тегів. Тег лишається в БД навіть без прив'язок.
- Лічильник кількості використань тега.
- Поширення тегів на сутності крім Task і Project.
- AND/OR перемикач у фільтрі — тільки OR.

## Tasks

### 001 - Backend Tag Foundation

Статус: todo

Модель `TagModel`, міграції для таблиць `tags` і `taggables`, polymorphic relations на `TaskModel` та `ProjectModel`.

### 002 - Backend Create And Search Tag Endpoints

Статус: todo

`CreateTagHandler`, `TagsController@store`, `TagsController@index` з опціональним `search`, FormRequest, нормалізація `name` (`trim` + `lowercase`), дефолтний рандомний HEX колір.

### 003 - Backend Tags In Task And Project Resources

Статус: todo

Поле `tags` (перші 4 за `taggables.created_at`) у Task і Project resources. Прийом `tag_ids` у Create/Update handlers Task і Project з `sync` прив'язок.

### 004 - Backend Record Tags Endpoints

Статус: todo

`GET /api/tasks/{id}/tags` і `GET /api/projects/{id}/tags` — повний список тегів сутності.

### 005 - Backend Task And Project Tag Filtering

Статус: todo

Фільтр за тегами (OR semantics) на list endpoints Task і Project через існуючу filter infrastructure.

### 006 - Frontend Tag Entity Layer

Статус: todo

`entities/tag/`: api, types, queries (search + record tags), mutations (create), config.

### 007 - Frontend Tag Components

Статус: todo

`TagBadge`, `TagList` (з кнопкою View All), `CreateTagDialog` (`vue3-colorpicker`), `TagInput` (multi-select + інтеграція `CreateTagDialog`), `ViewAllTagsDialog` (flex-wrap).

### 008 - Frontend Tag Integration

Статус: todo

Підключити `TagInput` до `EditTaskPage` і `EditProjectPage`. Додати фільтр за тегами на сторінки списків Task і Project.

## Dependencies

- 002 залежить від 001.
- 003, 004, 005 залежать від 001.
- 007 залежить від 006.
- 008 залежить від 007 та готовності `EditProjectPage` (окрема task `011 - Implement Project Edit Page` у sprint 004).

## Risks

- PHPStan level 5: polymorphic relations потребують `@property` PHPDoc на моделях.
- Потрібні індекси на `taggables` для швидкої фільтрації за `tag_id` та lookup по `taggable_id` + `taggable_type`.
- Якщо `EditProjectPage` не готова — інтеграцію в Project edit можна тимчасово винести з task 008 і відкласти; інтеграція у Task має бути виконана в будь-якому разі.

## Open Questions

- N/A

## Notes For Developer Agent

- Конкретні обмеження для `name` (max length) і `color` (формат HEX) визначити при реалізації відповідних backend tasks через FormRequest.
- Пакет `vue3-colorpicker` встановлений окремо — використовувати тільки в `CreateTagDialog`.
- Sync прив'язок (`tags()->sync($ids)`) не повинен створювати нові теги. Створення тільки через окремий endpoint у task 002.
- Нормалізація `name` (`trim` + `lowercase`) виконується тільки на бекенді у `CreateTagHandler`.
