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
    ├── NullableFilter.php      # key: "nullable"
    └── LookupFilter.php        # key: "lookup" — фільтрація по FK полях
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
| `LookupFilter` | `lookup` | `equals`, `notEquals` |

Якщо `matchMode` не вказано або null — filter використовує дефолтне значення (наприклад, `TextFilter` дефолтно `contains`).

### Як фільтрація застосовується до моделі

**Крок 1.** Додати трейт `HasFilters` до моделі та визначити `allowedFilters()`:

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

**Важливо:** `->orderBy()` для Scout CollectionEngine має бути на рівні Scout Builder (до `->query()`), а не всередині `->query()` callback.

### Allowed filters по моделях

| Модель | Filter | Поля |
|---|---|---|
| `ProjectModel` | `TextFilter` | `name`, `prefix` |
| `TaskListModel` | `TextFilter` | `name`, `project_id` |
| `TaskModel` | `TextFilter` | `name`, `description`, `key`, `status` |
| `TaskModel` | `IntegerFilter` | `priority` |
| `TaskModel` | `LookupFilter` | `project_id`, `task_list_id` |

---

## Frontend: shared/filters

### Де живе

```
resources/js/shared/filters/
├── types/
│   ├── filter-def.types.ts     # FilterDef, FilterDefMap, AnyFilterDef, FilterDataType
│   ├── filter-payload.types.ts # FilterPayloadItem (API contract)
│   └── match-mode.types.ts     # MatchMode union, MatchModeOption, constants
├── lib/
│   ├── filter-config.ts        # FILTER_TYPE_CONFIG — конфіг для кожного dataType
│   ├── filter-factory.ts       # createFilterDefinition(), createFilterDefMap()
│   └── filter-resolver.ts      # resolveFilters() → FilterPayloadItem[]
├── composables/
│   ├── use.filters.ts          # useFilters() — базовий composable
│   └── use.filter-sidebar.ts   # useFilterSidebar() — з draft/committed state
└── ui/
    ├── FilterControl.vue       # один filter рядок (toggle + match mode + value input)
    ├── FilterList.vue          # рендерить FilterControl для кожного запису в map
    ├── FilterSidebar.vue       # PrimeVue Drawer wrapper
    ├── FilterButton.vue        # кнопка з badge-лічильником активних фільтрів
    └── value-inputs/
        ├── TextInput.vue
        ├── IntegerInput.vue
        ├── BooleanInput.vue
        └── DateTimeInput.vue
```

Імпортувати тільки з barrel-файлу: `import { ... } from '@/shared/filters'`.

### Ключові типи

```ts
// відповідає filter_key у backend API
type FilterDataType = 'text' | 'integer' | 'boolean' | 'datetime' | 'nullable' | 'lookup'

type FilterDef<TDataType extends FilterDataType> = {
    label: string
    fieldName?: string            // встановлюється автоматично через createFilterDefMap
    dataType: TDataType
    value: FilterValue<TDataType>
    defaultValue: FilterValue<TDataType>
    matchMode: MatchMode | null
    inputProps: Record<string, unknown>
    extraParams?: Record<string, unknown>
    info?: string
    enabled: boolean
    withoutMatchMode?: boolean
    component?: Component         // кастомний input компонент, перекриває дефолтний для dataType
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

### FILTER_TYPE_CONFIG

`filter-config.ts` містить конфігурацію для кожного `FilterDataType`:

```ts
type FilterTypeConfig = {
    matchModes: MatchModeOption[]   // доступні режими порівняння
    isEmpty: (value: unknown) => boolean  // коли не включати у payload
    omitValue?: boolean             // не передавати value у payload (наприклад, nullable)
    requiresMatchMode?: boolean     // не включати якщо matchMode === null
}

const FILTER_TYPE_CONFIG: Record<FilterDataType, FilterTypeConfig>
```

`resolveFilters()` використовує цей конфіг замість if-else ланцюжків — додавання нового типу вимагає лише запису у конфіг.

### Декларативна фабрика

`createFilterDefMap()` — основний спосіб визначення набору фільтрів:

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

`addField(fieldName, dataType, configure)` — автоматично встановлює `fieldName` на def.

Доступні методи builder'а:

| Метод | Опис |
|---|---|
| `.label(v)` | Заголовок у sidebar |
| `.value(v)` | Початкове значення |
| `.defaultValue(v)` | Значення при reset |
| `.matchMode(v)` | Початковий matchMode |
| `.enabled(v)` | Чи активний фільтр при відкритті |
| `.withoutMatchMode()` | Приховати select matchMode |
| `.component(v)` | Кастомний input компонент (замість дефолтного для dataType) |
| `.mergeInputProps(v)` | Змерджити додаткові props для input компонента |
| `.setInputProps(v)` | Повністю замінити inputProps |
| `.extraParams(v)` | Додаткові params у payload |
| `.info(v)` | Інформаційний текст |

`createFilterDefinition()` для одиночного фільтра:

```ts
// fluent builder
const def = createFilterDefinition('text', (d) =>
    d.label('Name').value('').matchMode('contains')
)

// plain partial object (альтернативний синтаксис)
const def = createFilterDefinition('integer', { label: 'Priority', enabled: false })
```

### Lookup фільтри та LookupField wrapper компоненти

Для фільтрації по пов'язаних сутностях використовується dataType `lookup`. Value — ID запису (string | number | null).

Для кожної сутності існує wrapper компонент у `widgets/`, який інкапсулює `LookupField` + search query:

```
widgets/projects/lookup-field/ui/ProjectLookupField.vue
widgets/task-list/lookup-field/ui/TaskListLookupField.vue
```

Використання у фільтрах:

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

Ці ж компоненти використовуються у формах (TaskCreateDialog, TaskEditPage) з `object` prop:

```vue
<!-- повертає IProject об'єкт -->
<ProjectLookupField v-model="formData.project" :object="true" />

<!-- повертає string ID -->
<ProjectLookupField v-model="formData.project_id" />
```

### resolveFilters — перетворення стану в API payload

`resolveFilters(defMap: FilterDefMap): FilterPayloadItem[]`

Запис виключається якщо:
- `enabled: false`
- `requiresMatchMode: true` і `matchMode === null` (тип `nullable`)
- `isEmpty(value) === true` (логіка специфічна для кожного `dataType` через `FILTER_TYPE_CONFIG`)

### Composable: useFilterSidebar

Manages draft/committed state pattern — зміни у sidebar не застосовуються до query поки не натиснуто Apply.

```ts
const filterSidebar = useFilterSidebar(initialDefMap)
```

Повертає:

| Поле | Тип | Опис |
|---|---|---|
| `visible` | `Ref<boolean>` | видимість sidebar |
| `draftDefMap` | `Ref<FilterDefMap>` | робоча копія для рендерингу sidebar |
| `resolvedFilters` | `ComputedRef<FilterPayloadItem[]>` | оновлюється тільки після apply() |
| `updateFilter` | `(key, patch) => void` | змінює один filter у draftDefMap |
| `apply` | `() => void` | копіює draft → committed |
| `reset` | `() => void` | скидає draft до початкових значень |
| `sidebarProps` | `ComputedRef<object>` | готові props+handlers для `<FilterSidebar v-bind>` |
| `buttonProps` | `ComputedRef<object>` | готові props+handlers для `<FilterButton v-bind>` |

`sidebarProps` включає `visible`, `defMap`, `onChange`, `onApply`, `onReset` — повністю покриває wiring між composable і компонентом.

Поведінка при відкритті sidebar: `watch(visible)` копіює committed → draft, тому відкриття sidebar завжди показує актуально застосовані фільтри. Cancel або Escape відкидає незбережені зміни.

### Повний приклад інтеграції у Page компонент

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

`v-bind` на `sidebarProps` автоматично прокидає `visible`, `defMap`, `onChange`, `onApply`, `onReset`. Додатковий `@apply` використовується для page-специфічних side effects (скидання пагінації).

### Додавання нового типу фільтра на фронті

1. Додати новий `FilterDataType` до `filter-def.types.ts`.
2. Додати відповідний `FilterValue` у `FilterValueMap`.
3. Додати запис у `FILTER_TYPE_CONFIG` у `filter-config.ts` з `matchModes`, `isEmpty` та опціональними флагами.
4. Створити `NewTypeInput.vue` у `value-inputs/` (якщо потрібен дефолтний input).
5. Додати запис у `DATA_TYPE_COMPONENTS` у `FilterControl.vue`.
6. Реалізувати відповідний backend `Filter` клас.
