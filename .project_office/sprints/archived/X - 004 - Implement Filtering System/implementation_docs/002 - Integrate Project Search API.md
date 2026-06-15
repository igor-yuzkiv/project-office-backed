---
task: "002 - Integrate Project Search API"
status: done
---

# 002 - Integrate Project Search API

## What Was Implemented

Окремий `POST /api/projects/search` endpoint, який виконує пошук через Laravel Scout і підтримує `filters[]` з filter infrastructure (task 001). Існуючий `GET /api/projects` залишився без змін.

Також додано `toSearchableArray()` та трейт `Searchable` до `TaskListModel` і `TaskModel` — підготовка для майбутніх search endpoints.

## Route

```
POST /api/projects/search
```

Додано перед `apiResource`, щоб уникнути конфлікту з resource routing.

## Request Payload

```json
{
  "query": "my project",
  "filters": [
    {
      "filter_key": "text",
      "field_name": "name",
      "value": "alpha",
      "matchMode": "contains",
      "params": []
    }
  ]
}
```

- `query` — рядок для Scout пошуку; порожній рядок повертає всі проекти
- `filters` — масив filter payload items (з task 001 contract)

## Response

Той самий resource shape, що й `GET /api/projects` — `ProjectResource::collection()` з pagination.

## Files Changed

| File | Change |
|---|---|
| `routes/api.php` | Додано `POST projects/search` route перед `apiResource` |
| `app/Http/Controllers/Projects/ProjectsController.php` | Додано `search()` метод |
| `app/Domains/Project/Models/ProjectModel.php` | Додано `toSearchableArray()` |
| `app/Domains/TaskList/Models/TaskListModel.php` | Додано `Searchable` трейт + `toSearchableArray()` |
| `app/Domains/Task/Models/TaskModel.php` | Додано `Searchable` трейт + `toSearchableArray()` |

## Searchable Arrays

| Model | Fields |
|---|---|
| `ProjectModel` | `id`, `name`, `prefix` |
| `TaskListModel` | `id`, `name`, `project_id` |
| `TaskModel` | `id`, `key`, `name`, `description` |

## Controller Implementation

```php
public function search(Request $request): AnonymousResourceCollection
{
    $query = (string) $request->input('query', '');
    $filters = (array) $request->input('filters', []);
    $pagination = $this->getPaginationParams();

    $projects = ProjectModel::search($query)
        ->query(function (Builder $q) use ($filters) {
            /** @var Builder<ProjectModel> $q */
            return $q->with(['createdBy', 'updatedBy'])->filter($filters);
        })
        ->paginate($pagination->perPage, 'page', $pagination->page);

    return ProjectResource::collection($projects);
}
```

Filters застосовуються через Scout's `query()` callback — на рівні Eloquent Builder до вибірки з БД. Scout (collection driver) потім фільтрує результати за `query` рядком in-memory.

## Key Decisions

- **Окремий `POST search` endpoint** замість розширення `index` — щоб не змінювати поведінку існуючого `GET /api/projects`.
- **Scout collection driver** (дефолт у проєкті) — підтримує `query()` callbacks.
- **`query()` callback з `@var` assertion** — потрібен для PHPStan, щоб розпізнати scope `filter()` на `Builder<ProjectModel>`.
- **Немає FormRequest** — resolver сам валідує filter payload і кидає `InvalidFilterException` → 400.
- **`ProjectModel::allowedFilters()`** вже був визначений у task 001 — перевикористано без змін.

## Feature Tests

`tests/Feature/Http/Projects/ProjectSearchTest.php` — 8 тестів:

| Test | What it covers |
|---|---|
| empty query returns all projects with pagination | base behavior |
| returns projects matching search query | Scout text search |
| applies text filter on name field | filter integration |
| applies text filter on prefix field | filter integration |
| returns 400 for unknown filter key | error handling |
| returns 400 for field not in allowed list | error handling |
| paginates search results | pagination |
| GET /api/projects unchanged | regression |

## Checks Run

- `php artisan test tests/Feature/Http/Projects/ProjectSearchTest.php` — 8/8 passed
- `./vendor/bin/pint` — clean
- `./vendor/bin/phpstan analyse` — 0 errors (full project)

## Notes For Next Agent

- `TaskListModel` і `TaskModel` мають `Searchable` + `toSearchableArray()` — готові до search endpoints у майбутніх tasks.
- Scout driver: `collection` (env `SCOUT_DRIVER`). При зміні драйвера (напр. Meilisearch) `toSearchableArray()` вже визначено коректно.
- `POST /api/projects/search` потребує `auth:sanctum`.
- Pagination параметри: `page`, `per_page` — ті самі, що й у `index`.
