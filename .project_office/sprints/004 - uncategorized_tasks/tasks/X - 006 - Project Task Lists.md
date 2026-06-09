---
type: task
status: draft
---

# 006 - Project Task Lists Tab

## Goal

На вкладці Task Lists сторінки Project Details показати таблицю Task Lists, прив'язаних до поточного проекту, з кількістю задач у кожному списку. Поруч із таблицею додати кнопку для створення Task List у тому ж проекті через dialog, що відтворює патерн `TaskCreateDialog`.

## Dependencies

- `005 - Rename task_list entity folder to kebab-case` має бути виконано **перед** цією задачею. Усі шляхи в цій задачі написані з розрахунком на `entities/task-list` (kebab-case).

## Context

Backend API для Task Lists уже існує:

- `TaskListsController` має `index` і `search` (Scout-based з filtering) методи (`app/Http/Controllers/TaskLists/TaskListsController.php`);
- `TaskListModel` підтримує filtering по `name` і `project_id` через `HasFilters` + `TextFilter`;
- frontend `entities/task-list` уже має `types`, `api` (включно з `searchTaskListsRequest`, `createTaskListRequest`), `queries/use.task-lists-search.query.ts`, `config` з `TASK_LIST_MODULE_NAME` і `TaskListQueryKey`.

Чого поки що **немає**:

- `entities/task-list/mutations/` (порожньо — `useCreateTaskListMutation` не реалізовано);
- `widgets/task-list/lists-table/` (відсутня таблиця);
- `widgets/task-list/create-dialog/` (відсутній dialog для створення);
- `ProjectTaskListsPage` — поточно лише плейсхолдер "Not implemented" (`resources/js/pages/projects/details/tabs/ProjectTaskListsPage.vue`).

Маршрут `project-details.task-lists` уже зареєстрований у `resources/js/app/router/index.ts`.

Шаблон create-dialog patternу: `widgets/tasks/create-dialog/` (composable `useTaskCreateDialog` + Vue компонент `TaskCreateDialog.vue` з props `visible/formData/validationErrors/isPending` і events `update:visible/update:formData/submit`).

## Scope

**Backend — TaskList: tasks count:**

- Додати relation `tasks(): HasMany` у `TaskListModel` (поле `task_list_id` у таблиці `tasks` уже існує).
- Підвантажувати `withCount('tasks')` у методах `TaskListsController::index` і `TaskListsController::search`.
- Додати поле `'tasks_count' => $this->tasks_count ?? 0` (або через `whenCounted('tasks')`) у `TaskListResource`.
- PHP validation: `./vendor/bin/pint` і `./vendor/bin/phpstan analyse` проходять.

**Frontend — entities/task-list mutations:**

- Створити папку `resources/js/entities/task-list/mutations/`.
- Додати `use.create-task-list.mutation.ts` за патерном `use.create-task.mutation.ts`:
  - mutationFn: `createTaskListRequest`;
  - on success: `queryClient.invalidateQueries({ queryKey: TaskListQueryKey.all })`.
- Реекспорт через `mutations/index.ts`.

**Frontend — entities/task-list types:**

- Додати `tasks_count?: number` у `ITaskList` для відображення колонки Tasks Count.

**Frontend — widget `widgets/task-list/lists-table` (новий):**

- Створити widget поруч із `widgets/task-list/lookup-field/`.
- Структура:
  - `ui/TaskListsTable.vue` — pure-presentational таблиця за патерном `widgets/tasks/tasks-table/ui/TasksTable.vue`;
  - `index.ts` з реекспортом.
- Props:
  - `taskLists: ITaskList[]`;
  - `isPending: boolean`;
  - `paginationMeta?: PaginationMeta`;
  - `page: number`.
- Emits:
  - `pageChange: [page: number]`.
- **Не** емітити `rowClick` — рядки статичні.
- Колонки:
  - **Name** — `name`;
  - **Tasks Count** — `tasks_count ?? 0`, шрифт surface-500 у разі 0;
  - **Created** — `created_at` через `DisplayDate` (як у `TasksTable`).
- PrimeVue `DataTable` + `Column` + `Paginator` у footer (як у `TasksTable`).

**Frontend — widget `widgets/task-list/create-dialog` (новий):**

- Створити widget поруч із `widgets/task-list/lookup-field/` і `widgets/task-list/lists-table/`.
- Структура:
  - `ui/TaskListCreateDialog.vue`;
  - `composables/use.task-list-create-dialog.ts`;
  - `index.ts` з реекспортом обох.
- Composable `useTaskListCreateDialog()` за патерном `useTaskCreateDialog`:
  - state: `visible`, `formData`, `validationErrors`, `isPending`;
  - `open(project: IProject)` — обов'язковий параметр (проект завжди відомий з контексту сторінки); ініціалізує `formData.project` і `validationErrors = {}`;
  - `close()`;
  - `submit()` — виклик `useCreateTaskListMutation`, на успіх `close()` (без router.push, оскільки task-list-details сторінки немає);
  - `handleError` для validation errors (як у `useTaskCreateDialog`).
- `TaskListCreateDialog.vue` за патерном `TaskCreateDialog.vue`:
  - props/emits ідентичні `TaskCreateDialog`;
  - поля у формі:
    - **Name** — `InputText` + `InputContainer`, required;
    - **Project** — `ProjectLookupField` у режимі **visible read-only**: `disabled`, pre-filled з `formData.project`. Користувач не може змінити проект.
- Header dialog'у: `"New Task List"`.

**Frontend — `ProjectTaskListsPage`:**

- Замінити плейсхолдер реальною реалізацією.
- Отримати `projectId` з `route.params.id`.
- Через `useProjectQuery(projectId)` отримати `project` об'єкт (потрібен для prefill у dialog'у).
- Через `useTaskListsSearchQuery` з params `{ project_id, page, per_page: PAGE_SIZE }` отримати список.
- Рендерити:
  - Action area над таблицею з inline кнопкою **New Task List** (PrimeVue `Button`). Disabled поки `project` не завантажений.
  - `TaskListsTable` зі станом.
  - `TaskListCreateDialog` зі стейтом з `useTaskListCreateDialog()`.
- Клік по `New Task List` → `dialog.open(project)`.
- Без search/filter/sort UI.
- Не використовувати `useAppLayoutStore.setHeaderActions` для цієї кнопки.

## Out Of Scope

- Edit/Delete/Reorder Task Lists.
- Task-list-details сторінка чи route.
- Row click navigation з TaskListsTable.
- Search/filter/sort UI у `ProjectTaskListsPage`.
- Можливість змінити проект у TaskListCreateDialog.
- Створення Task List у глобальному контексті (поза project-details сторінкою).
- Attachment, role чи інші поля Task List.
- Optimistic UI / inline create row.
- Counter для інших агрегатів (open vs done tasks тощо).
- Тести (юніт/інтеграційні) — додавати за потреби окремою задачею.

## Expected Behavior

- Користувач відкриває Project Details → вкладка Task Lists.
- Бачить таблицю Task Lists поточного проекту з колонками Name, Tasks Count, Created.
- Якщо Task Lists немає — стандартний empty state PrimeVue `DataTable`.
- Над таблицею кнопка **New Task List**. Клік відкриває dialog.
- У dialog'і:
  - поле Name порожнє і фокусоване (або просто порожнє, якщо autofocus не реалізовано в інших dialog'ах);
  - поле Project pre-filled поточним проектом і **disabled** — користувач його бачить, але не може змінити.
- Подача форми створює Task List через `POST /task-lists`. На успіх — dialog закривається, таблиця оновлюється завдяки інвалідації `TaskListQueryKey.all`.
- Validation помилки з backend (422) відображаються під відповідними полями (через `InputContainer` як у `TaskCreateDialog`).
- Якщо у списку >1 сторінки — у footer таблиці paginator. Перехід між сторінками не перезавантажує сторінку.
- Tasks Count відображає актуальну кількість задач у списку на момент запиту.
- Row click нічого не робить (рядки статичні).

## Technical Notes

- Дотримуватись паттернів існуючих widget'ів `widgets/tasks/tasks-table` і `widgets/tasks/create-dialog`.
- Composable `useTaskListCreateDialog` має приймати `project: IProject` у `open()` — не робити optional fallback з router, щоб уникнути unclear coupling.
- `ProjectLookupField` у dialog'і використовується з prop `disabled` (перевірити що компонент підтримує disabled prop; якщо ні — додати у рамках цієї задачі мінімальне розширення без зміни поведінки в інших споживачах).
- Не вводити нову `task-list-filters.config.ts` — фільтр `project_id` уже формується всередині `searchTaskListsRequest`.
- Не торкатися Tasks tab (TasksPage) і не змінювати інші вкладки project-details.
- Frontend validation: `npm run format`, `npm run lint`, `npm run types:check`.
- Не встановлювати нові packages.

## Acceptance Criteria

**Backend:**

- [ ] `TaskListModel::tasks()` relation додано.
- [ ] `TaskListsController::index` і `::search` підвантажують `withCount('tasks')`.
- [ ] `TaskListResource` повертає `tasks_count`.
- [ ] PHP validation: pint + phpstan проходять.

**Frontend:**

- [ ] `entities/task-list/mutations/use.create-task-list.mutation.ts` існує і інвалідовує `TaskListQueryKey.all` на успіх.
- [ ] `ITaskList.tasks_count?: number` додано.
- [ ] `widgets/task-list/lists-table/` створено з `TaskListsTable.vue` (Name, Tasks Count, Created; без rowClick; з paginator у footer).
- [ ] `widgets/task-list/create-dialog/` створено з `TaskListCreateDialog.vue` і `useTaskListCreateDialog` composable за патерном `TaskCreateDialog`.
- [ ] У dialog'і Project field pre-filled і disabled.
- [ ] `ProjectTaskListsPage` показує TaskListsTable з фільтром по поточному проекту і inline кнопку New Task List.
- [ ] Клік по New Task List відкриває dialog із preselected проектом.
- [ ] Створення працює, таблиця оновлюється, dialog закривається.
- [ ] Validation помилки з backend відображаються в dialog'і.
- [ ] Frontend validation: format + lint + types:check проходять.

## Open Questions

- Чи потрібен autofocus на полі Name у dialog'і — узгодити з patternом `TaskCreateDialog` (якщо там немає, тут теж не додавати).
- Точне розташування inline кнопки `New Task List` (top-right у action bar над таблицею vs над таблицею як `app-card` smaller bar) — узгодити під час review з patternами на інших project-details вкладках, коли вони з'являться.
- Чи показувати `tasks_count` як просте число чи як badge/tag — узгодити під час review (за замовчуванням просте число).

## Notes For Developer Agent

- Не починати, поки `005 - Rename task_list entity folder to kebab-case` не завершено.
- Не дублювати `TaskCreateDialog` копією — повторити структуру, але не імпортувати з task widget.
- Не вводити нові route'и.
- Не вводити edit/delete UI — це окрема майбутня задача.
- Не міняти `ProjectLookupField` поза тим, що потрібно для `disabled` режиму.
- Тримати TaskListsTable pure-presentational (як TasksTable).
