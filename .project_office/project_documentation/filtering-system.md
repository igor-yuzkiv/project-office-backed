# Filtering System

Система фільтрації складається з двох незалежних шарів: backend filter infrastructure (`app/Libs/EloquentFilters/`) і frontend filters module (`resources/js/shared/filters/`). Вони пов'язані спільним API-контрактом — масивом `filters[]` у запиті.

---

## Backend: EloquentFilters

### Де живе

```
app/Libs/EloquentFilters/
├── Filter.php                  # abstract base
├── FilterPayload.php           # input DTO одного filter item
├── FilterDefinition.php        # реєстрація filter класу для моделі
├── FilterResolver.php          # валідує payload і повертає Filter instance
├── MatchMode.php               # enum допустимих match modes
├── InvalidFilterException.php  # 400 exception з named constructors
├── Concerns/
│   └── HasFilters.php          # model trait: allowedFilters() + scopeFilter()
└── Filters/
    ├── TextFilter.php          # key: "text"
    ├── IntegerFilter.php       # key: "integer"
    ├── BooleanFilter.php       # key: "boolean"
    ├── DateTimeFilter.php      # key: "datetime"
    └── NullableFilter.php      # key: "nullable"
```

### API-контракт

Кожен елемент масиву `filters[]` у запиті:

```json
{
  "filter_key": "text",
  "field_name": "name",
  "value": "alpha",
  "matchMode": "contains",
  "params": {}
}
```

| Поле | Опис |
|---|---|
| `filter_key` | Ідентифікує тип фільтра. Відповідає `Filter::key()` конкретного класу. |
| `field_name` | Поле моделі, по якому фільтрувати. Має бути у `allowedFields` для даного filter. |
| `value` | Значення фільтра. Тип залежить від filter класу. |
| `matchMode` | Оператор порівняння (опціонально). Деякі filter класи ігнорують. |
| `params` | Додаткові параметри (наразі не використовуються у стандартних filters). |

### Filter класи

| Клас | key | Підтримувані matchModes |
|---|---|---|
| `TextFilter` | `text` | `equals`, `notEquals`, `startsWith`, `endsWith`, `contains`, `notContains` |
| `IntegerFilter` | `integer` | `equals`, `notEquals`, `gt`, `gte`, `lt`, `lte` |
| `BooleanFilter` | `boolean` | — (matchMode ігнорується) |
| `DateTimeFilter` | `datetime` | `equals`, `notEquals`, `gt`, `gte`, `lt`, `lte`, `dateIs`, `dateIsNot`, `dateBefore`, `dateAfter` |
| `NullableFilter` | `nullable` | `equals` → `whereNull`, `notEquals` → `whereNotNull` |

Якщо `matchMode` не вказано або null — filter використовує дефолтне значення (наприклад, `TextFilter` дефолтно `contains`).

### Як фільтрація застосовується до моделі

**Крок 1.** Додати трейт `HasFilters` до моделі та визначити `allowedFilters()`:

```php
use App\Libs\EloquentFilters\Concerns\HasFilters;
use App\Libs\EloquentFilters\FilterDefinition;
use App\Libs\EloquentFilters\Filters\TextFilter;
use App\Libs\EloquentFilters\Filters\IntegerFilter;

class TaskModel extends Model
{
    use HasFilters;

    public static function allowedFilters(): array
    {
        return [
            new FilterDefinition(TextFilter::class, ['name', 'description']),
            new FilterDefinition(IntegerFilter::class, ['priority']),
        ];
    }
}
```

`FilterDefinition` реєструє: який filter клас і на яких полях дозволено.

**Крок 2.** Викликати `->filter($filters)` у запиті. Трейт додає Eloquent scope `scopeFilter()`:

```php
// у контролері або Query класі
$tasks = TaskModel::query()
    ->filter($request->input('filters', []))
    ->paginate($perPage);

// або всередині Scout query callback
TaskModel::search($query)
    ->query(fn (Builder $q) => $q->filter($filters))
    ->paginate($perPage);
```

`scopeFilter()` ітерує `filters[]`, для кожного item викликає `FilterResolver::resolve()`, потім `$filter->apply($query)`.

### FilterResolver: логіка валідації

`FilterResolver::resolve(array $payload, FilterDefinition[] $allowedFilters)` виконує:

1. Парсить сирий масив через `FilterPayload::fromArray()` → DTO з camelCase-властивостями.
2. Шукає `FilterDefinition` з відповідним `key()` серед `allowedFilters`. Не знайдено → `InvalidFilterException::unknownFilter()`.
3. Перевіряє `field_name` проти `allowedFields`. Не дозволено → `InvalidFilterException::fieldNotAllowed()`.
4. Якщо `matchMode` не null — перевіряє що це валідний `MatchMode` enum і що filter його підтримує.
5. Повертає `new FilterClass($payload)`.

### Обробка помилок

`InvalidFilterException` автоматично рендериться у HTTP 400:

```json
{
  "message": "Unknown filter: foo",
  "context": { "filter_key": "foo" }
}
```

| Named constructor | Коли |
|---|---|
| `unknownFilter($filterKey)` | `filter_key` не знайдено серед `allowedFilters` |
| `fieldNotAllowed($fieldName, $filterKey)` | `field_name` відсутнє в `allowedFields` для цього filter |
| `unknownMatchMode($matchMode)` | `matchMode` не є валідним enum значенням |
| `unsupportedMatchMode($matchMode, $filterKey)` | filter клас не підтримує цей matchMode |

### Додавання нового filter класу

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
        return 'status'; // цей рядок використовується як filter_key у API
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

Реєструвати у `allowedFilters()` моделі:

```php
new FilterDefinition(StatusFilter::class, ['status']),
```

### Search request

Всі search endpoints використовують єдиний validation class:

```
app/Http/Requests/Shared/SearchRequest.php
```

Клас валідує `query`, `filters[]`, `page`, `per_page`, `sort_by`, `sort_order`.

### Search endpoints

```
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

Логіка search інлайнована у відповідному controller:

```php
public function search(SearchRequest $request): AnonymousResourceCollection
{
    $sort = $this->getSortParams();
    $pagination = $this->getPaginationParams();

    $projects = ProjectModel::search((string) $request->input('query', ''))
        ->orderBy($sort->field, $sort->direction)
        ->query(function (Builder $q) use ($request): Builder {
            return $q->with(['createdBy', 'updatedBy'])->filter((array) $request->input('filters', []));
        })
        ->paginate($pagination->perPage, 'page', $pagination->page);

    return ProjectResource::collection($projects);
}
```

**Важливо:** `->orderBy()` для Scout CollectionEngine має бути на рівні Scout Builder (до `->query()`), а не всередині `->query()` callback. CollectionEngine застосовує порядок тільки через `$builder->orders`; `orderBy` всередині callback потрапляє в re-fetch, але потім перезаписується позиціями з `searchModels()`.

### Allowed filters по моделях

| Модель | Filter | Поля |
|---|---|---|
| `ProjectModel` | `TextFilter` | `name`, `prefix` |
| `TaskListModel` | `TextFilter` | `name`, `project_id` |
| `TaskModel` | `TextFilter` | `name`, `description`, `key`, `project_id`, `task_list_id`, `status` |
| `TaskModel` | `IntegerFilter` | `priority` |

---

## Frontend: shared/filters

### Де живе

```
resources/js/shared/filters/
├── types/
│   ├── filter-def.types.ts     # FilterDef, FilterDefMap, AnyFilterDef, FilterDataType, FilterValue
│   ├── filter-payload.types.ts # FilterPayloadItem (API contract)
│   └── match-mode.types.ts     # MatchMode union, MatchModeOption, constants
├── lib/
│   ├── filter-config.ts        # MATCH_MODE_OPTIONS lookup
│   ├── filter-factory.ts       # createFilterDefinition(), createFiltersDefinitionsMap()
│   └── filter-resolver.ts      # resolveFilters() → FilterPayloadItem[]
├── composables/
│   ├── use.filters.ts          # useFilters() — базовий composable
│   └── use.filter-sidebar.ts   # useFilterSidebar() — з draft/committed state
└── ui/
    ├── FilterControl.vue       # один filter рядок (toggle + match mode + value)
    ├── FilterGroup.vue         # рендерить FilterControl для кожного запису в map
    ├── FilterSidebar.vue       # PrimeVue Drawer wrapper
    ├── FiltersButton.vue       # кнопка з badge-лічильником активних фільтрів
    └── value-inputs/
        ├── TextValueInput.vue
        ├── IntegerValueInput.vue
        ├── BooleanValueInput.vue
        └── DateTimeValueInput.vue
```

Імпортувати тільки з barrel-файлу: `import { ... } from '@/shared/filters'`.

### Ключові типи

```ts
// dataType відповідає filter_key у backend API
type FilterDataType = 'text' | 'integer' | 'boolean' | 'datetime' | 'nullable'

type FilterDef<TDataType extends FilterDataType> = {
    label: string
    fieldName?: string           // ім'я поля; встановлюється через createFiltersDefinitionsMap
    dataType: TDataType
    value: FilterValue<TDataType>
    defaultValue: FilterValue<TDataType>
    matchMode: MatchMode | null
    inputProps: Record<string, unknown>
    extraParams?: Record<string, unknown>
    info?: string
    enabled: boolean
    withoutMatchMode?: boolean
}

// FilterDefMap — Record<fieldName, FilterDef>
type FilterDefMap = Record<string, AnyFilterDef>

// API payload — відправляється у POST /search
type FilterPayloadItem = {
    filter_key: string       // = def.dataType
    field_name: string       // = def.fieldName
    value: unknown
    matchMode: string | null
    params: Record<string, unknown>
}
```

### Декларативна фабрика

`createFiltersDefinitionsMap()` — основний спосіб визначення набору фільтрів:

```ts
const defMap = createFiltersDefinitionsMap((map) =>
    map
        .addField('name', 'text', (d) => d.label('Name'))
        .addField('prefix', 'text', (d) => d.label('Prefix'))
        .addField('priority', 'integer', (d) => d.label('Priority').enabled(false))
)
```

`addField(fieldName, dataType, configure)` — автоматично встановлює `fieldName` на def.

`createFilterDefinition()` для одиночного фільтра:

```ts
// fluent builder
const def = createFilterDefinition('text', (d) =>
    d.label('Name').value('').matchMode('contains')
)

// plain partial object (альтернативний синтаксис)
const def = createFilterDefinition('integer', { label: 'Priority', enabled: false })
```

### resolveFilters — перетворення стану в API payload

`resolveFilters(defMap: FilterDefMap): FilterPayloadItem[]`

Правила виключення запису з результату:
- `enabled: false`
- `value === null | undefined | ''` (для всіх типів, крім `boolean` і `nullable`)
- `boolean`: включається тільки якщо `value !== null`
- `nullable`: включається тільки якщо `matchMode === 'equals' | 'notEquals'`

### Composable: useFilterSidebar

Manages draft/committed state pattern — зміни у sidebar не застосовуються до query поки не натиснуто Apply.

```ts
const {
    visible,          // Ref<boolean> — видимість sidebar
    draftDefMap,      // Ref<FilterDefMap> — робоча копія для рендерингу sidebar
    resolvedFilters,  // Ref<FilterPayloadItem[]> — оновлюється тільки в apply()
    updateFilter,     // (key, patch) => void — змінює один filter у draftDefMap
    apply,            // () => void — копіює draft → committed, перераховує resolvedFilters
    reset,            // () => void — скидає draft до початкових значень
} = useFilterSidebar(initialDefMap)
```

Поведінка при відкритті sidebar: `watch(visible)` копіює committed → draft, тому відкриття sidebar завжди показує актуально застосовані фільтри. Cancel або Escape відкидає незбережені зміни.

### UI компоненти

**FilterSidebar** — повний sidebar з групою фільтрів і кнопками Apply/Reset:

```vue
<FilterSidebar
    v-model:visible="sidebarVisible"
    :def-map="sidebarDefMap"
    title="Filters"
    @apply="onApply"
    @reset="onReset"
    @change="updateFilter"
/>
```

**FiltersButton** — кнопка з badge, що показує кількість активних фільтрів:

```vue
<FiltersButton :count="activeFiltersCount" @click="sidebarVisible = true" />
```

### Повний приклад інтеграції у Page компонент

```vue
<script setup lang="ts">
import { computed, ref } from 'vue'
import { FilterSidebar, FiltersButton, createFiltersDefinitionsMap, useFilterSidebar } from '@/shared/filters'
import { useTasksSearchQuery } from '@/entities/task/queries'

const {
    visible: sidebarVisible,
    draftDefMap: sidebarDefMap,
    resolvedFilters: appliedFilters,
    updateFilter,
    apply: applyFilters,
    reset: resetFilters,
} = useFilterSidebar(
    createFiltersDefinitionsMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('priority', 'integer', (d) => d.label('Priority'))
    )
)

const page = ref(1)
const activeFiltersCount = computed(() => appliedFilters.value.length)

const searchParams = computed(() => ({
    filters: appliedFilters.value,
    page: page.value,
    per_page: 15,
}))

const { tasks } = useTasksSearchQuery(searchParams)

function onApply() {
    applyFilters()
    page.value = 1
}
</script>

<template>
    <FiltersButton :count="activeFiltersCount" @click="sidebarVisible = true" />

    <FilterSidebar
        v-model:visible="sidebarVisible"
        :def-map="sidebarDefMap"
        title="Filters"
        @apply="onApply"
        @reset="resetFilters"
        @change="updateFilter"
    />
</template>
```

### Додавання нового типу фільтра на фронті

1. Додати новий `FilterDataType` до `filter-def.types.ts`.
2. Додати відповідний `FilterValue` у `FilterValueMap`.
3. Додати `MatchModeOption[]` у `MATCH_MODE_OPTIONS` в `filter-config.ts`.
4. Створити `NewTypeValueInput.vue` у `value-inputs/`.
5. Додати гілку у `FilterControl.vue` для рендерингу нового input компонента.
6. Реалізувати відповідний backend `Filter` клас.
