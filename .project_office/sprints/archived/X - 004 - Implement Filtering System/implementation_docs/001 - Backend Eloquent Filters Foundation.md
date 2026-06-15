---
task: "001 - Backend Eloquent Filters Foundation"
status: done
---

# 001 - Backend Eloquent Filters Foundation

## What Was Implemented

Backend filter infrastructure in `app/Libs/EloquentFilters/` — reusable, declarative Eloquent and Scout filtering via a standardized `filters[]` payload.

## Payload Contract

```json
{
  "filter_key": "text",
  "field_name": "name",
  "value": "foo",
  "matchMode": "contains",
  "params": {}
}
```

`filter_key` і `field_name` — snake_case ключі зовнішнього API контракту. PHP-властивості DTO називаються `filterKey` і `fieldName`.

## Files

### Core infrastructure

| File | Purpose |
|---|---|
| `app/Libs/EloquentFilters/Filter.php` | Abstract base: `key()`, `apply(Builder\|ScoutBuilder)`, `supportedMatchModes()` |
| `app/Libs/EloquentFilters/FilterPayload.php` | DTO одного payload item; `fromArray()` парсить сирий масив |
| `app/Libs/EloquentFilters/FilterDefinition.php` | DTO запису з `allowedFilters()`: `filterClass` + `allowedFields`; `key()` делегує до класу |
| `app/Libs/EloquentFilters/FilterResolver.php` | `resolve(array, FilterDefinition[])` — валідує і повертає Filter instance |
| `app/Libs/EloquentFilters/MatchMode.php` | Enum підтримуваних match modes |
| `app/Libs/EloquentFilters/InvalidFilterException.php` | Domain exception з named constructors і контекстом; `render()` → 400 JSON |
| `app/Libs/EloquentFilters/Concerns/HasFilters.php` | Model trait: `allowedFilters(): FilterDefinition[]` + `scopeFilter()` |

### Filter classes

| File | Key | Supported matchModes |
|---|---|---|
| `Filters/TextFilter.php` | `text` | equals, notEquals, startsWith, endsWith, contains, notContains |
| `Filters/IntegerFilter.php` | `integer` | equals, notEquals, gt, gte, lt, lte |
| `Filters/BooleanFilter.php` | `boolean` | — (no matchMode) |
| `Filters/DateTimeFilter.php` | `datetime` | equals, notEquals, gt, gte, lt, lte, dateIs, dateIsNot, dateBefore, dateAfter |
| `Filters/NullableFilter.php` | `nullable` | equals → `whereNull`, notEquals → `whereNotNull` |

### Tests

| File | Coverage |
|---|---|
| `tests/Unit/Libs/EloquentFilters/FilterResolverTest.php` | unknown key, disallowed field, unknown matchMode, unsupported matchMode, valid resolution, null matchMode |
| `tests/Unit/Libs/EloquentFilters/Filters/NullableFilterTest.php` | equals → whereNull, notEquals → whereNotNull |
| `tests/Unit/Libs/EloquentFilters/Filters/TextFilterTest.php` | contains, startsWith, equals, notContains |

## Model Contract

```php
public static function allowedFilters(): array
{
    return [
        new FilterDefinition(TextFilter::class, ['name', 'prefix']),
        new FilterDefinition(NullableFilter::class, ['deleted_at']),
    ];
}
```

Повертає `FilterDefinition[]`. Ключ фільтра (`'text'`, `'nullable'`) визначається самим filter класом через `key()`.

## InvalidFilterException

Приватний конструктор. Чотири named constructors з контекстом:

| Factory | Context у response |
|---|---|
| `unknownFilter($filterKey)` | `{filter_key}` |
| `fieldNotAllowed($fieldName, $filterKey)` | `{filter_key, field_name}` |
| `unknownMatchMode($matchMode)` | `{matchMode}` |
| `unsupportedMatchMode($matchMode, $filterKey)` | `{filter_key, matchMode}` |

Response format:
```json
{ "message": "...", "context": { ... } }
```

## Key Decisions

- **Payload keys snake_case** (`filter_key`, `field_name`) — `fromArray()` маппить їх у camelCase-властивості DTO.
- **`FilterPayload::fromArray()`** — вся логіка парсингу сирого масиву в одному місці; `FilterResolver::resolve()` починається з одного виклику.
- **`FilterDefinition` DTO** замість вкладеного масиву — явна структура з `filterClass`, `allowedFields`, `key()`.
- **Filter key визначається класом** (`key()` static method) — модель не дублює ключ у карті.
- **`apply()` приймає `Builder|ScoutBuilder`** — інфраструктура сумісна з Laravel Scout.
- **`InvalidFilterException::render()`** — 400 без змін у `bootstrap/app.php`.
- **Resolver не валідує `value`** — filter класи пропускають запит якщо value порожнє; це навмисно для nullable/boolean.

## Checks Run

- `php artisan test tests/Unit/Libs/` — 12/12 passed
- `./vendor/bin/pint` — clean
- `./vendor/bin/phpstan analyse` — 0 errors (full project)

## Notes For Next Agent

- Task 002 інтегрує цю інфраструктуру в `ProjectsController@index`.
- `ProjectModel` вже має `HasFilters` і `allowedFilters()` з `TextFilter` для `name` і `prefix` — додано під час тестування foundation.
- Зовнішній payload контракт: `filter_key`, `field_name`, `value`, `matchMode`, `params`.
