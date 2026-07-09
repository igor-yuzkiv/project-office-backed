---
id: doc-0002
title: Include System
type: specification
created_date: '2026-06-24 19:34'
updated_date: '2026-06-24 19:34'
---
# Include System

Mechanism for optionally loading related resources into API responses. It lets the
client declare which relations are needed for a request and avoid over-fetching.

---

## Backend

### ResourceController

The shared base class for resource controllers (`app/Http/WebApi/Controllers/ResourceController.php`).
It replaces the old base `Controller` and provides:

- `getPaginationParams()` and `getSortParams()` - shared helpers.
- `getAllowedIncludes()` - **abstract**; each controller defines which Eloquent
  relations clients may request.
- `resolveIncludes(required, requested)` - merges required and requested includes.
- `parseRequestedIncludes()` - reads `include` from the request.

### getAllowedIncludes

Each controller implements this method and returns **Eloquent relation names**, the
same names used by `->with()`:

```php
protected function getAllowedIncludes(): array
{
    return ['project', 'taskList', 'createdBy', 'updatedBy', 'tags'];
}
```

### resolveIncludes

```php
protected function resolveIncludes(array $required, array $requested): array
```

- `$required` - relations that are **always** loaded for this action.
- `$requested` - relations requested by the client, usually from `parseRequestedIncludes()`.

The method validates every requested item against `getAllowedIncludes()`. If a
relation is not allowed, it throws `InvalidIncludeException` with HTTP 422. Allowed
items are merged with `$required`, with duplicates removed.

The result is passed into `->with()` or `->load()`:

```php
public function index(): AnonymousResourceCollection
{
    $includes = $this->resolveIncludes(
        required:  ['createdBy', 'updatedBy', 'tags'],
        requested: $this->parseRequestedIncludes(),
    );

    $tasks = TaskModel::with($includes)->paginate(...);

    return TaskResource::collection($tasks);
}
```

For `show()`, the action passes all mandatory relations through `$required`, while
`$requested` lets the client add more:

```php
public function show(TaskModel $task): TaskResource
{
    $task->load($this->resolveIncludes(
        required:  ['createdBy', 'updatedBy', 'project', 'taskList', 'tags'],
        requested: $this->parseRequestedIncludes(),
    ));

    return new TaskResource($task);
}
```

### InvalidIncludeException

`app/Infrastructure/Exceptions/InvalidIncludeException.php` is thrown when the client
requests a relation that is not present in `getAllowedIncludes()`.

It renders as HTTP 422:

```json
{
  "message": "Include 'fooBar' is not allowed. Allowed includes: project, taskList, createdBy, updatedBy, tags."
}
```

### Resource: whenLoaded

In `toArray()`, each include is rendered through `whenLoaded()`. The field appears in
the response only when the relation was loaded. For collections, `::collection()` stays
inside the callback:

```php
// singular relation - absent when not loaded
'project' => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),

// collection - absent when not loaded (not [], absent)
'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
```

---

## API Contract

### Request format

`include` is sent as:

- **GET** (`index`, `show`): query parameter with a comma-separated string.
  ```txt
  GET /api/tasks?include=project,taskList
  ```
- **POST** (`search`): array in the request body.
  ```json
  { "include": ["project", "taskList"] }
  ```

The backend handles both formats through `parseRequestedIncludes()`.

### Errors

| Situation | HTTP | Response |
|---|---|---|
| relation is not in `getAllowedIncludes()` | 422 | `{ "message": "Include 'X' is not allowed. Allowed includes: ..." }` |

### Allowed includes by entity

| Entity | Allowed includes |
|---|---|
| **Project** | `createdBy`, `updatedBy`, `tags`, `tasks`, `taskLists` |
| **Task** | `project`, `taskList`, `createdBy`, `updatedBy`, `tags` |
| **TaskList** | `tasks`, `project`, `createdBy`, `updatedBy` |
| **ProjectDocument** | `project`, `tags`, `tasks`, `createdBy`, `updatedBy` |
| **Attachment** | `createdBy`, `updatedBy` |

### Required includes by action

Even without an `include` parameter, some relations are always loaded through the
action-specific `$required` list:

| Entity | Action | Required |
|---|---|---|
| **Project** | `index`, `search`, `show` | `createdBy`, `updatedBy`, `tags` |
| **Task** | `index`, `search` | `createdBy`, `updatedBy`, `tags` |
| **Task** | `show` | `createdBy`, `updatedBy`, `tags`, `project`, `taskList` |
| **TaskList** | `index`, `search`, `show` | `createdBy`, `updatedBy` |
| **ProjectDocument** | `index` | `tags` |
| **ProjectDocument** | `show`, `store`, `update` | `project`, `tags`, `tasks`, `createdBy`, `updatedBy` |
| **Attachment** | `search` | `createdBy`, `updatedBy` |

---

## Frontend

### Structure

Each entity keeps API types in a separate `*.api.types.ts` file inside
`entities/{entity}/types/`:

```txt
entities/{entity}/types/
├── {entity}.types.ts       # entity interface, overview DTO
├── {entity}.api.types.ts   # *Include, *FetchParams, *SearchParams, ICreate*Input, IUpdate*Input
└── index.ts                # re-exports both files
```

### Include types

Each entity has a union type of Eloquent relation names, matching the backend names:

```ts
// task.api.types.ts
export type TaskInclude = 'project' | 'taskList' | 'createdBy' | 'updatedBy' | 'tags'

// project.api.types.ts
export type ProjectInclude = 'createdBy' | 'updatedBy' | 'tags' | 'tasks' | 'taskLists'

// task_list.api.types.ts
export type TaskListInclude = 'tasks' | 'project' | 'createdBy' | 'updatedBy'

// project-document.api.types.ts
export type ProjectDocumentInclude = 'project' | 'tags' | 'tasks' | 'createdBy' | 'updatedBy'

// attachment.api.types.ts
export type AttachmentInclude = 'createdBy' | 'updatedBy'
```

### Entity interfaces

Relation fields in entity interfaces are optional (`?`) because their presence depends
on whether `include` was requested:

```ts
export interface ITask extends IEntity {
    // ... scalar fields ...

    project?:    ProjectOverviewDto
    task_list?:  ITaskList
    created_by?: UserOverviewDto
    updated_by?: UserOverviewDto
    tags?:       ITag[]
}
```

Field names are `snake_case` and match Resource `toArray()` keys.

### Params types

`include` is part of fetch and search params types:

```ts
export type TaskFetchParams = PagingParams &
    SortParams & {
        include?: TaskInclude[]
    }

export type TaskSearchParams = PagingParams &
    SortParams & {
        query?:   string
        filters?: FilterPayloadItem[]
        include?: TaskInclude[]
    }
```

### API functions

**GET** serializes `include` as a comma-separated string:

```ts
export async function fetchTasksRequest(params?: TaskFetchParams): PromisePaginatedResponse<ITask> {
    const { include, ...rest } = params ?? {}
    return httpClient
        .get('/tasks', { params: { ...rest, include: include?.join(',') } })
        .then((res) => res.data)
}
```

**POST** sends `include` as an array in the request body:

```ts
export async function searchTasksRequest(params: TaskSearchParams): PromisePaginatedResponse<ITask> {
    const { query = '', filters = [], include, ...pagination } = params
    return httpClient
        .post('/tasks/search', { query, filters, include, ...pagination })
        .then((res) => res.data)
}
```

### Component usage

```ts
const searchParams = computed(() => ({
    query:      searchQuery.value,
    filters:    filterSidebar.resolvedFilters.value,
    page:       page.value,
    per_page:   PAGE_SIZE,
    include:    ['project', 'taskList'] satisfies TaskInclude[],
}))
```

Template fields are optional, so always check for presence:

```vue
<template #body="{ data }: { data: ITask }">
    <span v-if="data.project">{{ data.project.name }}</span>
</template>
```

---

## Adding includes to an entity

**Backend:**

1. Add the relation to the model (`hasMany`, `belongsTo`, etc.) and its `@property` PHPDoc.
2. Add the relation name to `getAllowedIncludes()` in the relevant controller.
3. Add a `whenLoaded()` field to the relevant Resource.

**Frontend:**

1. Add the value to the `*Include` union type in `*.api.types.ts`.
2. Add an optional field to the entity interface in `*.types.ts`.
