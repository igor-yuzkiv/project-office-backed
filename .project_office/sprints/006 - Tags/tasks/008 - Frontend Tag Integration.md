---
type: task
status: draft
---

# 008 - Frontend Tag Integration

## Goal

Підключити `TagInput` до `EditTaskPage` і `ProjectUpsertDialog`. Додати фільтр за тегами на сторінки списків Task і Project.

## Context

Компоненти готові у task 007, entity layer — у task 006. Цей task інтегрує їх у реальні місця користувацького flow.

Project не має окремої edit-сторінки — редагування і створення Project відбуваються через `widgets/projects/upsert-dialog/ui/ProjectUpsertDialog.vue`.

## Scope

- `EditTaskPage`:
  - додати `TagInput` у форму редагування;
  - bind до поля `tag_ids` у формі;
  - при submit — відправляти `tag_ids` як частину `Update Task` payload.

- `ProjectUpsertDialog`:
  - додати `TagInput` у форму;
  - bind до поля `tag_ids`;
  - підтримати як create, так і update flow — при створенні новий Project повинен прив'язати вибрані теги одразу через `tag_ids` у payload.

- Task details / Project details сторінки:
  - відображати `TagList` із полем `tags` із resource (перші 4);
  - підключити кнопку "View all" у `TagList` до `ViewAllTagsDialog`.

- Фільтр за тегами на сторінках списків:
  - Tasks list page — додати фільтр-control з multi-select тегів через `useTagsSearch` і backend filter `tags` (OR);
  - Projects list page — те саме;
  - використати існуючу filter infrastructure sprint 004 / 005.

## Out Of Scope

- Зміни в інших сторінках, де теги не передбачені.
- Редагування існуючого тега.
- Видалення тегів.

## Expected Behavior

- На `EditTaskPage` можна додавати/видаляти теги і створювати нові через dialog. Збереження форми зберігає прив'язки.
- У `ProjectUpsertDialog` те саме — теги доступні при створенні і редагуванні Project.
- На Task details сторінці показано перші 4 теги; "View all" відкриває dialog із повним списком.
- На Project details сторінці — те саме.
- На Tasks list сторінці можна відфільтрувати задачі за одним або кількома тегами (OR).
- На Projects list сторінці — те саме.

## Technical Notes

- Не дублювати state — `tag_ids` зберігається у формі сторінки, відображення йде через `TagInput`.
- Після успішного зберігання форми — invalidate query для конкретного Task/Project, щоб detail view оновився.
- Фільтр-control не повинен мати власну логіку зберігання — слідувати pattern, використаному у sprint 004 для існуючих фільтрів.

## Acceptance Criteria

- [ ] `EditTaskPage` дозволяє редагувати теги через `TagInput` і зберігає `tag_ids` при submit.
- [ ] `ProjectUpsertDialog` підтримує `TagInput` як при створенні, так і при редагуванні.
- [ ] Task details показує `TagList` із перших 4 тегів і кнопку "View all".
- [ ] Project details — те саме.
- [ ] Tasks list має фільтр за тегами (OR).
- [ ] Projects list — те саме.
- [ ] Після створення нового тега через dialog він автоматично додається у вибраний список.
- [ ] Format, lint, types check проходять.

## Open Questions

- N/A

## Notes For Developer Agent

- Не змінювати верстку інших полів більше, ніж потрібно для додавання `TagInput`.
- `ProjectUpsertDialog` розташований у `widgets/projects/upsert-dialog/ui/ProjectUpsertDialog.vue`.
