---
id: doc-0004
title: Persisted List State
type: specification
created_date: '2026-07-11 16:00'
updated_date: '2026-07-11 16:00'
---
# Persisted List State

Persists a list page's filters and sorting to browser storage and restores them on
reload. Builds on top of the [Filtering System](./filtering-system.md) and
[Sorting System](./sorting-system.md) — read those first for `useFilterSidebar` /
`useSortDialog`.

---

## Frontend: shared/composables

### Location

```txt
resources/js/shared/composables/
└── use.persisted-list-state.ts   # usePersistedListState() composable
```

Import from the barrel file:

```ts
import { usePersistedListState } from '@/shared/composables'
```

### Composable: usePersistedListState

```ts
usePersistedListState(state, options?)
```

| Param | Type | Description |
|---|---|---|
| `state` | `Record<string, { value: unknown }>` | Refs (or writable computeds) to persist and restore. Values must be JSON-serializable. |
| `options.key` | `string` | Storage key suffix. Defaults to `route.path`, which already scopes nested pages (e.g. `/projects/:id/tasks`) per record. |
| `options.ttlMs` | `number` | Time to live before a saved state is considered stale. Defaults to 24 hours. |
| `options.storage` | `'session' \| 'local'` | Storage backend. Defaults to `'session'` (`sessionStorage`). |
| `options.validate` | `(data: Record<string, unknown>) => boolean` | Rejects a restored snapshot before it is applied (e.g. a `sortBy` no longer among the known fields). Optional. |

Behavior:

1. Reads `list-state:${options.key ?? route.path}` from the chosen storage on setup.
2. If the stored envelope is missing, expired, or fails `validate`, the storage entry is
   removed and nothing is restored.
3. Otherwise, each key present in the stored data is assigned into the matching ref in
   `state`.
4. A `watch` on the combined state writes `{ data, expiresAt: Date.now() + ttlMs }` back
   to storage on every change — including a filter `clear()`/`reset()` or a new committed
   sort, since those mutate the same refs.

The TTL is a **sliding window**: every write refreshes `expiresAt`, so state kept alive by
continued use never goes stale mid-session.

### Page integration example

```ts
import { useFilterSidebar } from '@/shared/filters'
import { useSortDialog } from '@/shared/sort'
import { usePersistedListState } from '@/shared/composables'
import { taskSortFieldDefs, createDefaultTaskFiltersDefMap } from '@/entities/task/config'

const filterSidebar = useFilterSidebar(createDefaultTaskFiltersDefMap())
const sort = useSortDialog(taskSortFieldDefs, 'updated_at', 'desc')

usePersistedListState(
    {
        filters: filterSidebar.filtersSnapshot,
        sortBy: sort.sortBy,
        sortOrder: sort.sortOrder,
    },
    {
        validate: (data) =>
            taskSortFieldDefs.some((f) => f.field === data.sortBy) &&
            (data.sortOrder === 'asc' || data.sortOrder === 'desc'),
    }
)
```

Call it right after `filterSidebar`/`sort` are created, before any query params are
built, so the restored values are already in place for the first search request.

Wired into `TasksPage.vue`, `ProjectsPage.vue`, and `ProjectTasksPage.vue`. Pages without
filter/sort UI (e.g. `ProjectTaskListsPage.vue`) don't use it. Free-text search and the
current page number are intentionally **not** persisted — they always reset on reload.

### `filterSidebar.filtersSnapshot`

`useFilterSidebar` (see [Filtering System](./filtering-system.md)) exposes a writable
computed `filtersSnapshot` specifically for this use case:

```ts
type FilterStateSnapshot = Record<string, { value: unknown; matchMode: string | null; enabled: boolean }>
```

- **get** projects the committed `FilterDefMap` down to just `{ value, matchMode,
  enabled }` per field — no `component`, `label`, or `inputProps`, which are not
  JSON-serializable (a `component` is a Vue component reference).
- **set** merges those fields back into the *current* def map by key, so a def field that
  no longer exists in the current filter schema (e.g. after a filter was renamed/removed)
  is silently ignored rather than applied or crashing.
- **set** revives `datetime` values: JSON turns a `Date` into an ISO string on the way
  into storage, so on restore, any field with `dataType === 'datetime'` gets its string
  value converted back with `new Date(...)`.

Do not persist the raw `committedDefMap` directly — it carries live component references
that cannot round-trip through `JSON.stringify`.

### Why not persist search query and pagination

Acceptance criteria for this feature covers filters and sorting only. Search query and
page number reset on every page load by design, to avoid showing a stale page number
against a result set that may have changed since the last visit.
