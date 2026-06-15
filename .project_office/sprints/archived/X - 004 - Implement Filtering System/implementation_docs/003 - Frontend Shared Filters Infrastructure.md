---
task: "003 - Frontend Shared Filters Infrastructure"
status: done
---

# 003 - Frontend Shared Filters Infrastructure

## What Was Implemented

Shared frontend filters module в `resources/js/shared/filters/`. Містить TypeScript types, декларативну фабрику, resolver і generic UI components. Повністю entity-agnostic — без Projects-specific logic.

## Module Structure

```
resources/js/shared/filters/
├── types/
│   ├── filter-def.types.ts       # FilterDef, FilterDefMap, AnyFilterDef, FilterDataType, FilterValue
│   ├── filter-payload.types.ts   # FilterPayloadItem (API contract)
│   └── match-mode.types.ts       # MatchMode union, MatchModeOption, constants per data type
├── lib/
│   ├── filter-config.ts          # MATCH_MODE_OPTIONS: Record<FilterDataType, MatchModeOption[]>
│   ├── filter-factory.ts         # createFilterDefinition(), createFiltersDefinitionsMap()
│   └── filter-resolver.ts        # resolveFilters() → FilterPayloadItem[]
├── composables/
│   └── use.filters.ts            # useFilters() composable
├── ui/
│   ├── FilterControl.vue         # single filter row (checkbox + match mode + value input)
│   ├── FilterGroup.vue           # iterates FilterDefMap, renders FilterControl per entry
│   ├── FilterSidebar.vue         # PrimeVue Drawer wrapper з apply/reset actions
│   └── value-inputs/
│       ├── TextValueInput.vue
│       ├── IntegerValueInput.vue
│       ├── BooleanValueInput.vue
│       └── DateTimeValueInput.vue
└── index.ts                      # єдиний barrel export на рівні модуля
```

Внутрішні папки не мають barrel `index.ts`. Всі imports всередині модуля вказують напряму на файл.

## Public API

```ts
// types
export type { FilterDataType, FilterDef, AnyFilterDef, FilterDefMap, FilterValue }
export type { FilterPayloadItem }
export type { MatchMode, MatchModeOption }
export { TEXT_MATCH_MODES, INTEGER_MATCH_MODES, DATETIME_MATCH_MODES, NULLABLE_MATCH_MODES }

// config
export { MATCH_MODE_OPTIONS }

// factory
export { createFilterDefinition, createFiltersDefinitionsMap }

// resolver
export { resolveFilters }

// composable
export { useFilters }

// UI components
export { FilterControl, FilterGroup, FilterSidebar }
```

## Types

### FilterDef

```ts
type FilterDef<TDataType extends FilterDataType> = {
    label: string
    fieldName?: string          // встановлюється через createFiltersDefinitionsMap.addField()
    dataType: TDataType
    value: FilterValue<TDataType>
    defaultValue: FilterValue<TDataType>
    matchMode: MatchMode | null
    inputProps: Record<string, unknown>   // props для value input компонента
    extraParams?: Record<string, unknown> // додаткові backend params
    info?: string
    enabled: boolean
    withoutMatchMode?: boolean
}
```

`filterKey` у `FilterDef` відсутній — resolver використовує `def.dataType` як `filter_key` у payload. Це відповідає backend contract, де `TextFilter::key() === 'text'`, `IntegerFilter::key() === 'integer'` тощо.

### FilterPayloadItem (API contract)

```ts
type FilterPayloadItem = {
    filter_key: string
    field_name: string
    value: unknown
    matchMode: string | null
    params: Record<string, unknown>
}
```

### FilterValue (lookup type)

```ts
type FilterValueMap = {
    text: string | null
    integer: number | null
    boolean: boolean | null
    datetime: Date | null
    nullable: null
}

type FilterValue<TDataType extends FilterDataType> = FilterValueMap[TDataType]
```

## Factory (declarative builder)

```ts
// fluent callback
const def = createFilterDefinition('text', (d) =>
    d.label('Name').value('').withoutMatchMode(false)
)

// plain partial object
const def = createFilterDefinition('integer', { label: 'Priority', enabled: false })

// map builder
const defMap = createFiltersDefinitionsMap((map) =>
    map
        .addField('name', 'text', (d) => d.label('Name'))
        .addField('status', 'boolean', (d) => d.label('Active'))
)
```

`addField(fieldName, dataType, configure)` автоматично встановлює `fieldName` на def зі значення першого аргумента.

## Resolver

`resolveFilters(defMap)` → `FilterPayloadItem[]`

Правила:
- пропускає `enabled: false` фільтри
- для `nullable` включає тільки якщо `matchMode === 'equals' | 'notEquals'`
- для `boolean` включає тільки якщо `value !== null`
- пропускає `value === null | undefined | ''` для решти типів
- `filter_key` береться з `def.dataType`
- `params` береться з `def.extraParams ?? {}`

## Composable

```ts
const { defMap, resolvedFilters, hasActiveFilters, updateFilter, resetFilters } = useFilters(initialDefMap)
```

- `defMap` — `Ref<FilterDefMap>`, реактивний стан
- `resolvedFilters` — `ComputedRef<FilterPayloadItem[]>`, автоматично реагує на зміни
- `hasActiveFilters` — `ComputedRef<boolean>`
- `updateFilter(key, patch)` — оновлює один filter
- `resetFilters()` — скидає всі до `defaultValue`, `matchMode: null`, `enabled: true`

## UI Components

### FilterSidebar

```vue
<FilterSidebar
    v-model:visible="sidebarVisible"
    :def-map="defMap"
    title="Filters"
    @apply="onApply"
    @reset="onReset"
    @change="updateFilter"
/>
```

PrimeVue `Drawer` (position right, `!w-80`). Apply закриває sidebar + emits `apply`. Reset emits `reset` без закриття.

### FilterGroup

Ітерує `FilterDefMap`, рендерить `FilterControl` для кожного entry.

### FilterControl

Checkbox (enabled toggle) + Select для match mode + value input компонент за `dataType`. Match mode options беруться з `MATCH_MODE_OPTIONS[def.dataType]` — object lookup без switch.

## Key Decisions

- **`filterKey` видалено з `FilterDef`** — `dataType` є filter key (відповідає backend `Filter::key()`). Усуває дублювання.
- **Декларативна фабрика** — fluent builder замість plain object options. Підтримує обидва стилі (`function` callback і `Partial<FilterDef>`).
- **`inputProps` замість `params`** — розділено UI props (`inputProps`) і backend params (`extraParams`).
- **Єдиний barrel на рівні модуля** — внутрішні `index.ts` видалені, зовнішній код імпортує тільки з `shared/filters`.
- **`MATCH_MODE_OPTIONS` у `filter-config.ts`** — централізований об'єкт замість switch у компоненті.
- **`vue/no-deprecated-filter` workaround** — TypeScript union casts `as T | null` у template атрибутах помилково спрацьовують на цьому правилі. Вирішено через helper functions (`asString`, `asNumber`, тощо) у script setup.

## Files Created

| File | Purpose |
|---|---|
| `resources/js/shared/filters/types/filter-def.types.ts` | Core filter definition types |
| `resources/js/shared/filters/types/filter-payload.types.ts` | API payload contract |
| `resources/js/shared/filters/types/match-mode.types.ts` | Match mode types і constants |
| `resources/js/shared/filters/lib/filter-config.ts` | `MATCH_MODE_OPTIONS` lookup object |
| `resources/js/shared/filters/lib/filter-factory.ts` | Declarative builder factory |
| `resources/js/shared/filters/lib/filter-resolver.ts` | Filter state → API payload |
| `resources/js/shared/filters/composables/use.filters.ts` | Reactive filter state composable |
| `resources/js/shared/filters/ui/FilterControl.vue` | Single filter row component |
| `resources/js/shared/filters/ui/FilterGroup.vue` | Filter map renderer |
| `resources/js/shared/filters/ui/FilterSidebar.vue` | Full-height Drawer sidebar |
| `resources/js/shared/filters/ui/value-inputs/TextValueInput.vue` | Text input wrapper |
| `resources/js/shared/filters/ui/value-inputs/IntegerValueInput.vue` | Number input wrapper |
| `resources/js/shared/filters/ui/value-inputs/BooleanValueInput.vue` | Boolean select wrapper |
| `resources/js/shared/filters/ui/value-inputs/DateTimeValueInput.vue` | Date picker wrapper |
| `resources/js/shared/filters/index.ts` | Public module API |

## Checks Run

- `npm run format` — clean
- `npx eslint resources/ --fix` — 0 errors
- `npm run types:check` — 0 errors

## Notes For Next Agent

- Task 004 інтегрує цей модуль у Projects table view.
- `FilterSidebar` відкривається через `v-model:visible`. Кнопка "Filters" над таблицею має керувати цим значенням.
- `useFilters(initialDefMap)` приймає `FilterDefMap` — його потрібно визначити через `createFiltersDefinitionsMap()` у Projects-specific composable.
- Після `@apply` потрібно передати `resolvedFilters.value` у `POST /api/projects/search` як `filters[]`.
