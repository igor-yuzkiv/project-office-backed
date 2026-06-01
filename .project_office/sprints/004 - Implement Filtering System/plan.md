---
type: sprint
status: draft
---

# Sprint 004 - Implement Filtering System

## Goal

Впровадити стандартизовану систему фільтрації списків для backend API та базову frontend-інфраструктуру для формування filter payload.

Sprint існує як підготовчий технічний sprint, який має розблокувати завершення Sprint 3 frontend CRUD без зміни поточних API routes.

## Expected Outcome

Після завершення sprint:

* backend має reusable Eloquent filter infrastructure в `app/Libs/EloquentFilters`;
* моделі можуть явно визначати allowed filters та allowed fields;
* API list endpoints можуть приймати `filters[]` payload;
* invalid filter payload повертає `400 Bad Request`;
* Projects API має окремий `POST search` endpoint на Laravel Scout з підтримкою filters по `name` та `prefix`;
* frontend має shared filter types, definitions та resolver для формування API payload;
* Projects table має мінімальну filter UI integration для end-to-end перевірки;
* існуючі routes залишаються без змін, додається тільки окремий Projects search route.

## Scope

* Backend filter foundation:
  * base filter abstraction;
  * resolver для `filters[]`;
  * allowed filters resolution;
  * allowed fields validation;
  * match modes;
  * domain exception для invalid filter payload;
  * Eloquent scope/trait для застосування фільтрів.
* Initial backend filters:
  * text;
  * integer;
  * boolean;
  * datetime;
  * nullable.
* Project API integration:
  * allowed filters for `ProjectModel`;
  * allowed fields: `name`, `prefix`;
  * new `POST search` method for Projects;
  * Laravel Scout search with filters;
  * `ProjectsController@index` remains a plain paginated projects list without filters.
* Frontend shared filters module:
  * filter payload types;
  * filter definition types;
  * match mode types;
  * resolver from UI state to API `filters[]`;
  * support for `params`;
  * shared generic filter sidebar/panel components.
* Projects table UI integration:
  * search field above Projects table;
  * Filters button above Projects table;
  * full-height filter sidebar opened by the Filters button;
  * filter controls for `name` and `prefix`;
  * API request integration;
  * design reference alignment with `projects_table_page` and Zoho Projects filter UI reference.

## Out Of Scope

* Changing existing API routes.
* Adding filters to `ProjectsController@index`.
* Replacing nested routes:
  * `/projects/{project}/task-lists`;
  * `/projects/{project}/tasks`.
* Adding flat `/task-lists` or `/tasks` endpoints.
* Migrating Task List or Task list endpoints to filters.
* Relationship filters.
* Nested filter groups.
* Full `AND` / `OR` query builder.
* Sorting as part of the filtering system.
* Advanced filter UI.
* Pixel-perfect copy of Zoho Projects UI.
* Final visual polish beyond the agreed MVP filter sidebar.

## Tasks

### 001 - Backend Eloquent Filters Foundation

Статус: todo

Створити backend filter infrastructure в `app/Libs/EloquentFilters`, включно з resolver, allowed filters, allowed fields validation, match modes, exceptions та базовими filter classes.

### 002 - Integrate Project Search API

Статус: todo

Додати allowed filters до `ProjectModel` та окремий `POST search` endpoint, який використовує Laravel Scout і підтримує `filters[]` для полів `name` і `prefix`. Поточний `index` має лишитись простим paginated list без filters.

### 003 - Frontend Shared Filters Infrastructure

Статус: todo

Створити shared frontend module для filter types, definitions, match modes, resolver, який формує API-compatible `filters[]` payload, та generic full-height filter sidebar components.

### 004 - Integrate Project Table Filters

Статус: todo

Додати toolbar над Projects table з search field і Filters button, а також full-height filter sidebar за Zoho Projects reference pattern для перевірки end-to-end flow.

## Dependencies

* Sprint 3 Project table implementation.
* Existing Projects API list endpoint.
* Existing frontend API request layer.
* Existing shared pagination and sorting params.
* Projects table concept design: `.project_office/design/concept/projects_table_page.png`.
* Filter sidebar reference: `.project_office/design/references/zoho_project_filters_ui_reference.png`.

## Risks

* Filter payload contract має бути однаковим на frontend і backend; розбіжність призведе до прихованих runtime помилок.
* Generic filters без strict allowed fields можуть випадково відкрити фільтрацію по небажаних DB columns.
* Nullable filter через `equals` / `notEquals` може бути неочевидним для UI, тому match mode labels треба описати в task.
* Reference UI взято з Zoho Projects, але потрібно адаптувати його до PrimeVue/Tailwind і поточного стилю project table, а не копіювати один-в-один.
* Якщо filter exception handling буде зроблено глобально занадто широко, це може вплинути на інші API errors.

## Open Questions

* Чи потрібно після Sprint 4 створити окремий sprint/task для flat `/task-lists` та `/tasks` endpoints, якщо вони залишаються актуальними для глобальних списків.
* Для Projects search API потрібно уточнити exact search query field name та behavior для empty query.
* Для Projects table потрібно узгодити, чи table завжди переходить на search endpoint, чи тільки коли активний search/filter state.

## Notes For Developer Agent

* Не змінювати існуючі API routes у межах цього sprint.
* Дозволено додати окремий Projects search route для task 002.
* Не додавати flat `/task-lists` або `/tasks` endpoints у межах цього sprint.
* Використовувати reference implementation з іншого проєкту тільки як технічний орієнтир, не переносити один-в-один.
* Backend payload contract: request query/body має містити `filters[]`.
* Кожен filter payload item має мати стандартизовану форму:

```ts
{
    filter: string
    field: string
    value: unknown
    matchMode: string | null
    params: Record<string, unknown>
}
```

* Frontend не повинен знати PHP class names.
* Shared frontend filters module має містити generic компоненти для filter sidebar/panel, але без Projects-specific logic.
* Backend filter class сам визначає свій filter key.
* Resolver має працювати тільки через model whitelist.
* Generic filters мають перевіряти `allowed_fields`.
* Invalid filter, invalid field або unsupported match mode мають повертати `400 Bad Request`.
* Для nullable filter використовувати окремий filter class:
  * `matchMode: 'equals'` означає `whereNull(field)`;
  * `matchMode: 'notEquals'` означає `whereNotNull(field)`.
* Для Projects першими allowed fields є тільки `name` і `prefix`.
* UI reference для task 004:
  * `.project_office/design/concept/projects_table_page.png` показує toolbar над таблицею з search field і кнопкою Filters;
  * `.project_office/design/references/zoho_project_filters_ui_reference.png` показує full-height filter sidebar/popup pattern.
