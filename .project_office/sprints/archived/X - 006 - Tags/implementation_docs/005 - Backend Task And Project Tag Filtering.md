---
task: 005 - Backend Task And Project Tag Filtering
status: done
---

# 005 - Backend Task And Project Tag Filtering

## What Was Implemented

- `TagFilter` — dedicated filter із `key() = 'tags'`, OR semantics через `whereHas('tags', fn ($q) => $q->whereIn('tags.id', $value))`.
- Зареєстровано в `TaskModel::allowedFilters()` і `ProjectModel::allowedFilters()` із `allowedFields = []`.
- `FilterResolver` — умова field name validation тепер `if (!empty($definition->allowedFields))`. Якщо `allowedFields` порожній — field name валідація пропускається (замість окремого методу `requiresFieldName()`).
- Виправлено `morphToMany` на `TaskModel` і `ProjectModel` — додано явний `relatedPivotKey: 'tag_id'`.
- `SearchRequest` — `filters.*.field_name` relaxed до `nullable`.

## Files Created

- `app/Libs/EloquentFilters/Filters/TagFilter.php`
- `tests/Feature/Tags/TagFilterTest.php` — 3 тести

## Files Modified

- `app/Libs/EloquentFilters/FilterResolver.php` — умова на `empty(allowedFields)`
- `app/Domains/Task/Models/TaskModel.php` — зареєстровано `TagFilter`, виправлено `relatedPivotKey`
- `app/Domains/Project/Models/ProjectModel.php` — те саме
- `app/Http/Requests/Shared/SearchRequest.php` — `field_name` nullable

## Checks Run

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — 0 errors
- `php artisan test --filter=TagFilterTest` — 3/3

## Notes For Next Agent

- Фільтр активується через search payload: `{ "filter_key": "tags", "value": ["id1", "id2"] }`.
- `allowedFields = []` на `FilterDefinition` — конвенція для dedicated filters без field name.
