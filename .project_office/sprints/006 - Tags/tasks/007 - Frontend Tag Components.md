---
type: task
status: draft
---

# 007 - Frontend Tag Components

## Goal

Створити набір UI компонентів для відображення, редагування і створення тегів: `TagBadge`, `TagList`, `CreateTagDialog`, `TagInput`, `ViewAllTagsDialog`.

## Context

Sprint 006 додає теги до Task і Project. Компоненти ізольовані від конкретних сторінок, щоб їх можна було реалізувати незалежно від інтеграції (інтеграція — task 008).

`vue3-colorpicker` встановлений окремо і використовується тільки в `CreateTagDialog`.

## Scope

Розмістити компоненти у `entities/tag/ui/` (або widget, якщо доречніше за конвенцією проєкту — узгодити при реалізації).

- `TagBadge.vue`:
  - props: `tag: Tag`;
  - відображає один тег як кольоровий бейдж із текстом `name`;
  - використати PrimeVue `Tag` або кастомний span з inline стилем `background-color`.

- `TagList.vue`:
  - props: `tags: Tag[]`, `total?: number`, `recordType?: 'task' | 'project'`, `recordId?: string`;
  - відображає масив `TagBadge` у flex-wrap;
  - якщо `total > tags.length` або є додаткові ознаки (наприклад, `tags.length === 4` і є `recordType` + `recordId`) — показати кнопку "View all", яка відкриває `ViewAllTagsDialog`.

- `CreateTagDialog.vue`:
  - props: `visible: boolean` (через `defineModel`);
  - поля форми: `name` (input), `color` (vue3-colorpicker із дефолтним рандомним HEX);
  - валідація: `name` не порожнє після trim;
  - submit → `useCreateTag` mutation;
  - emit `created` з `Tag` після успіху;
  - закривається після успіху.

- `TagInput.vue`:
  - props: `modelValue: string[]` (масив tag ID) через `defineModel`;
  - інкапсулює multi-select з пошуком існуючих тегів через `useTagsSearch`;
  - відображає вже вибрані теги як `TagBadge` усередині поля;
  - поряд із полем — кнопка `+`, що відкриває `CreateTagDialog`;
  - на event `created` з dialog — додає ID нового тега у `modelValue`;
  - PrimeVue `MultiSelect` або `AutoComplete` із `multiple` (вибрати після перевірки можливостей).

- `ViewAllTagsDialog.vue`:
  - props: `visible: boolean`, `recordType: 'task' | 'project'`, `recordId: string`;
  - всередині — `useRecordTags(recordType, recordId)`;
  - відображає всі теги у flex-wrap як `TagBadge`;
  - порожній стан — повідомлення "No tags yet".

## Out Of Scope

- Інтеграція компонентів у `EditTaskPage` / `EditProjectPage` / filters — task 008.
- Редагування name/color існуючого тега.
- Видалення тегів.
- Лічильник використання.

## Expected Behavior

- `TagInput` ніколи не створює тег за рядком — створення тільки через `CreateTagDialog`.
- `CreateTagDialog` ніколи не прив'язує тег до сутності — він тільки повертає створений тег через event.
- Усі компоненти повторно використовують `Tag` тип з `entities/tag`.
- Колір тега — inline background, з контрастним текстом (білий або чорний залежно від яскравості HEX) — мінімальна логіка контрасту.

## Technical Notes

- Структуру `<script setup>` тримати відповідно до CLAUDE.md (imports → types → props/emits → composables → state → computed → methods → watchers → lifecycle → expose).
- Перевірити PrimeVue `MultiSelect` vs `AutoComplete` для `TagInput`. Якщо обидва не підходять — wrapper з власною композицією, але без власної віртуалізації чи власного dropdown.
- `vue3-colorpicker` — тільки в `CreateTagDialog`.
- Не вводити Pinia store для тегів — стану достатньо у TanStack Query кеші.

## Acceptance Criteria

- [ ] Існують усі 5 компонентів за описаними props і поведінкою.
- [ ] `TagInput` показує вибрані теги і дозволяє додавати/видаляти через пошук.
- [ ] `TagInput` має кнопку `+`, яка відкриває `CreateTagDialog`.
- [ ] Після створення тег автоматично додається у `modelValue` `TagInput`.
- [ ] `CreateTagDialog` показує `vue3-colorpicker` з дефолтним рандомним HEX.
- [ ] `ViewAllTagsDialog` показує всі теги сутності у flex-wrap.
- [ ] `TagList` коректно показує 4 теги + кнопку View All.
- [ ] Format, lint, types check проходять.

## Open Questions

- N/A

## Notes For Developer Agent

- Компоненти мають бути page-agnostic — без імпортів з `pages/` чи `widgets/projects/`, `widgets/tasks/`.
- Якщо PrimeVue компонент потребує перевизначення стилів (`pt` API) — обмежитись мінімумом, не переписувати темізацію.
