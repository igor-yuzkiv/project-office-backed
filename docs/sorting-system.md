---
id: doc-0003
title: Sorting System
type: specification
created_date: '2026-06-24 19:34'
updated_date: '2026-06-24 19:34'
---
# Sorting System

The sorting system consists of the frontend `resources/js/shared/sort/` module and the
backend `SortParams` DTO. Sorting parameters are sent with each search request alongside
filters.

---

## Frontend: shared/sort

### Location

```txt
resources/js/shared/sort/
├── types/
│   └── sort.types.ts          # SortDirection, SortFieldDef, SortParams
├── composables/
│   └── use.sort-dialog.ts     # useSortDialog() composable
├── ui/
│   ├── SortButton.vue         # button with the current field in the label
│   └── SortDialog.vue         # PrimeVue Dialog for choosing field and direction
└── index.ts                   # barrel export
```

Import only from the barrel file:

```ts
import { ... } from '@/shared/sort'
```

### Types

```ts
type SortDirection = 'asc' | 'desc'

type SortFieldDef = {
    field: string   // field name sent as sort_by
    label: string   // display text in the UI
}

// sent with other API request params
type SortParams = {
    sort_by?: string
    sort_order?: SortDirection
}
```

`SortParams` is used by API function parameter types, for example `ProjectSearchParams`,
to guarantee that `sort_by` / `sort_order` can be passed to the request.

### Composable: useSortDialog

Manages dialog visibility and implements the draft/committed state pattern. Changing
the sort field inside the dialog does not affect the query until Apply is pressed.

```ts
const sort = useSortDialog(
    fields,        // SortFieldDef[] - available fields
    defaultField,  // string - default field (optional, defaults to the first field)
    defaultOrder   // SortDirection - default direction (defaults to 'asc')
)
```

Returns:

| Property / method | Type | Description |
|---|---|---|
| `visible` | `Ref<boolean>` | Dialog visibility. |
| `sortBy` | `Ref<string>` | **Committed** field used in query params. |
| `sortOrder` | `Ref<SortDirection>` | **Committed** direction. |
| `draftSortBy` | `Ref<string>` | Field inside the open dialog; changes before Apply. |
| `draftSortOrder` | `Ref<SortDirection>` | Direction inside the open dialog. |
| `activeSortLabel` | `ComputedRef<string>` | Label of the committed field, used by the button. |
| `open()` | `() => void` | Syncs draft from committed state and opens the dialog. |
| `close()` | `() => void` | Closes the dialog without saving. |
| `setDraftField(field)` | `(string) => void` | Updates `draftSortBy`. |
| `setDraftOrder(order)` | `(SortDirection) => void` | Updates `draftSortOrder`. |
| `apply()` | `() => void` | Copies draft state to committed state. |
| `reset()` | `() => void` | Resets draft state to defaults. |

**Important:** `open()` always syncs draft state from committed state before opening.
If the user opens the dialog, changes something, and closes without Apply, the next
open shows the currently applied sorting, not unsaved changes.

### UI components

**SortButton** displays the current sort field in its label:

```vue
<SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
```

Props: `label?: string`. Emits: `click: []`; no `MouseEvent` is needed because the dialog
does not need an anchor position.

**SortDialog** is a PrimeVue Dialog with two Select controls and Cancel/Apply buttons:

```vue
<SortDialog
    :visible="sort.visible.value"
    :fields="sortFieldDefs"
    :sort-by="sort.draftSortBy.value"
    :sort-order="sort.draftSortOrder.value"
    @update:visible="sort.visible.value = $event"
    @update:sort-by="sort.setDraftField"
    @update:sort-order="sort.setDraftOrder"
    @apply="onSortApply"
/>
```

`@apply` and `@update:visible` are separate events, so Apply and Cancel are handled
independently.

### Full page integration example

```vue
<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useSortDialog, SortButton, SortDialog, type SortFieldDef } from '@/shared/sort'

const sortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

// default sorting: updated_at desc
const sort = useSortDialog(sortFieldDefs, 'updated_at', 'desc')

const page = ref(1)

const searchParams = computed(() => ({
    page: page.value,
    per_page: 15,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
}))

function onSortApply() {
    sort.apply()
    sort.close()
}

// reset pagination when sorting changes
watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})
</script>

<template>
    <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />

    <SortDialog
        :visible="sort.visible.value"
        :fields="sortFieldDefs"
        :sort-by="sort.draftSortBy.value"
        :sort-order="sort.draftSortOrder.value"
        @update:visible="sort.visible.value = $event"
        @update:sort-by="sort.setDraftField"
        @update:sort-order="sort.setDraftOrder"
        @apply="onSortApply"
    />
</template>
```

---

## Backend: Sort params

### SortParams DTO

```php
// app/Infrastructure/DTO/SortParams.php
class SortParams
{
    public function __construct(
        public string $field = 'created_at',
        public string $direction = 'desc',
    ) {}
}
```

Defaults: `created_at desc`. Any endpoint that does not receive explicit sort params
returns this order.

### How sort params reach controllers

The base `Controller` provides `getSortParams()`:

```php
// in any controller
$sort = $this->getSortParams(); // reads sort_by, sort_order from the request
```

It returns a `SortParams` instance with `field` and `direction`.

### Sorting regular Eloquent queries

```php
$projects = ProjectModel::query()
    ->orderBy($sort->field, $sort->direction)
    ->paginate($pagination->perPage, page: $pagination->page);
```

### Sorting Scout queries

When using Laravel Scout with `CollectionEngine`, sorting must be applied at the Scout
Builder level, **before** the `->query()` callback:

```php
ProjectModel::search($query)
    ->orderBy($sort->field, $sort->direction)  // correct: Scout Builder level
    ->query(function (Builder $q) use ($filters): Builder {
        return $q->filter($filters);
        // Do not add orderBy here; CollectionEngine ignores it when building result order.
    })
    ->paginate($perPage, 'page', $page);
```

**Why:** `CollectionEngine::searchModels()` orders results exclusively through
`$builder->orders` on the Scout Builder. `orderBy` inside the `->query()` callback reaches
`queryScoutModelsByIds()` during re-fetch, but `map()` then overwrites the order with
positions from `searchModels()`, which were built without considering the query callback.

### SearchProjectsQuery

Project search logic is extracted from the controller into
`app/Domains/Project/Queries/SearchProjectsQuery.php`:

```php
class SearchProjectsQuery
{
    public function __construct(
        private readonly string $query,
        private readonly array $filters,
        private readonly SortParams $sort,
        private readonly PaginationParams $pagination,
    ) {}

    public function run(): LengthAwarePaginator
    {
        return ProjectModel::search($this->query)
            ->orderBy($this->sort->field, $this->sort->direction)
            ->query(function (Builder $q): Builder {
                /** @var Builder<ProjectModel> $q */
                return $q->with(['createdBy', 'updatedBy'])->filter($this->filters);
            })
            ->paginate($this->pagination->perPage, 'page', $this->pagination->page);
    }
}
```

The controller only delegates:

```php
public function search(Request $request): AnonymousResourceCollection
{
    $projects = (new SearchProjectsQuery(
        query: (string) $request->input('query', ''),
        filters: (array) $request->input('filters', []),
        sort: $this->getSortParams(),
        pagination: $this->getPaginationParams(),
    ))->run();

    return ProjectResource::collection($projects);
}
```

### Adding sorting to a new search endpoint

1. Read `sort_by` and `sort_order` from the request through `$this->getSortParams()`.
2. Pass `SortParams` into a Query class or use it inline.
3. For Scout: call `->orderBy($sort->field, $sort->direction)` before `->query()`.
4. For Eloquent: call `->orderBy($sort->field, $sort->direction)` on the Builder.
5. On the frontend: add `sort_by` and `sort_order` to the API function params object
   through the `SortParams` type.
