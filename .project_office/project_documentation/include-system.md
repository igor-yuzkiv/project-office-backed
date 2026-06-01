# Include System

Механізм для опціонального підвантаження зв'язаних ресурсів у API-відповідь. Дозволяє клієнту декларувати, які relations потрібні для конкретного запиту, і уникнути over-fetching.

---

## Backend

### Базовий Controller

`Controller::getIncludeParams()` — спільний helper для всіх controllers:

```php
/**
 * @param  array<string, string>  $allowedMap  API name → Eloquent relation name
 * @return string[]
 */
protected function getIncludeParams(array $allowedMap): array
{
    $raw = request()->input('include', []);
    $requested = is_string($raw) ? array_filter(explode(',', $raw)) : (array) $raw;

    return array_values(array_filter(
        array_map(fn (string $key) => $allowedMap[trim($key)] ?? null, $requested)
    ));
}
```

Метод:
- читає `include` з request (query string або POST body);
- нормалізує до масиву (підтримує і рядок `project,task_list`, і JSON масив);
- фільтрує тільки дозволені значення через `$allowedMap`;
- повертає Eloquent relation names готові для `->with()`.

### Підключення до Controller

У кожному controller оголошується whitelist через `ALLOWED_INCLUDES`:

```php
private const ALLOWED_INCLUDES = [
    'project'   => 'project',   // API name => Eloquent relation
    'task_list' => 'taskList',
];
```

Далі передається в `index()` і `search()`:

```php
public function index(): AnonymousResourceCollection
{
    $includes = $this->getIncludeParams(self::ALLOWED_INCLUDES);

    $tasks = TaskModel::with(['createdBy', 'updatedBy', ...$includes])
        ->orderBy($sort->field, $sort->direction)
        ->paginate($pagination->perPage, page: $pagination->page);

    return TaskResource::collection($tasks);
}

public function search(SearchRequest $request): AnonymousResourceCollection
{
    $includes = $this->getIncludeParams(self::ALLOWED_INCLUDES);

    $tasks = TaskModel::search((string) $request->input('query', ''))
        ->query(function (Builder $q) use ($request, $includes): Builder {
            return $q->with(['createdBy', 'updatedBy', ...$includes])->filter(...);
        })
        ->paginate(...);

    return TaskResource::collection($tasks);
}
```

### Resource

У `toArray()` includes рендеряться через `whenLoaded()` — поле з'являється в response тільки якщо relation завантажено:

```php
public function toArray(Request $request): array
{
    return [
        'id'        => $this->id,
        // ... основні поля ...
        'project'   => $this->whenLoaded('project', fn () => new ProjectResource($this->project)),
        'task_list' => $this->whenLoaded('taskList', fn () => new TaskListResource($this->taskList)),
    ];
}
```

Якщо `include=project` не передано — поле `project` відсутнє у відповіді повністю (не `null`, а відсутнє).

---

## API Contract

`include` передається як:
- **query param** для GET запитів: `?include=project,task_list`
- **тіло запиту** для POST (search): `{ "include": ["project", "task_list"] }`

Backend нормалізує обидва формати — поведінка однакова.

Значення відповідають API names з `ALLOWED_INCLUDES` конкретного controller:

| API name | Що повертається |
|---|---|
| `project` | Об'єкт `ProjectResource` вкладений у відповідь |
| `task_list` | Об'єкт `TaskListResource` вкладений у відповідь |

Невідомі або недозволені includes мовчки ігноруються — помилки не виникає.

---

## Frontend

### Тип include

Для кожної сутності оголошується union type допустимих includes:

```ts
export type TaskInclude = 'project' | 'task_list'
```

### Тип entity

Опціональні поля для можливих includes:

```ts
export interface ITask extends IEntity {
    // ... основні поля ...
    project?:   IProject
    task_list?: ITaskList
}
```

Поля опціональні (`?`), бо їх присутність залежить від того, чи передавався `include` у запиті.

### Search params

`include` додається до params-типу сутності:

```ts
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
export async function fetchTasksRequest(
    params?: PagingParams & SortParams & { include?: TaskInclude[] }
): PromisePaginatedResponse<ITask> {
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
    filters:    appliedFilters.value,
    page:       page.value,
    per_page:   PAGE_SIZE,
    sort_by:    sort.sortBy.value,
    sort_order: sort.sortOrder.value,
    include:    ['project' as const],   // підключаємо тільки те, що потрібно на цій сторінці
}))
```

У шаблоні поле доступне як `data.project` — але оскільки воно опціональне, завжди перевіряємо наявність:

```vue
<template #body="{ data }">
    <RouterLink v-if="data.project" :to="{ name: 'project-details', params: { id: data.project_id } }">
        {{ data.project.name }}
    </RouterLink>
</template>
```

---

## Додавання includes до нової сутності

**Backend:**

1. Додати `ALLOWED_INCLUDES` до controller.
2. Передати `$includes` у `->with()` в `index()` і `search()`.
3. Додати `whenLoaded()` поля у відповідний `Resource`.

**Frontend:**

1. Оголосити `EntityInclude` union type.
2. Додати опціональні поля у `IEntity` interface.
3. Додати `include?: EntityInclude[]` у `EntitySearchParams`.
4. Серіалізувати `include` в API функціях (join для GET, масив для POST).
