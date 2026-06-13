# Include System

Механізм для опціонального підвантаження зв'язаних ресурсів у API-відповідь. Дозволяє клієнту декларувати, які relations потрібні для конкретного запиту, і уникнути over-fetching.

---

## Backend

### ResourceController

Єдиний базовий клас для всіх resource controllers (`app/Http/Controllers/ResourceController.php`). Замінює старий `Controller`. Містить:

- `getPaginationParams()` та `getSortParams()` — загальні helpers
- `getAllowedIncludes()` — **abstract**, кожен controller визначає які Eloquent relations доступні клієнту
- `resolveIncludes(required, requested)` — об'єднує обов'язкові та запитані includes
- `parseRequestedIncludes()` — читає `include` з request

### getAllowedIncludes

Кожен controller реалізує метод і повертає список **Eloquent relation names** — тих самих, що і в `->with()`:

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

- `$required` — relations, які **завжди** завантажуються для цього action (задаються в контролері)
- `$requested` — relations, які запросив клієнт (з `parseRequestedIncludes()`)

Метод перевіряє кожен `$requested` item проти `getAllowedIncludes()`. Якщо relation не дозволений — кидає `InvalidIncludeException` з HTTP 422. Дозволені items об'єднуються з `$required` (дублікати видаляються).

Результат передається у `->with()` або `->load()`:

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

Для `show()` всі потрібні relations передаються через `$required`, а `$requested` дозволяє клієнту додати ще:

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

`app/Infrastructure/Exceptions/InvalidIncludeException.php` — кидається коли клієнт передає relation якого немає в `getAllowedIncludes()`.

Рендериться як HTTP 422:

```json
{
  "message": "Include 'fooBar' is not allowed. Allowed includes: project, taskList, createdBy, updatedBy, tags."
}
```

### Resource: whenLoaded

У `toArray()` кожен include рендериться через `whenLoaded()` — поле з'являється у response тільки якщо relation завантажено. Для колекцій `::collection()` знаходиться всередині callback:

```php
// singular relation — відсутнє якщо не loaded
'project' => $this->whenLoaded('project', fn () => new ProjectOverviewResource($this->project)),

// collection — відсутнє якщо не loaded (не [], а відсутнє)
'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
```

---

## API Contract

### Формат передачі

`include` передається як:

- **GET** (index, show): query param з comma-separated рядком
  ```
  GET /api/tasks?include=project,taskList
  ```
- **POST** (search): масив у тілі запиту
  ```json
  { "include": ["project", "taskList"] }
  ```

Backend обробляє обидва формати однаково через `parseRequestedIncludes()`.

### Помилки

| Ситуація | HTTP | Відповідь |
|---|---|---|
| relation не в `getAllowedIncludes()` | 422 | `{ "message": "Include 'X' is not allowed. Allowed includes: ..." }` |

### Allowed includes по сутностях

| Сутність | Allowed includes |
|---|---|
| **Project** | `createdBy`, `updatedBy`, `tags`, `tasks`, `taskLists` |
| **Task** | `project`, `taskList`, `createdBy`, `updatedBy`, `tags` |
| **TaskList** | `tasks`, `project`, `createdBy`, `updatedBy` |
| **Attachment** | `createdBy`, `updatedBy` |

### Required includes по діях

Навіть без `include` у запиті, деякі relations завантажуються завжди (вони в `$required` конкретного action):

| Сутність | Action | Required (завжди завантажуються) |
|---|---|---|
| **Project** | `index`, `search`, `show` | `createdBy`, `updatedBy`, `tags` |
| **Task** | `index`, `search` | `createdBy`, `updatedBy`, `tags` |
| **Task** | `show` | `createdBy`, `updatedBy`, `tags`, `project`, `taskList` |
| **TaskList** | `index`, `search`, `show` | `createdBy`, `updatedBy` |
| **Attachment** | `search` | `createdBy`, `updatedBy` |

---

## Frontend

### Структура

API-типи кожної сутності знаходяться в окремому файлі `*.api.types.ts` всередині `entities/{entity}/types/`:

```
entities/{entity}/types/
├── {entity}.types.ts       # entity interface, overview DTO
├── {entity}.api.types.ts   # *Include, *FetchParams, *SearchParams, ICreate*Input, IUpdate*Input
└── index.ts                # re-exports обох файлів
```

### Include types

Для кожної сутності — union type з Eloquent relation names (ті самі назви, що і на беку):

```ts
// task.api.types.ts
export type TaskInclude = 'project' | 'taskList' | 'createdBy' | 'updatedBy' | 'tags'

// project.api.types.ts
export type ProjectInclude = 'createdBy' | 'updatedBy' | 'tags' | 'tasks' | 'taskLists'

// task_list.api.types.ts
export type TaskListInclude = 'tasks' | 'project' | 'createdBy' | 'updatedBy'

// attachment.api.types.ts
export type AttachmentInclude = 'createdBy' | 'updatedBy'
```

### Entity interfaces

Поля relations в entity interface — опціональні (`?`), бо їх присутність залежить від того чи передавався `include`:

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

Назви полів — snake_case, відповідають ключам з Resource `toArray()`.

### Params types

`include` входить до fetch і search params типів:

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

### API функції

**GET** — `include` серіалізується у comma-separated рядок:

```ts
export async function fetchTasksRequest(params?: TaskFetchParams): PromisePaginatedResponse<ITask> {
    const { include, ...rest } = params ?? {}
    return httpClient
        .get('/tasks', { params: { ...rest, include: include?.join(',') } })
        .then((res) => res.data)
}
```

**POST** — `include` передається масивом у тілі:

```ts
export async function searchTasksRequest(params: TaskSearchParams): PromisePaginatedResponse<ITask> {
    const { query = '', filters = [], include, ...pagination } = params
    return httpClient
        .post('/tasks/search', { query, filters, include, ...pagination })
        .then((res) => res.data)
}
```

### Використання у компоненті

```ts
const searchParams = computed(() => ({
    query:      searchQuery.value,
    filters:    filterSidebar.resolvedFilters.value,
    page:       page.value,
    per_page:   PAGE_SIZE,
    include:    ['project', 'taskList'] satisfies TaskInclude[],
}))
```

Поле у шаблоні — опціональне, завжди перевіряємо наявність:

```vue
<template #body="{ data }: { data: ITask }">
    <span v-if="data.project">{{ data.project.name }}</span>
</template>
```

---

## Додавання includes до сутності

**Backend:**

1. Додати relation до моделі (`hasMany`, `belongsTo`, тощо) і `@property` PHPDoc.
2. Додати relation name у `getAllowedIncludes()` відповідного controller.
3. Додати `whenLoaded()` поле у відповідний Resource.

**Frontend:**

1. Додати значення у `*Include` union type у `*.api.types.ts`.
2. Додати опціональне поле у entity interface у `*.types.ts`.
