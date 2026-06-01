---
task: "001 - Backend Eloquent Filters Foundation"
status: done
---

# 001 - Backend Eloquent Filters Foundation

## What Was Implemented

Backend filter infrastructure in `app/Libs/EloquentFilters/` — reusable, declarative Eloquent filtering via a standardized `filters[]` payload.

## Payload Contract

```json
{
  "filter": "text",
  "field": "name",
  "value": "foo",
  "matchMode": "contains",
  "params": {}
}
```

## Files Created

### Core infrastructure

| File | Purpose |
|---|---|
| `app/Libs/EloquentFilters/Filter.php` | Abstract base: `key()`, `apply()`, `supportedMatchModes()` |
| `app/Libs/EloquentFilters/FilterResolver.php` | Resolves payload item → Filter instance; validates filter key, field, matchMode |
| `app/Libs/EloquentFilters/MatchMode.php` | Enum of all supported match modes |
| `app/Libs/EloquentFilters/ParameterBag.php` | Immutable params holder passed to filter instances |
| `app/Libs/EloquentFilters/InvalidFilterException.php` | Domain exception; `render()` returns `400 Bad Request` JSON |
| `app/Libs/EloquentFilters/Concerns/HasFilters.php` | Model trait: declares `allowedFilters()` contract + `scopeFilter()` |

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
        'text'     => [TextFilter::class, ['allowed_fields' => ['name', 'prefix']]],
        'nullable' => [NullableFilter::class, ['allowed_fields' => ['deleted_at']]],
    ];
}
```

Key is the filter key string; value is `[FilterClass, ['allowed_fields' => [...]]]`.

## Key Decisions

- **No string schema format** — only array payload format per task spec.
- **Filter key defined by the filter class** (`key()` static method), not by the model map key — both must match.
- **`allowed_fields` is explicit and named** in the model definition to avoid positional ambiguity.
- **`InvalidFilterException::render()`** handles the 400 response without touching `bootstrap/app.php`.
- **`@phpstan-ignore trait.unused`** on `HasFilters` — will be removed once the trait is applied in Task 002.

## Checks Run

- `php artisan test tests/Unit/Libs/` — 12/12 passed
- `./vendor/bin/pint app/Libs/EloquentFilters/` — clean
- `./vendor/bin/phpstan analyse app/Libs/EloquentFilters/` — 0 errors

## Notes For Next Agent

- Task 002 integrates this foundation into `ProjectModel` and `ProjectsController@index`.
- Remove the `@phpstan-ignore trait.unused` annotation from `HasFilters.php` after applying the trait to `ProjectModel`.
- The resolver does not validate that `value` is present — individual filter classes skip silently if value is empty. This is intentional for nullable/boolean cases.
