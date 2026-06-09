---
type: task
status: draft
---

# 006 - Create Task Dialog

## Goal

Створити мінімальний flow створення `Task` з глобальної `TasksPage`: користувач відкриває dialog, вводить назву задачі, вибирає project через autocomplete, створює task і після успіху переходить на порожню task details page.

## Context

Sprint 3 готує початковий frontend CRUD для основних сутностей. `TasksPage` вже існує як глобальний список задач і має header action `add-task`, але зараз action не відкриває create flow.

Для MVP create dialog має містити тільки мінімальний набір полів. Повне редагування task, description та інші детальні поля не входять у цю задачу.

Поточний backend contract вимагає `priority` під час створення task. Це суперечить мінімальному UI scope цієї задачі, тому в межах задачі потрібно змінити contract: `priority` має бути nullable і за замовчуванням не заданий.

## Scope

Що входить у задачу:

* Додати мінімальний create task dialog на `resources/js/pages/tasks/TasksPage.vue`.
* Відкривати dialog через існуючий header action `add-task`.
* Dialog має містити тільки поля:
  * `name`;
  * `project`.
* Використати `PrimeVue Dialog` для вікна створення task.
* Використати `PrimeVue AutoComplete` локально всередині task dialog для вибору project.
* Не створювати reusable lookup component у межах цієї задачі.
* Project option у autocomplete відображати як `prefix - name`.
* Для project autocomplete використати існуючий project search flow:
  `resources/js/entities/project/queries/use.projects-search.query.ts`.
* При відкритті або початковому стані autocomplete має показувати початкові project suggestions.
* Пошук project suggestions має працювати з debounce через VueUse.
* Створити task create mutation у `resources/js/entities/task/mutations`.
* Після успішного створення task інвалідовувати task queries.
* Після успішного створення task перенаправляти користувача на task details page.
* Створити порожню route-level `TaskDetailsPage`.
* Додати route для task details page.
* Task details page у межах цієї задачі має бути тільки placeholder/empty page.
* Змінити backend contract для task priority:
  * `priority` у create task request більше не required;
  * `priority` може бути `null`;
  * якщо priority не передано, task створюється з `priority = null`;
  * task API response має коректно віддавати `priority: null`.
* Оновити frontend task types під nullable `priority`.
* Оновити task list UI так, щоб nullable `priority` не ламав відображення.

## Out Of Scope

Що не входить у задачу:

* Повна task details page.
* Task edit page.
* Повне редагування task.
* Поле `description` у create dialog.
* Поле `priority` у create dialog.
* Поле `task_list_id` у create dialog.
* Task delete flow.
* Reusable/generic lookup component.
* Pixel-perfect design polish.
* Kanban/calendar/task workflow UI.

## Expected Behavior

Користувач відкриває `TasksPage`, натискає `Add Task` у header actions і бачить create task dialog.

У dialog користувач вводить `name` і вибирає project через autocomplete. Project suggestions показуються у форматі `prefix - name`, підтримують початковий список і debounce-пошук.

Після submit створюється task без заданого priority. Backend зберігає `priority = null`, а response повертає `priority: null`.

Після успішного створення dialog закривається, task queries інвалідовуються, і користувача перенаправляє на route-level task details page створеної task. Task details page поки не показує повний details UI і може містити placeholder.

## Technical Notes

* Dialog можна реалізувати як widget за аналогією з `resources/js/widgets/projects/upsert-dialog`, якщо це не розширює scope непропорційно.
* Логіку відкриття, закриття, submit, selected project, search term і validation errors бажано винести в composable за pattern `useProjectUpsertDialog`.
* Для autocomplete не створювати shared lookup abstraction у цій задачі.
* Для debounce використати вже встановлений VueUse.
* `createTaskRequest` вже існує в `resources/js/entities/task/api/task.api.ts`.
* `resources/js/entities/task/mutations` наразі відсутній і має бути створений за pattern project mutations.
* `TaskQueryKey.all` вже існує і може використовуватись для invalidation.
* Поточний `ICreateTaskInput` містить required `priority`; його потрібно оновити під новий backend contract.
* Поточний `ITask.priority` не nullable; його потрібно оновити.
* Поточний backend `TaskResource` формує `priority` як object; після зміни contract він має коректно обробляти `null`.
* Поточний backend `StoreTaskRequest` вимагає `priority`; validation потрібно змінити.
* Поточна database schema має non-null `tasks.priority`; потрібна backend зміна schema/migration, щоб дозволити `null`.
* Якщо існуючі backend tests очікують required priority, їх потрібно оновити або додати targeted tests для nullable priority.
* Route name для task details бажано зробити консистентним з project details, наприклад `task-details`.

## Acceptance Criteria

* [ ] Header action `add-task` на `TasksPage` відкриває create task dialog.
* [ ] Dialog реалізований через `PrimeVue Dialog`.
* [ ] Create task dialog містить тільки `name` і `project`.
* [ ] Project field реалізований через локальний `PrimeVue AutoComplete`.
* [ ] Reusable lookup component не створений у межах цієї задачі.
* [ ] Project options відображаються у форматі `prefix - name`.
* [ ] Project autocomplete показує початкові suggestions.
* [ ] Project autocomplete підтримує debounce search через VueUse.
* [ ] Submit викликає task create mutation.
* [ ] Task create mutation створена в `resources/js/entities/task/mutations`.
* [ ] Після успішного створення task queries інвалідовуються.
* [ ] Після успішного створення користувача перенаправляє на task details page створеної task.
* [ ] Існує route-level `TaskDetailsPage`.
* [ ] Task details page у цій задачі є placeholder/empty page без повного details UI.
* [ ] Backend create task contract дозволяє не передавати `priority`.
* [ ] Backend зберігає `priority = null`, якщо priority не передано.
* [ ] Task API response повертає `priority: null` для task без priority.
* [ ] Frontend task types підтримують nullable `priority`.
* [ ] `TasksPage` не ламається при task з `priority: null`.
* [ ] Validation errors з API відображаються у form.
* [ ] Loading/pending state відображається під час submit.
* [ ] Повне редагування task не реалізоване у межах задачі.
* [ ] Backend validation виконана пропорційно зміні: Pint, PHPStan, relevant tests.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* Немає критичних відкритих питань для старту реалізації.

## Notes For Developer Agent

Ця задача свідомо містить невеликий backend contract change, бо мінімальний create task dialog не має поля `priority`, а поточний API вимагає його.

Не створювати reusable lookup component у межах цієї задачі. Використати PrimeVue `AutoComplete` локально в task dialog.

Task details page має бути тільки route placeholder. Не реалізовувати read-only details layout, edit form або додаткові task sections у межах цієї задачі.
