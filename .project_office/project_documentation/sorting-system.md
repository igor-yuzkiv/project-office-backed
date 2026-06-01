# Sorting System

Система сортування складається з frontend модуля `resources/js/shared/sort/` і backend `SortParams` DTO. Параметри сортування передаються в кожному пошуковому запиті разом з фільтрами.

---

## Frontend: shared/sort

### Де живе

```
resources/js/shared/sort/
├── types/
│   └── sort.types.ts          # SortDirection, SortFieldDef, SortParams
├── composables/
│   └── use.sort-dialog.ts     # useSortDialog() composable
├── ui/
│   ├── SortButton.vue         # кнопка з поточним полем у label
│   └── SortDialog.vue         # PrimeVue Dialog з вибором поля і напрямку
└── index.ts                   # barrel export
```

Імпортувати тільки з barrel-файлу: `import { ... } from '@/shared/sort'`.

### Типи

```ts
type SortDirection = 'asc' | 'desc'

type SortFieldDef = {
    field: string   // ім'я поля, відправляється у sort_by
    label: string   // текст для відображення у UI
}

// передається у API-запит разом з іншими params
type SortParams = {
    sort_by?: string
    sort_order?: SortDirection
}
```

`SortParams` використовується у типах параметрів API-функцій (наприклад, `ProjectSearchParams`), щоб гарантувати що `sort_by` / `sort_order` завжди передаються в запит.

### Composable: useSortDialog

Управляє видимістю діалогу і реалізує draft/committed state — зміна поля сортування у діалозі не застосовується до запиту до натиснення Apply.

```ts
const sort = useSortDialog(
    fields,        // SortFieldDef[] — список доступних полів
    defaultField,  // string — поле за замовчуванням (опціонально, дефолт = перше поле)
    defaultOrder   // SortDirection — напрямок за замовчуванням (дефолт = 'asc')
)
```

Повертає:

| Властивість / метод | Тип | Опис |
|---|---|---|
| `visible` | `Ref<boolean>` | видимість діалогу |
| `sortBy` | `Ref<string>` | **committed** поле — використовується у query params |
| `sortOrder` | `Ref<SortDirection>` | **committed** напрямок |
| `draftSortBy` | `Ref<string>` | поле у відкритому діалозі (змінюється до Apply) |
| `draftSortOrder` | `Ref<SortDirection>` | напрямок у відкритому діалозі |
| `activeSortLabel` | `ComputedRef<string>` | label committed поля (для кнопки) |
| `open()` | `() => void` | синхронізує draft ← committed, відкриває діалог |
| `close()` | `() => void` | закриває діалог без збереження |
| `setDraftField(field)` | `(string) => void` | оновлює `draftSortBy` |
| `setDraftOrder(order)` | `(SortDirection) => void` | оновлює `draftSortOrder` |
| `apply()` | `() => void` | копіює draft → committed |
| `reset()` | `() => void` | скидає draft до дефолтних значень |

**Важливо:** `open()` завжди синхронізує draft з committed перед відкриттям. Якщо користувач відкрив діалог, щось змінив і закрив без Apply — наступного разу побачить актуально застосоване сортування, а не незбережені зміни.

### UI компоненти

**SortButton** — відображає поточне поле сортування у label:

```vue
<SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
```

Пропси: `label?: string`. Emit: `click: []` (без `MouseEvent` — діалог не потребує позиції).

**SortDialog** — PrimeVue Dialog з двома Select і кнопками Cancel/Apply:

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

`@apply` і `@update:visible` — окремі events, тому Apply і Cancel обробляються незалежно.

### Повний приклад інтеграції у Page компонент

```vue
<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useSortDialog, SortButton, SortDialog, type SortFieldDef } from '@/shared/sort'

const sortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

// дефолтне сортування: updated_at desc
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

// скидати сторінку при зміні сортування
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

Дефолти: сортування за `created_at desc`. Будь-який endpoint, що не отримав явних sort params, поверне цей порядок.

### Як sort params потрапляють у контролер

`Controller` базовий клас надає `getSortParams()`:

```php
// у будь-якому контролері
$sort = $this->getSortParams(); // читає sort_by, sort_order з request
```

Повертає `SortParams` з `field` і `direction`.

### Сортування у звичайному Eloquent запиті

```php
$projects = ProjectModel::query()
    ->orderBy($sort->field, $sort->direction)
    ->paginate($pagination->perPage, page: $pagination->page);
```

### Сортування у Scout запиті

При використанні Laravel Scout (CollectionEngine) сортування має бути на рівні Scout Builder — **до** `->query()` callback:

```php
ProjectModel::search($query)
    ->orderBy($sort->field, $sort->direction)  // ← Scout Builder level (правильно)
    ->query(function (Builder $q) use ($filters): Builder {
        return $q->filter($filters);
        // НЕ додавати orderBy тут — CollectionEngine ігнорує його при формуванні порядку
    })
    ->paginate($perPage, 'page', $page);
```

**Чому:** `CollectionEngine::searchModels()` впорядковує результати виключно через `$builder->orders` (Scout Builder). `orderBy` всередині `->query()` callback потрапляє у `queryScoutModelsByIds()` при re-fetch, але потім `map()` перезаписує порядок позиціями з `searchModels()` — які формуються без урахування `queryCallback`.

### SearchProjectsQuery

Логіка пошуку проектів винесена з контролера у `app/Domains/Project/Queries/SearchProjectsQuery.php`:

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

Контролер лише делегує:

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

### Додавання сортування до нового search endpoint

1. Прийняти `sort_by` і `sort_order` з request через `$this->getSortParams()`.
2. Передати `SortParams` у Query клас або використати в inline запиті.
3. Для Scout: `->orderBy($sort->field, $sort->direction)` до `->query()`.
4. Для Eloquent: `->orderBy($sort->field, $sort->direction)` на Builder.
5. На фронті: додати `sort_by` і `sort_order` у params об'єкт API-функції (через `SortParams` тип).
