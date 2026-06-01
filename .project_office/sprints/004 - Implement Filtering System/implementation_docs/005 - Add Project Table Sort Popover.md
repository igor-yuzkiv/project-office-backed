---
task: 005 - Add Project Table Sort Popover
sprint: 004 - Implement Filtering System
status: done
---

# 005 - Add Project Table Sort Popover

## Що реалізовано

- Shared sort module `resources/js/shared/sort/` з types, composable і UI компонентами.
- Sort dialog у toolbar над Projects table з вибором поля і напрямку сортування.
- Мінімальна backend зміна: `SearchProjectsQuery` тепер приймає sort params.
- `SortParams` тип перенесено з `shared/types/pagination.types.ts` у `shared/sort/types/sort.types.ts`.
- Баг з `HeaderActions` (race condition при навігації) пофіксовано через `onMounted`.
- Scout `CollectionEngine` bug: `orderBy` всередині `->query()` callback ігнорується engine — фікс: `->orderBy()` на рівні Scout Builder.

## Змінені файли

### Нові файли

| Файл | Опис |
|---|---|
| `resources/js/shared/sort/types/sort.types.ts` | `SortDirection`, `SortFieldDef`, `SortParams` |
| `resources/js/shared/sort/composables/use.sort-dialog.ts` | Керує `visible`, committed/draft sort state, `open/close/apply/reset` |
| `resources/js/shared/sort/ui/SortButton.vue` | Кнопка з поточним полем у label |
| `resources/js/shared/sort/ui/SortDialog.vue` | PrimeVue Dialog з двома Select і Cancel/Apply |
| `resources/js/shared/sort/index.ts` | Public exports |
| `app/Domains/Project/Queries/SearchProjectsQuery.php` | Scout search + filters + sort, винесено з контролера |

### Змінені файли

| Файл | Що змінено |
|---|---|
| `resources/js/shared/types/pagination.types.ts` | Видалено `SortParams` (перенесено в `shared/sort`) |
| `resources/js/entities/project/types/project.types.ts` | `ProjectSearchParams` розширено `& SortParams` |
| `resources/js/entities/project/api/project.api.ts` | Імпорт `SortParams` з `@/shared/sort` |
| `resources/js/entities/task/api/task.api.ts` | Імпорт `SortParams` з `@/shared/sort` |
| `resources/js/entities/task_list/api/task_list.api.ts` | Імпорт `SortParams` з `@/shared/sort` |
| `resources/js/pages/projects/ProjectsPage.vue` | Sort dialog підключено; `setHeaderActions` перенесено в `onMounted`; додано `onUnmounted` з `clearHeaderActions` |
| `app/Http/Controllers/Projects/ProjectsController.php` | `search` делегує в `SearchProjectsQuery`; видалено `Builder` import |

### Мертвий код (можна видалити)

- `resources/js/shared/sort/ui/SortPopover.vue`
- `resources/js/shared/sort/composables/use.sort-popover.ts`

## Важливі рішення

### Scout CollectionEngine і сортування

`CollectionEngine::searchModels()` застосовує сортування **тільки** через `$builder->orders` (Scout Builder's `->orderBy()`). `orderBy` всередині `->query()` callback потрапляє в `queryScoutModelsByIds()` при re-fetch, але `map()` перетирає порядок через `sortBy(objectIdPositions)` — де позиції беруться з `searchModels()`, яка ігнорує `queryCallback`.

**Фікс**: `->orderBy()` на рівні Scout Builder (до `->query()`), а не всередині callback.

### Draft/committed state у sort composable

Sort застосовується через Apply button (не одразу). Composable зберігає два стани:
- `sortBy` / `sortOrder` — committed, використовуються в query params
- `draftSortBy` / `draftSortOrder` — in-dialog draft, застосовуються тільки після Apply

`open()` синхронізує draft з committed. `apply()` комітить draft.

### HeaderActions race condition

`setHeaderActions` у `setup()` (ProjectsPage) викликалась до `clearHeaderActions` у `onUnmounted` (TaskPage). Порядок хуків Vue Router при навігації A→B: `B.setup → B.onBeforeMount → A.onBeforeUnmount → A.onUnmounted → B.onMounted`. Фікс: `setHeaderActions` перенесено в `onMounted`.

### SearchProjectsQuery

Логіка пошуку винесена з `ProjectsController::search` в `app/Domains/Project/Queries/SearchProjectsQuery.php`. Параметри передаються в конструктор, логіка виконується в `run()`.

## Перевірки

- Backend: Pint ✓, PHPStan level 5 ✓
- Frontend: format ✓, lint ✓, types:check ✓
