---
type: sprint
status: draft
---

# Sprint 007 - Time Logs

## Goal

Додати облік часу на задачі: створення, редагування, видалення time logs, перегляд логів на рівні задачі і глобально через окрему сторінку у sidebar.

## Expected Outcome

- Користувач може залогувати час на конкретну задачу через діалог.
- Користувач може редагувати та видаляти існуючі логи.
- На сторінці задачі є вкладка / підсторінка з логами цієї задачі і кнопкою створення нового логу.
- У sidebar є глобальна сторінка Time Logs зі списком усіх логів і кнопкою створення.
- Сума логів одного користувача за день не може перевищувати 24 години.
- PM може створити лог від імені іншого користувача — у списку видно і виконавця (`user`), і автора (`created_by`).

## Scope

Backend:
- Сутність `TimeLog`: `id` (ULID), `task_id` (ULID, nullable, FK ON DELETE SET NULL), `user_id`, `created_by`, `updated_by`, `date` (UTC, date-only), `minutes` (int), `type` (`billable` / `non_billable`), `notes` (text), timestamps.
- Enum `TimeLogType`.
- CRUD endpoints `/api/time-logs` (`index`, `store`, `update`, `destroy`).
- Custom validation rule: сума `minutes` для `user_id` + `date` ≤ 1440 (при update — виключаючи поточний лог).
- Мінімальний `GET /api/users` (з опц. `search`) і `UserResource` — для `UserLookupField`.

Frontend:
- `entities/time-log/` (api, types, queries, mutations, config).
- Розширення `entities/user/` (list/search api, queries).
- Widget `UserLookupField` (на базі `shared/components/input/LookupField`).
- Widget `TaskLookupField` (на існуючому Task search endpoint).
- `TimeLogUpsertDialog` (create + edit): поля `task`, `user`, `date`, `time` (`InputMask 99:99`), `type` (dropdown), `notes` (`MarkdownEditor` з `preview: false`).
- Сторінка time logs на task details (вкладка / підсторінка, за зразком `ProjectTasksPage`).
- Глобальна сторінка time logs (за зразком `TasksPage`).
- Sidebar entry для глобальної сторінки.
- Edit і delete actions у списку логів.

## Out Of Scope

- Привʼязка time logs до Project або інших сутностей.
- Soft delete на Task (використовуємо ON DELETE SET NULL для `task_id`).
- Звіти, sum-агрегації по project / user / period.
- Фільтри на global сторінці Time Logs (тільки pagination, хронологічний порядок).
- Ролі / permissions понад "authenticated user".
- Теги для time logs.
- Окремі timer / "start–stop" механіки.

## Tasks

### 001 - Backend Time Log Foundation

Статус: todo

Модель `TimeLogModel`, міграція, enum `TimeLogType` (`billable`, `non_billable`), relations (`task`, `user`, `createdBy`, `updatedBy`). `task_id` nullable з ON DELETE SET NULL.

### 002 - Backend Time Log CRUD Endpoints

Статус: todo

`TimeLogsController` (`index` з опц. `task_id`, `store`, `update`, `destroy`), Handlers, FormRequests із custom валідацією "сума `minutes` для `user_id` + `date` ≤ 1440 хв" (при update — виключаючи поточний лог), `TimeLogResource`, pagination.

### 003 - Backend Users Search Endpoint

Статус: todo

Мінімальний `UsersController@index` з опц. `search`, `UserResource`. Тільки те, що необхідне для `UserLookupField`.

### 004 - Frontend Time Log Entity Layer

Статус: todo

`entities/time-log/`: api, types (`TimeLog`, payloads), queries (list — для task-level і global, з підтримкою опц. `task_id`), mutations (create, update, delete), config (query keys).

### 005 - Frontend User Lookup Foundation

Статус: todo

Розширити `entities/user/` (api + queries для list/search). Створити widget `UserLookupField` на базі `shared/components/input/LookupField`, за прикладом `ProjectLookupField`.

### 006 - Frontend Task Lookup Field

Статус: todo

Widget `TaskLookupField` на існуючому Task search endpoint. Потрібен для глобальної сторінки Time Logs, де користувач обирає задачу при створенні логу.

### 007 - Frontend Time Log Upsert Dialog

Статус: todo

`TimeLogUpsertDialog`: підтримує і create, і edit. Поля: `task` (readonly у task-level контексті, через `TaskLookupField` у global), `user` (через `UserLookupField`, default — current user), `date`, `time` (`InputMask 99:99`), `type` (dropdown, default `billable`), `notes` (`MarkdownEditor` з `preview: false`).

### 008 - Frontend Time Logs Pages + Integration

Статус: todo

Нова task-level сторінка time logs на task details (вкладка / підсторінка), нова global сторінка time logs у sidebar, routing, кнопки створення (на обох сторінках), edit/delete actions у списку логів. На global сторінці orphan лог (`task: null`) відображати як `(deleted task)`.

## Dependencies

- 002 залежить від 001.
- 004 залежить від 002.
- 005 залежить від 003.
- 007 залежить від 004, 005, 006.
- 008 залежить від 007.

## Risks

- Custom валідація "сума ≤ 1440 хв на день": edge case при зміні `date` або `user_id` через update — потребує targeted test.
- UTC vs user local TZ: на frontend `date` приходить як date-only string; не конвертувати у `Date` без явної UTC normalization, інакше можливе зміщення дня.
- Eager loads (`user`, `task`, `createdBy`, `updatedBy`) на list endpoint — ризик N+1.
- `TimeLogResource` з nullable `task` — UI повинен бути готовий до `task: null` і не падати.
- Відсутність `UsersController` і `TaskLookupField` — sprint містить дві mini-foundation, які виглядають як side-quest. Без них основна задача нереалізована.

## Open Questions

- Чи `UserLookupField` оформлювати як повноцінний widget у `widgets/users/lookup-field/`, чи достатньо тримати тонкий wrapper у `entities/user/ui/`? (Рішення приймемо при фіналізації task 005.)
- Чи `TaskLookupField` виносити окремим widget-ом у `widgets/tasks/lookup-field/`, чи інтегрувати inline у task 007 як internal helper? (Рішення приймемо при фіналізації task 006.)

## Notes For Developer Agent

- `MarkdownEditor.vue` уже існує у `shared/components/md-editor/ui/`; використовувати з `preview: false`.
- `LookupField.vue` уже існує у `shared/components/input/ui/`; будувати `UserLookupField` і `TaskLookupField` на його основі, як `ProjectLookupField` (`widgets/projects/lookup-field/ui/ProjectLookupField.vue`).
- Pages організувати за прикладом `pages/tasks/list/TasksPage.vue` (глобальна) і `pages/projects/details/tabs/ProjectTasksPage.vue` (task-level).
- Sidebar entry додати у `app/shell/ui/navigation/AppLeftNavigationSidebar.vue`.
- Routing — у `app/router/index.ts`.
- Хто `created_by` / `updated_by` визначається бекендом автоматично з auth user, не приймати з payload.
- Time зберігати тільки як `minutes: int`; форматування "HH:MM" — тільки на frontend.
