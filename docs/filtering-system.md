---
id: doc-0001
title: Filtering System
type: specification
created_date: '2026-06-24 19:34'
updated_date: '2026-06-24 19:34'
---
# Filtering System

The filtering system consists of two independent layers: backend filter infrastructure
(`app/Libs/EloquentFilters/`) and the frontend filters module
(`resources/js/shared/filters/`). They are connected by a shared API contract: the
`filters[]` array in search requests.

---

## Backend: EloquentFilters

### Location

```txt
app/Libs/EloquentFilters/
├── Filter.php                  # abstract base
├── FilterPayload.php           # input DTO for one filter item
├── FilterDefinition.php        # registers a filter class for a model
├── FilterResolver.php          # validates payload and returns a Filter instance
├── MatchMode.php               # enum of allowed match modes
├── InvalidFilterException.php  # 400 exception with named constructors
├── Concerns/
│   └── HasFilters.php          # model trait: allowedFilters() + scopeFilter()
└── Filters/
    ├── TextFilter.php          # key: "text"
    ├── IntegerFilter.php       # key: "integer"
    ├── BooleanFilter.php       # key: "boolean"
    ├── DateTimeFilter.php      # key: "datetime"
    ├── NullableFilter.php      # key: "nullable"
    └── LookupFilter.php        # key: "lookup" - filtering by FK fields
```

### API contract

Each item in the `filters[]` request array:

```json
{
  "filter_key": "text",
  "field_name": "name",
  "value": "alpha",
  "matchMode": "contains",
  "params": {}
}
```

| Field | Description |
|---|---|
| `filter_key` | Identifies the filter type. Matches `Filter::key()` for the concrete class. |
| `field_name` | Model field to filter by. Must be present in `allowedFields` for this filter. |
| `value` | Filter value. Type depends on the filter class. |
| `matchMode` | Comparison operator, optional. Some filter classes ignore it. |
| `params` | Extra params. Currently unused by the standard filters. |

### Filter classes

| Class | key | Supported matchModes |
|---|---|---|
| `TextFilter` | `text` | `equals`, `notEquals`, `startsWith`, `endsWith`, `contains`, `notContains` |
| `IntegerFilter` | `integer` | `equals`, `notEquals`, `gt`, `gte`, `lt`, `lte` |
| `BooleanFilter` | `boolean` | none; `matchMode` is ignored |
| `DateTimeFilter` | `datetime` | `equals`, `notEquals`, `gt`, `gte`, `lt`, `lte`, `dateIs`, `dateIsNot`, `dateBefore`, `dateAfter` |
| `NullableFilter` | `nullable` | `equals` -> `whereNull`, `notEquals` -> `whereNotNull` |
| `LookupFilter` | `lookup` | `equals`, `notEquals` |

If `matchMode` is omitted or `null`, the filter uses its default value. For example,
`TextFilter` defaults to `contains`.

### Applying filtering to a model

**Step 1.** Add the `HasFilters` trait to the model and define `allowedFilters()`:

```php
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\TextFilter;
use App\Libs\EloquentFilters\Filters\IntegerFilter;
use App\Libs\EloquentFilters\Filters\LookupFilter;

class TaskModel extends Model
{
    use HasFilters;

    public static function allowedFilters(): array
    {
        return [
            new FilterDefinition(TextFilter::class, ['name', 'description', 'key', 'status']),
            new FilterDefinition(IntegerFilter::class, ['priority']),
            new FilterDefinition(LookupFilter::class, ['project_id', 'task_list_id']),
        ];
    }
}
```

`FilterDefinition` registers which filter class is allowed on which fields.

**Step 2.** Call `->filter($filters)` in the query. The trait adds the Eloquent
`scopeFilter()` scope:

```php
// in a controller or Query class
$tasks = TaskModel::query()
    ->filter($request->input('filters', []))
    ->paginate($perPage);

// or inside a Scout query callback
TaskModel::search($query)
    ->query(fn (Builder $q) => $q->filter($filters))
    ->paginate($perPage);
```

`scopeFilter()` iterates over `filters[]`; for each item it calls
`FilterResolver::resolve()`, then `$filter->apply($query)`.

### FilterResolver validation logic

`FilterResolver::resolve(array $payload, FilterDefinition[] $allowedFilters)` does:

1. Parses the raw array through `FilterPayload::fromArray()` into a DTO with camelCase
   properties.
2. Finds a `FilterDefinition` with the matching `key()` among `allowedFilters`. If none
   exists, throws `InvalidFilterException::unknownFilter()`.
3. Checks `field_name` against `allowedFields`. If not allowed, throws
   `InvalidFilterException::fieldNotAllowed()`.
4. If `matchMode` is not `null`, checks that it is a valid `MatchMode` enum value and
   that the filter supports it.
5. Returns `new FilterClass($payload)`.

### Error handling

`InvalidFilterException` automatically renders as HTTP 400:

```json
{
  "message": "Unknown filter: foo",
  "context": { "filter_key": "foo" }
}
```

| Named constructor | When |
|---|---|
| `unknownFilter($filterKey)` | `filter_key` was not found among `allowedFilters` |
| `fieldNotAllowed($fieldName, $filterKey)` | `field_name` is absent from `allowedFields` for this filter |
| `unknownMatchMode($matchMode)` | `matchMode` is not a valid enum value |
| `unsupportedMatchMode($matchMode, $filterKey)` | The filter class does not support this `matchMode` |

### Adding a new filter class

```php
namespace App\Libs\EloquentFilters\Filters;

use App\Libs\EloquentFilters\Filter;
use App\Libs\EloquentFilters\MatchMode;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

class StatusFilter extends Filter
{
    public static function key(): string
    {
        return 'status';
    }

    public static function supportedMatchModes(): ?array
    {
        return [MatchMode::EQUALS, MatchMode::NOT_EQUALS];
    }

    public function apply(Builder|ScoutBuilder $query): Builder|ScoutBuilder
    {
        $field = $this->payload->fieldName;
        $value = $this->payload->value;

        if (!$field || $value === null || $value === '') {
            return $query;
        }

        return $this->matchMode() === MatchMode::NOT_EQUALS
            ? $query->where($field, '!=', $value)
            : $query->where($field, $value);
    }
}
```

Register it in the model's `allowedFilters()`:

```php
new FilterDefinition(StatusFilter::class, ['status']),
```

### Search request

All search endpoints use one validation class:

```txt
app/Http/WebApi/Requests/Shared/SearchRequest.php
```

The class validates `query`, `filters[]`, `page`, `per_page`, `sort_by`, and
`sort_order`.

### Search endpoints

```txt
POST /api/projects/search
POST /api/task-lists/search
POST /api/tasks/search
Authorization: Bearer {token}
```

```json
{
  "query": "alpha",
  "filters": [
    {
      "filter_key": "text",
      "field_name": "name",
      "value": "alpha",
      "matchMode": "contains",
      "params": {}
    }
  ],
  "page": 1,
  "per_page": 15,
  "sort_by": "name",
  "sort_order": "asc"
}
```

**Important:** for Scout `CollectionEngine`, `->orderBy()` must be at Scout Builder
level, before `->query()`, not inside the `->query()` callback.

### Allowed filters by model

| Model | Filter | Fields |
|---|---|---|
| `ProjectModel` | `TextFilter` | `name`, `prefix` |
| `TaskListModel` | `TextFilter` | `name`, `project_id` |
| `TaskModel` | `TextFilter` | `name`, `description`, `key`, `status` |
| `TaskModel` | `IntegerFilter` | `priority` |
| `TaskModel` | `LookupFilter` | `project_id`, `task_list_id` |

---

## Frontend: shared/filters

### Location

```txt
resources/js/shared/filters/
├── types/
│   ├── filter-def.types.ts     # FilterDef, FilterDefMap, AnyFilterDef, FilterDataType
│   ├── filter-payload.types.ts # FilterPayloadItem (API contract)
│   └── match-mode.types.ts     # MatchMode union, MatchModeOption, constants
├── lib/
│   ├── filter-config.ts        # FILTER_TYPE_CONFIG - config for each dataType
│   ├── filter-factory.ts       # createFilterDefinition(), createFilterDefMap()
│   └── filter-resolver.ts      # resolveFilters() -> FilterPayloadItem[]
├── composables/
│   ├── use.filters.ts          # useFilters() - base composable
│   └── use.filter-sidebar.ts   # useFilterSidebar() - draft/committed state
└── ui/
    ├── FilterControl.vue       # one filter row: toggle + match mode + value input
    ├── FilterList.vue          # renders FilterControl for every entry in the map
    ├── FilterSidebar.vue       # PrimeVue Drawer wrapper
    ├── FilterButton.vue        # button with an active filters count badge
    └── value-inputs/
        ├── TextInput.vue
        ├── IntegerInput.vue
        ├── BooleanInput.vue
        └── DateTimeInput.vue
```

Import only from the barrel file:

```ts
import { ... } from '@/shared/filters'
```

### Key types

```ts
// matches filter_key in the backend API
type FilterDataType = 'text' | 'integer' | 'boolean' | 'datetime' | 'nullable' | 'lookup'

type FilterDef<TDataType extends FilterDataType> = {
    label: string
    fieldName?: string            // set automatically by createFilterDefMap
    dataType: TDataType
    value: FilterValue<TDataType>
    defaultValue: FilterValue<TDataType>
    matchMode: MatchMode | null
    inputProps: Record<string, unknown>
    extraParams?: Record<string, unknown>
    info?: string
    enabled: boolean
    withoutMatchMode?: boolean
    component?: Component         // custom input component overriding the default for dataType
}

// FilterDefMap - Record<fieldName, FilterDef>
type FilterDefMap = Record<string, AnyFilterDef>

// API payload sent to POST /search
type FilterPayloadItem = {
    filter_key: string       // = def.dataType
    field_name: string       // = def.fieldName
    value: unknown
    matchMode: string | null
    params: Record<string, unknown>
}
```

### FILTER_TYPE_CONFIG

`filter-config.ts` contains configuration for each `FilterDataType`:

```ts
type FilterTypeConfig = {
    matchModes: MatchModeOption[]        // available comparison modes
    isEmpty: (value: unknown) => boolean // when to omit from payload
    omitValue?: boolean                  // do not send value, e.g. nullable
    requiresMatchMode?: boolean          // omit when matchMode === null
}

const FILTER_TYPE_CONFIG: Record<FilterDataType, FilterTypeConfig>
```

`resolveFilters()` uses this config instead of an if/else chain. Adding a new type
requires only a new config entry.

### Declarative factory

`createFilterDefMap()` is the main way to define a set of filters:

```ts
const defMap = createFilterDefMap((map) =>
    map
        .addField('name', 'text', (d) => d.label('Name'))
        .addField('priority', 'integer', (d) => d.label('Priority').enabled(false))
        .addField('project_id', 'lookup', (d) =>
            d.label('Project').component(ProjectLookupField).withoutMatchMode()
        )
)
```

`addField(fieldName, dataType, configure)` automatically sets `fieldName` on the
definition.

Available builder methods:

| Method | Description |
|---|---|
| `.label(v)` | Sidebar label. |
| `.value(v)` | Initial value. |
| `.defaultValue(v)` | Value used on reset. |
| `.matchMode(v)` | Initial match mode. |
| `.enabled(v)` | Whether the filter is active when opened. |
| `.withoutMatchMode()` | Hide the match mode select. |
| `.component(v)` | Custom input component instead of the default for `dataType`. |
| `.mergeInputProps(v)` | Merge extra props into the input component props. |
| `.setInputProps(v)` | Replace `inputProps` completely. |
| `.extraParams(v)` | Extra params in the payload. |
| `.info(v)` | Informational text. |

`createFilterDefinition()` for a single filter:

```ts
// fluent builder
const def = createFilterDefinition('text', (d) =>
    d.label('Name').value('').matchMode('contains')
)

// plain partial object, alternative syntax
const def = createFilterDefinition('integer', { label: 'Priority', enabled: false })
```

### Lookup filters and LookupField wrapper components

For filtering by related entities, use the `lookup` data type. Its value is a record ID:
`string | number | null`.

Each entity has a wrapper component in `widgets/` that encapsulates `LookupField` plus
its search query:

```txt
widgets/projects/lookup-field/ui/ProjectLookupField.vue
widgets/task-list/lookup-field/ui/TaskListLookupField.vue
```

Usage in filters:

```ts
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'

createFilterDefMap((map) =>
    map
        .addField('project_id', 'lookup', (d) =>
            d.label('Project').component(ProjectLookupField).withoutMatchMode()
        )
        .addField('task_list_id', 'lookup', (d) =>
            d.label('Task List').component(TaskListLookupField).withoutMatchMode()
        )
)
```

The same components are used in forms (`TaskCreateDialog`, `TaskEditPage`) with the
`object` prop:

```vue
<!-- returns an IProject object -->
<ProjectLookupField v-model="formData.project" :object="true" />

<!-- returns a string ID -->
<ProjectLookupField v-model="formData.project_id" />
```

### resolveFilters - converting state to API payload

```ts
resolveFilters(defMap: FilterDefMap): FilterPayloadItem[]
```

An entry is excluded when:

- `enabled: false`
- `requiresMatchMode: true` and `matchMode === null` (`nullable` type)
- `isEmpty(value) === true`, with logic specific to each `dataType` through
  `FILTER_TYPE_CONFIG`

### Composable: useFilterSidebar

Manages the draft/committed state pattern. Changes in the sidebar are not applied to the
query until Apply is pressed.

```ts
const filterSidebar = useFilterSidebar(initialDefMap)
```

Returns:

| Field | Type | Description |
|---|---|---|
| `visible` | `Ref<boolean>` | Sidebar visibility. |
| `draftDefMap` | `Ref<FilterDefMap>` | Working copy rendered in the sidebar. |
| `resolvedFilters` | `ComputedRef<FilterPayloadItem[]>` | Updates only after `apply()`. |
| `updateFilter` | `(key, patch) => void` | Changes one filter in `draftDefMap`. |
| `apply` | `() => void` | Copies draft state to committed state. |
| `reset` | `() => void` | Resets draft state to initial values. |
| `sidebarProps` | `ComputedRef<object>` | Ready props and handlers for `<FilterSidebar v-bind>`. |
| `buttonProps` | `ComputedRef<object>` | Ready props and handlers for `<FilterButton v-bind>`. |

`sidebarProps` includes `visible`, `defMap`, `onChange`, `onApply`, and `onReset`, fully
covering the wiring between composable and component.

Open behavior: `watch(visible)` copies committed state to draft state, so opening the
sidebar always shows the currently applied filters. Cancel or Escape discards unsaved
changes.

### Full page integration example

```vue
<script setup lang="ts">
import { computed, ref } from 'vue'
import { FilterSidebar, FilterButton, createFilterDefMap, useFilterSidebar } from '@/shared/filters'
import { useTasksSearchQuery } from '@/entities/task/queries'

const filterSidebar = useFilterSidebar(
    createFilterDefMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('priority', 'integer', (d) => d.label('Priority'))
    )
)

const page = ref(1)

const searchParams = computed(() => ({
    filters: filterSidebar.resolvedFilters.value,
    page: page.value,
    per_page: 15,
}))

const { tasks } = useTasksSearchQuery(searchParams)
</script>

<template>
    <FilterButton v-bind="filterSidebar.buttonProps.value" />

    <FilterSidebar v-bind="filterSidebar.sidebarProps.value" @apply="page = 1" />
</template>
```

`v-bind` on `sidebarProps` automatically passes `visible`, `defMap`, `onChange`,
`onApply`, and `onReset`. The extra `@apply` is used for page-specific side effects,
such as resetting pagination.

### Adding a new filter type on the frontend

1. Add a new `FilterDataType` to `filter-def.types.ts`.
2. Add the corresponding `FilterValue` to `FilterValueMap`.
3. Add an entry to `FILTER_TYPE_CONFIG` in `filter-config.ts` with `matchModes`,
   `isEmpty`, and optional flags.
4. Create `NewTypeInput.vue` in `value-inputs/` if a default input is needed.
5. Add an entry to `DATA_TYPE_COMPONENTS` in `FilterControl.vue`.
6. Implement the matching backend `Filter` class.
