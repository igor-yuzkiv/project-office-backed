# 004 - Create TasksPage With Global Tasks List

## Що реалізовано

Read-only `TasksPage` з глобальним списком задач, search, filter sidebar, sort dialog та підтримкою include system. Реалізовано include mechanism для опціонального підвантаження зв'язаних ресурсів на backend і frontend.

## Змінені файли

### Backend

- `app/Http/Controllers/Controller.php` — додано `getIncludeParams(array $allowedMap): array` — спільний helper для парсингу `include` параметра з request, нормалізація string/array, whitelist фільтрація.
- `app/Http/Controllers/Tasks/TasksController.php` — `ALLOWED_INCLUDES`, `getIncludeParams()` у `index()` і `search()`.
- `app/Http/Resources/Tasks/TaskResource.php` — `whenLoaded('project')` і `whenLoaded('taskList')` для опціональних includes.

### Frontend — Task entity

- `resources/js/entities/task/types/task.types.ts` — додано `TaskInclude`, `TaskFetchParams`, `TaskSearchParams` з `include?: TaskInclude[]`, опціональні поля `project?` і `task_list?` в `ITask`.
- `resources/js/entities/task/api/task.api.ts` — додано `searchTasksRequest`, оновлено `fetchTasksRequest` під `TaskFetchParams`. GET серіалізує `include` як comma-separated рядок, POST передає масивом.
- `resources/js/entities/task/config/index.ts` — новий файл: `TaskQueryKey`.
- `resources/js/entities/task/queries/use.tasks-search.query.ts` — новий файл: `useTasksSearchQuery`.
- `resources/js/entities/task/queries/index.ts` — новий файл: barrel export.

### Frontend — Shared

- `resources/js/shared/components/display/ui/CopyToClipboard.vue` — новий компонент: показує текст з кнопкою копіювання у clipboard, VueUse `useClipboard`, іконки `mdi:content-copy` / `mdi:check`.
- `resources/js/shared/components/display/index.ts` — додано `CopyToClipboard` до barrel.

### Frontend — Pages & Router

- `resources/js/pages/tasks/TasksPage.vue` — повна реалізація: `useTasksSearchQuery` з `include: ['project']`, search input, filter sidebar (`name`, `status`, `priority`), sort dialog, DataTable з колонками `key` (CopyToClipboard), `name`, `project` (RouterLink), `status`, `priority.name`, `created_at`.
- `resources/js/pages/projects/ProjectDetailsPage.vue` — новий файл: порожня сторінка-заглушка, показує `projectId` з route params.
- `resources/js/app/router/index.ts` — додано route `/projects/:id` → `project-details`.

### Документація

- `.project_office/project_documentation/include-system.md` — новий файл: опис include mechanism без прив'язки до сутності, на прикладі tasks. Backend helper, ALLOWED_INCLUDES, whenLoaded. Frontend типи, API серіалізація, використання у компоненті, інструкція додавання до нової сутності.

## Важливі рішення

- **Include mechanism** — опціональне підвантаження relations через `include` параметр замість постійного eager load. `getIncludeParams()` у базовому Controller — єдина точка парсингу і валідації, може бути підключена до будь-якого controller.
- **`TaskFetchParams` окремий від `TaskSearchParams`** — GET і POST мають різні param-контракти, тому типи розділені.
- **`CopyToClipboard` у `shared/components/display`** — generic компонент без прив'язки до сутності, приймає `text: string | null | undefined`, показує fallback `—`.
- **`ProjectDetailsPage` як заглушка** — створена під майбутню task 005, дозволила вже підключити RouterLink у колонці Project.

## Перевірки

- `php artisan test` — 46/46 passed
- `npm run format` — passed
- `npm run types:check` — passed
