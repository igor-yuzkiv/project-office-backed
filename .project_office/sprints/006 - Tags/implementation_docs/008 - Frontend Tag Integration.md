---
task: 008 - Frontend Tag Integration
status: done
---

# 008 - Frontend Tag Integration

## What Was Implemented

### TaskEditPage
- `tag_ids: string[]` у `TaskEditFormData`, ініціалізація з `task.tags?.map(tag => tag.id) ?? []`.
- `TagInput` у формі.
- `tag_ids` передається у `IUpdateTaskInput` при submit.

### ProjectUpsertDialog
- `tagIds` ref у `use.project-upsert-dialog.ts`, ініціалізація з `project?.tags`.
- `tagIds` prop + `update:tagIds` emit у `ProjectUpsertDialog.vue`.
- `TagInput` у формі. Підтримка create і update flow.

### Overview сторінки
- `TaskOverviewPage` — `<TagList>` у `<DisplayField label="Tags">`.
- `ProjectOverviewPage` — те саме для project.

### Filter infrastructure
- Новий тип `'tags': string[] | null` у `FilterValueMap`.
- `tags` config у `FILTER_TYPE_CONFIG`: `isEmpty` на порожній масив, без `matchModes`.
- `tags: null` у `DATA_TYPE_COMPONENTS` у `FilterControl.vue` (компонент береться з `def.component`).
- `TagFilterInput.vue` — `defineModel<string[] | null>`, PrimeVue `MultiSelect` з `useTagsSearchQuery`.
- `task-filters.config.ts` — зареєстровано tags filter з `TagFilterInput`.
- `ProjectsPage` — зареєстровано tags filter аналогічно.

## Files Created

- `resources/js/entities/tag/ui/TagFilterInput.vue`

## Files Modified

- `resources/js/entities/tag/ui/index.ts`
- `resources/js/entities/task/types/task.types.ts`
- `resources/js/entities/project/types/project.types.ts`
- `resources/js/pages/tasks/edit/TaskEditPage.vue`
- `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue`
- `resources/js/pages/projects/details/tabs/ProjectOverviewPage.vue`
- `resources/js/widgets/projects/upsert-dialog/ui/ProjectUpsertDialog.vue`
- `resources/js/widgets/projects/upsert-dialog/composables/use.project-upsert-dialog.ts`
- `resources/js/pages/projects/list/ProjectsPage.vue`
- `resources/js/shared/filters/types/filter-def.types.ts`
- `resources/js/shared/filters/lib/filter-config.ts`
- `resources/js/shared/filters/ui/FilterControl.vue`
- `resources/js/entities/task/config/task-filters.config.ts`

## Checks Run

- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
