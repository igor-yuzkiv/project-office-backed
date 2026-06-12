---
type: task
status: draft
---

# 001 - Backend Tag Foundation

## Goal

Створити модель `TagModel`, міграції для таблиць `tags` і `taggables`, polymorphic relations на `TaskModel` та `ProjectModel`.

## Context

Sprint 006 додає теги для Task і Project. Поточна задача — foundation, на якій будуть будуватися всі наступні backend tasks: створення тегів, sync прив'язок, фільтрація.

Зв'язок Tag ↔ {Task, Project} реалізується через polymorphic many-to-many (`morphToMany` / `morphedByMany`).

## Scope

- Створити доменну папку `app/Domains/Tag/`.
- Створити `TagModel` у `app/Domains/Tag/Models/TagModel.php`.
- Створити міграцію для таблиці `tags`:
  - `id` ULID, primary key;
  - `name` string, unique;
  - `color` string (HEX без `#` або з `#` — на вибір реалізації, узгодити в Technical Notes);
  - без `timestamps`.
- Створити міграцію для таблиці `taggables`:
  - `tag_id` ULID, FK на `tags.id`, on delete cascade;
  - `taggable_id` ULID;
  - `taggable_type` string;
  - `created_at` timestamp (потрібний для порядку);
  - індекс по (`taggable_id`, `taggable_type`);
  - індекс по `tag_id`;
  - composite unique по (`tag_id`, `taggable_id`, `taggable_type`).
- Додати на `TaskModel` метод `tags(): MorphToMany`.
- Додати на `ProjectModel` метод `tags(): MorphToMany`.
- Додати `@property` PHPDoc на `TagModel`, `TaskModel`, `ProjectModel` для polymorphic relation, щоб задовольнити PHPStan level 5.

## Out Of Scope

- Створення тега через API.
- Інтеграція тегів у API resources Task/Project.
- Sync прив'язок у Create/Update handlers Task/Project.
- Endpoints для отримання тегів сутності.
- Фільтрація.
- Frontend.

## Expected Behavior

- Міграції створюють таблиці без помилок.
- `TaskModel::tags()` і `ProjectModel::tags()` повертають порожню колекцію для сутностей без прив'язок.
- `TagModel` можна створювати напряму через Eloquent з `name` і `color`.
- Спроба створити другий тег з тим самим `name` у БД призводить до constraint violation.

## Technical Notes

- ID — ULID, як в інших моделях проєкту.
- Колір зберігати як HEX string. Формат (з `#` чи без) узгодити з існуючою frontend практикою у `entities/project` (де є `status` color). У будь-якому разі залишити це рішення прозорим у міграції — без прихованих перетворень.
- `Searchable` (Scout) не додавати у цій задачі — це окрема ініціатива поза scope sprint.
- Polymorphic relations реєструвати у `boot` через `Relation::enforceMorphMap`, якщо в проєкті вже використовується morph map. Інакше — стандартні class names.

## Acceptance Criteria

- [ ] Існує `app/Domains/Tag/Models/TagModel.php` з `name`, `color`, ULID.
- [ ] Існують міграції для `tags` і `taggables` зі всіма колонками та індексами, описаними в Scope.
- [ ] `TaskModel` має `tags(): MorphToMany`.
- [ ] `ProjectModel` має `tags(): MorphToMany`.
- [ ] PHPDoc `@property` оновлений на трьох моделях для polymorphic relation.
- [ ] Pint і PHPStan проходять без нових помилок.

## Open Questions

- N/A

## Notes For Developer Agent

- Не додавати handlers, controllers, resources — це наступні tasks.
- Не змінювати існуючі поля моделей Task/Project, тільки додати relation.
- Назви класів і шляхи мають слідувати існуючому patern у `app/Domains/{Entity}/Models/{Entity}Model.php`.
