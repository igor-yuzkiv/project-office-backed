---
task: "004 - Integrate Project Table Filters"
status: done
---

# 004 - Integrate Project Table Filters

## What Was Implemented

Toolbar над Projects table з search field і Filters button, full-height filter sidebar з підтримкою `name` і `prefix` filters, end-to-end інтеграція з `POST /api/projects/search`.

## Files Created

| File | Purpose |
|---|---|
| `resources/js/entities/project/queries/use.projects-search.query.ts` | Query composable для search endpoint |
| `resources/js/shared/filters/composables/use.filter-sidebar.ts` | Generic sidebar state composable |
| `resources/js/shared/filters/ui/FiltersButton.vue` | Filters button з badge-індикатором активних фільтрів |
| `resources/js/shared/components/input/ui/SearchInput.vue` | Search input з іконкою-кнопкою лупи |
| `resources/js/shared/components/input/index.ts` | Barrel export для input components |
| `resources/js/shared/components/display/ui/DisplayDate.vue` | Компонент для форматування дати |
| `resources/js/shared/components/display/index.ts` | Barrel export для display components |

## Files Modified

| File | Change |
|---|---|
| `resources/js/entities/project/api/project.api.ts` | Додано `searchProjectsRequest` |
| `resources/js/entities/project/types/project.types.ts` | Додано `ProjectSearchParams` |
| `resources/js/entities/project/config/index.ts` | Додано `ProjectQueryKey.search()` |
| `resources/js/entities/project/queries/index.ts` | Re-export нового query |
| `resources/js/shared/filters/index.ts` | Експорт `FiltersButton`, `useFilterSidebar` |
| `resources/js/shared/filters/ui/FilterControl.vue` | Checkbox замінено на `Panel` toggleable; `pt` override для Zoho-like стилю |
| `resources/js/shared/filters/ui/FilterGroup.vue` | Прибрано `gap-4` — роздільником служить border Panel |
| `resources/js/shared/filters/ui/FilterSidebar.vue` | Ширина `!w-80` → `!w-96` |
| `resources/js/pages/projects/ProjectsPage.vue` | Toolbar, sidebar integration, порядок блоків за CLAUDE.md |

## Key Decisions

- **Завжди search endpoint** — `ProjectsPage` завжди використовує `POST /api/projects/search`, навіть без активних фільтрів (порожній `query` + `filters: []` повертає всі проекти).
- **Draft state** — `useFilterSidebar` тримає `committedDefMap` (для query) і `draftDefMap` (для sidebar). Cancel/Escape відкидає draft. Apply копіює draft → committed.
- **resolveFilters тільки в apply()** — `resolvedFilters` є `ref`, не `computed`. Оновлюється один раз при Apply, не реактивно при кожній зміні контролів.
- **Panel toggle = enabled** — `collapsed = !enabled`: expanded panel → filter enabled, collapsed → disabled. Виправлено: початкова реалізація мала інвертовану логіку.
- **`ProjectSearchParams`** — тип перенесено з `project.api.ts` до `project.types.ts`.
- **Panel pt override** — PrimeVue Aura Panel стилізується через `pt` з Tailwind `!important`: тільки нижній border, без rounded, без shadow, компактні відступи.
- **Script block order** — `ProjectsPage.vue` реорганізовано за CLAUDE.md: composables → state → functions.

## Composable: useFilterSidebar

```ts
const {
    visible,       // Ref<boolean> — видимість sidebar
    draftDefMap,   // Ref<FilterDefMap> — робоча копія для sidebar
    resolvedFilters, // Ref<FilterPayloadItem[]> — оновлюється тільки в apply()
    updateFilter,  // (key, patch) => void
    apply,         // () => void — копіює draft → committed, resolves filters
    reset,         // () => void — скидає draft до initialDefs
} = useFilterSidebar(initialDefs)
```

## Checks Run

- `npm run format` — clean
- `npx eslint resources/ --fix` — 0 errors
- `npm run types:check` — 0 errors
