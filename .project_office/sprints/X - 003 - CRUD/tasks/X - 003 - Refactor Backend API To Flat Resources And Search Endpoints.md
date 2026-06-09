---
type: task
status: draft
---

# 003 - Refactor Backend API To Flat Resources And Search Endpoints

## Goal

Переробити backend API для CRUD-сутностей Sprint 3 з поточної вкладеної структури routes на плоску resource-структуру та додати search endpoints для пошуку і фільтрації.

## Context

Поточний backend API для `TaskList` і `Task` побудований навколо вкладених routes:

```txt
/projects/{project}/task-lists
/projects/{project}/tasks
```

Це блокує подальшу frontend CRUD реалізацію, бо `TaskList` і `Task` мають використовуватись не тільки в контексті одного Project, а й у глобальних списках.

У коді вже є приклад для Projects:

* `routes/api.php` містить `POST /projects/search`;
* `app/Http/Controllers/Projects/ProjectsController.php` має `search(Request $request)`;
* `ProjectModel` використовує Laravel Scout і `allowedFilters()`.

Цю задачу потрібно використати як backend API refactor task перед продовженням frontend CRUD для Task Lists і Tasks.

## Scope

Що входить у задачу:

* Перейти від nested routes до flat API resources для сутностей Sprint 3:
  * `Project`;
  * `TaskList`;
  * `Task`.
* Залишити Projects resource плоским:
  * `GET /projects`;
  * `POST /projects`;
  * `GET /projects/{project}`;
  * `PATCH /projects/{project}`;
  * `DELETE /projects/{project}`.
* Переробити Task Lists API на flat routes:
  * `GET /task-lists`;
  * `POST /task-lists`;
  * `GET /task-lists/{taskList}`;
  * `PATCH /task-lists/{taskList}`;
  * `DELETE /task-lists/{taskList}`.
* Переробити Tasks API на flat routes:
  * `GET /tasks`;
  * `POST /tasks`;
  * `GET /tasks/{task}`;
  * `PATCH /tasks/{task}`;
  * `DELETE /tasks/{task}`.
* Додати search endpoints для всіх CRUD-сутностей Sprint 3:
  * `POST /projects/search`;
  * `POST /task-lists/search`;
  * `POST /tasks/search`.
* Реалізувати `search` method у відповідних controllers за прикладом `ProjectsController::search`.
* Використати Laravel Scout для search endpoints.
* Підтримати `filters[]` у search endpoints.
* Додати `allowedFilters()` до моделей, яким це потрібно.
* Оновити request validation для flat `store` / `update`, якщо route context більше не містить `ProjectModel`.
* Оновити backend tests або додати targeted feature tests для нових API routes.

## Out Of Scope

Що не входить у задачу:

* Frontend API layer refactor.
* UI зміни.
* Advanced filter UI.
* Relationship filter groups.
* Sorting як частина filter system.
* Зміна response resource shape без окремого погодження.
* Attachments API.

## Expected Behavior

`Project`, `TaskList` і `Task` мають мати плоскі CRUD endpoints.

`TaskList` більше не повинен вимагати `ProjectModel $project` як route parameter у controller methods.

`Task` більше не повинен вимагати `ProjectModel $project` як route parameter у controller methods.

Project context для `TaskList` і `Task` має передаватись через payload або filter/search механізм, а не через nested URL.

Search endpoints:

* приймають search query у полі `query`;
* приймають `filters[]`;
* використовують Laravel Scout;
* повертають paginated resource collection;
* мають працювати з порожнім `query`, щоб дозволити filter-only search.

Invalid filters мають повертати `400 Bad Request` згідно з filter infrastructure behavior.

## Technical Notes

* Використати `ProjectsController::search` як pattern для `TaskListsController::search` і `TasksController::search`.
* Не дублювати business logic у controllers поза необхідною query/search orchestration.
* Controllers мають залишатись thin.
* Для flat `TaskList` creation потрібно визначити `project_id` у request payload.
* Для flat `Task` creation потрібно визначити `project_id` у request payload.
* `task_list_id` для `Task` залишається payload field.
* `TaskListModel` і `TaskModel` вже використовують Laravel Scout, але потребують allowed filters.
* Якщо nested routes видаляються, оновити route names і tests відповідно.
* Якщо nested routes тимчасово залишаються для backward compatibility, це має бути явно погоджено перед реалізацією.

## Acceptance Criteria

* [ ] `routes/api.php` містить flat API resources для Projects, Task Lists і Tasks.
* [ ] Nested `projects.task-lists` routes більше не є основним API контрактом.
* [ ] Nested `projects.tasks` routes більше не є основним API контрактом.
* [ ] `TaskListsController` працює без `ProjectModel $project` route parameter.
* [ ] `TasksController` працює без `ProjectModel $project` route parameter.
* [ ] `TaskList` create/update validation підтримує flat API payload.
* [ ] `Task` create/update validation підтримує flat API payload.
* [ ] `POST /projects/search` існує і працює як pattern endpoint.
* [ ] `POST /task-lists/search` існує.
* [ ] `POST /tasks/search` існує.
* [ ] Search endpoints використовують Laravel Scout.
* [ ] Search endpoints підтримують `query`.
* [ ] Search endpoints підтримують `filters[]`.
* [ ] Search endpoints повертають paginated resource collections.
* [ ] `TaskListModel` має allowed filters.
* [ ] `TaskModel` має allowed filters.
* [ ] Invalid filters повертають `400 Bad Request`.
* [ ] Додані або оновлені relevant backend feature tests.
* [ ] Backend validation виконана пропорційно зміні: Pint, PHPStan, relevant tests.

## Open Questions

* Чи nested routes потрібно повністю видалити у цій task, чи тимчасово залишити як backward-compatible aliases?
* Який exact allowed filters list для `TaskListModel`?
* Який exact allowed filters list для `TaskModel`?
* Чи `project_id` у flat create/update payload має бути required для `TaskList` і `Task`?
* Чи `task_list_id` у `Task` може бути nullable при flat API?

## Notes For Developer Agent

Не починати реалізацію, якщо open questions вище не закриті.

Ця задача змінює API contract і має виконуватись до продовження frontend CRUD для Task Lists і Tasks.

Не змінювати Attachments API в межах цієї task.
