---
task: 007 - Frontend Tag Components
status: done
---

# 007 - Frontend Tag Components

## What Was Implemented

5 компонентів у `resources/js/entities/tag/ui/`:

- `TagBadge.vue` — PrimeVue `<Tag>` з inline `backgroundColor` + авто-контрастний текст (luminance formula).
- `TagList.vue` — flex-wrap список `TagBadge`. Кнопка "View all" якщо `total > tags.length` або `tags.length === 4` + `recordType` + `recordId`. Відкриває `ViewAllTagsDialog`.
- `CreateTagDialog.vue` — dialog з `name` (InputText) і `color` (vue3-colorpicker). `useCreateTagMutation`, emit `created` з Tag, reset форми.
- `TagInput.vue` — PrimeVue `MultiSelect` з debounced search через `useTagsSearchQuery`. Local `Map<id, Tag>` для резолву вибраних ID у повні об'єкти (навіть якщо не в поточних результатах). Кнопка `+` → `CreateTagDialog`. Після `created` → додає у map і modelValue.
- `ViewAllTagsDialog.vue` — завантажує теги через `useTaskTagsQuery` або `useProjectTagsQuery` залежно від `recordType`. "No tags yet" при порожньому стані.

## Files Created

- `resources/js/entities/tag/ui/TagBadge.vue`
- `resources/js/entities/tag/ui/TagList.vue`
- `resources/js/entities/tag/ui/CreateTagDialog.vue`
- `resources/js/entities/tag/ui/TagInput.vue`
- `resources/js/entities/tag/ui/ViewAllTagsDialog.vue`
- `resources/js/entities/tag/ui/index.ts`

## Files Modified

- `resources/js/entities/tag/index.ts` — додано `export * from './ui'`

## Checks Run

- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
