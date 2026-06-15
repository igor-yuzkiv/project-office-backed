---
type: task
status: ready
---

# 002 - Add Start Date and Due Date to Tasks Module

## Goal

Додати поля `start_date` і `due_date` до моделі `Task` — на бекенді (БД, model, API) та фронтенді (edit page, details tab).

## Context

Модуль `Task` зараз не має жодних дат планування. Користувачу потрібно фіксувати, коли задача планується до початку і до якої дати має бути завершена.

База даних містить тільки fake-дані — редагуємо існуючу міграцію `2026_05_31_130000_create_tasks_table.php`, нову не створюємо.

Цей task **не зачіпає** статус `archived`, `completed_at`, ані трейти `HasArchivableColumns` / `HasCompletableColumns` — це окремі sprint'и.

## Scope

### Backend — Schema

- Відредагувати `database/migrations/2026_05_31_130000_create_tasks_table.php`:
  - додати `start_date` (`date`, nullable);
  - додати `due_date` (`date`, nullable).

### Backend — Model

- `App\Domains\Task\Models\TaskModel`:
  - оновити `#[Fillable]` (додати `start_date`, `due_date`);
  - додати у `casts()` → `start_date` і `due_date` як `date`;
  - оновити PHPDoc `@property` для нових полів (для PHPStan).

### Backend — HTTP Layer

- `StoreTaskRequest`, `UpdateTaskRequest`:
  - `start_date` → `['sometimes', 'nullable', 'date']`;
  - `due_date`   → `['sometimes', 'nullable', 'date', 'after_or_equal:start_date']`.
- `CreateTaskCommand`, `UpdateTaskCommand`: додати поля `startDate`, `dueDate` (рядки або `null`).
- `CreateTaskHandler`, `UpdateTaskHandler`: пробросити поля у `create` / `update` payload.
- `TasksController`: передати нові поля з валідованого request у Commands.
- `TaskResource`: додати `start_date`, `due_date`.
- `TaskOverviewResource`: додати `start_date`, `due_date` (для list view).

### Frontend — Entity Layer

- `entities/task/types/task.types.ts` (`ITask`): додати `start_date: string | null`, `due_date: string | null`.
- `TaskOverviewDto`: додати `start_date`, `due_date` у `Pick<...>`.
- `entities/task/types/task.api.types.ts` (`ICreateTaskInput`, `IUpdateTaskInput`): додати `start_date`, `due_date`.

### Frontend — Task Edit Page

- `pages/tasks/edit/TaskEditPage.vue`:
  - додати поля `Start Date` і `Due Date` (`DatePicker` з PrimeVue, nullable) у верхній grid поряд з іншими полями;
  - підключити до `TaskEditFormData` (нові поля `startDate`, `dueDate` як `string | null` у форматі `YYYY-MM-DD`);
  - bind у submit payload (`start_date`, `due_date`);
  - відображати validation errors `validationErrors.start_date`, `validationErrors.due_date`;
  - ініціалізувати з `task.start_date` / `task.due_date` у watcher.

### Frontend — Task Details (Overview) Tab

- `pages/tasks/details/tabs/TaskOverviewPage.vue`:
  - додати `DisplayDate` для `Start Date` і `Due Date` у тому ж grid (після Project / Task List, перед Created/Updated блоками).
  - Показувати плейсхолдер (порожній рядок або "—") коли значення `null` — використати дефолтну поведінку `DisplayDate`.

## Out Of Scope

- Статус `archived` для tasks, трейт `HasArchivableColumns` для `TaskModel`, `archived_at` / `archived_by`.
- Поле `completed_at`, трейт `HasCompletableColumns`, інтерфейс `Completable`, `completed_by`.
- `TaskCreateDialog` — лишається мінімальним (Name + Project), нові поля туди не додаються.
- Фільтрація / сортування tasks за датами на list-сторінці.
- Дедлайн-нотифікації, нагадування, overdue badges.
- Відображення дат у `TasksTableView` (table-вью).

## Expected Behavior

- На `TaskEditPage` користувач може вибрати `Start Date` і `Due Date` через DatePicker. Обидва nullable — можна очистити.
- Збереження → дати зберігаються в БД у форматі DATE (без часу).
- Backend повертає 422, якщо `due_date < start_date`.
- На details tab дати відображаються через `DisplayDate`, без часу.
- API `GET /tasks/{id}` (resource) і `GET /tasks` (overview resource) містять `start_date` і `due_date`.
- Існуюча поведінка task module не змінюється — `TaskCreateDialog` працює як і раніше.

## Technical Notes

- БД повністю перевикочується (`migrate:fresh --seed`) — фейкові дані, нова міграція не потрібна.
- `DatePicker` (PrimeVue) у проекті вже використовується (підключиться так само, як у task 001). Формат `value` — string `YYYY-MM-DD`. У PrimeVue `DatePicker` з `date-format="yy-mm-dd"` повертає `Date`, тож або серіалізувати при submit, або використати `model-value` як string з конвертацією. Подивитись, як це зроблено в `ProjectEditPage` (task 001), і повторити патерн 1-в-1.
- Submit payload відправляє `null` для порожніх дат (не пустий рядок).
- `TaskFactory` — якщо генерує `tasks`, дати можна не додавати (вони nullable). Якщо хочемо realistic тестові дані — додати `fake()->optional()->dateTimeBetween()->format('Y-m-d')`.
- PHPStan: додати `@property \Illuminate\Support\Carbon|null $start_date` і `$due_date` у doc-блок `TaskModel`.
- Не торкатися інших полів/міграцій/моделей. Не торкатися `TaskStatus` enum.

## Acceptance Criteria

- [ ] Міграція `create_tasks_table.php` містить `start_date` і `due_date`; `migrate:fresh --seed` проходить без помилок.
- [ ] `TaskModel` має нові поля у `#[Fillable]` і `casts()`.
- [ ] `StoreTaskRequest` і `UpdateTaskRequest` валідують нові поля; `due_date < start_date` повертає 422.
- [ ] `CreateTaskCommand`, `UpdateTaskCommand`, відповідні Handlers і `TasksController` передають нові поля.
- [ ] `TaskResource` і `TaskOverviewResource` повертають `start_date` і `due_date`.
- [ ] `ITask`, `TaskOverviewDto`, `ICreateTaskInput`, `IUpdateTaskInput` оновлено.
- [ ] `TaskEditPage` має робочі DatePicker'и для обох дат, ініціалізує з task, шле у submit, показує validation errors.
- [ ] `TaskOverviewPage` показує обидві дати через `DisplayDate`.
- [ ] Backend: `./vendor/bin/pint` + `./vendor/bin/phpstan analyse` проходять без помилок.
- [ ] Frontend: `npm run format` + `npm run lint` + `npm run types:check` проходять без помилок.
- [ ] Існуючі feature-тести Task API не зламані.

## Open Questions

- N/A

## Notes For Developer Agent

### Виконання через паралельні саб-агенти

Task маленький, але добре розпадається на дві паралельні зони:

**Sub-agent A — Backend:**
- Міграція + `TaskModel` (fillable, casts, PHPDoc) + Requests + Commands + Handlers + Controller + `TaskResource` + `TaskOverviewResource`.
- Перевірити: `pint`, `phpstan`, `php artisan test --filter=Task`.

**Sub-agent B — Frontend** (паралельно з A після того, як A зафіксував shape API — типи `ITask`, `ICreateTaskInput`, `IUpdateTaskInput`):
- Оновити `entities/task/types/`.
- Оновити `TaskEditPage` (DatePicker + form data + submit).
- Оновити `TaskOverviewPage` (DisplayDate).
- Перевірити: `npm run format && npm run lint && npm run types:check`.

### Інші важливі замітки

- Цей task **навмисно** не торкається статусу `archived`, `completed_at`, трейтів архівування — це окремі sprint'и.
- DatePicker patterns: подивитись як зроблено в `ProjectEditPage` (task 001 цього sprint) і скопіювати підхід (формат значення, валідація, відображення помилок) 1-в-1. Це забезпечить консистентність UX між Project і Task edit page.
- `TaskCreateDialog` НЕ чіпати — дати тільки на edit page.
- Не вводити нових пакетів.
- Не змінювати `TasksTableView` верстку.
