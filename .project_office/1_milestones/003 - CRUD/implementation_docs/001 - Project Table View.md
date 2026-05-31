# 001 - Project Table View

## Що реалізовано

Табличне представлення Projects з pagination, контекстним меню (Edit / Delete) та підтвердженням видалення.

## Змінені файли

| Дія | Файл |
|---|---|
| New | `resources/js/entities/project/config/index.ts` |
| New | `resources/js/entities/project/queries/use.projects.query.ts` |
| New | `resources/js/entities/project/queries/index.ts` |
| New | `resources/js/entities/project/mutations/use.delete-project.mutation.ts` |
| New | `resources/js/entities/project/mutations/index.ts` |
| Modified | `resources/js/pages/projects/ProjectsPage.vue` |
| New | `resources/js/app/config/index.ts` |

## Ключові рішення

### Query keys у `entities/project/config`

`ProjectQueryKey.all` — базовий ключ для інвалідації. `ProjectQueryKey.paginated(params)` — приймає `MaybeRefOrGetter<PagingParams>`, передається напряму в `queryKey` масиві, vue-query відстежує реактивність автоматично.

### `useProjectsQuery`

`keepPreviousData` — таблиця не мигає при переключенні сторінок. `pagination` — `ref` на рівні сторінки; при зміні сторінки ref оновлюється → query автоматично робить новий запит.

### `useDeleteProjectMutation` — два методи

- `mutate(id)` — чистий виклик без UI
- `mutateWithConfirm(id, message?)` — підтвердження через `useConfirmDialog().requireAsync`, default message якщо не передано

Confirm-логіка всередині mutation, щоб сторінка не займалася організацією діалогу.

### Контекстне меню

Один `<Menu popup>` на всю таблицю. `selectedProject` ref зберігає поточний рядок при кліку на `pi-ellipsis-v`. `rowMenuItems` — статичний масив; `command` читає `selectedProject` через closure.

### `app/config/index.ts`

`PAGE_SIZE`, `APP_NAME`, `API_BASE_URL` — централізоване місце для env-значень і глобальних констант. Замінено усі `import.meta.env` usages у `http.client.ts`, `LoginPage.vue`, `AppLeftNavigationSidebar.vue`, `use.app-layout.store.ts`.

## Перевірки

- `npm run types:check` — без помилок

## Commit message

```
feat(projects): add project table view with pagination and delete action
```
