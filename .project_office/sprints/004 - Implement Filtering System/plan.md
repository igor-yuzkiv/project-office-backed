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
  * support for `params`.
* Projects table UI integration:
  * minimal filter controls for `name` and `prefix`;
  * API request integration;
  * placeholder for final design details.

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
* Final visual polish for filter UI.

## Tasks

### 001 - Backend Eloquent Filters Foundation

Статус: todo

Створити backend filter infrastructure в `app/Libs/EloquentFilters`, включно з resolver, allowed filters, allowed fields validation, match modes, exceptions та базовими filter classes.

### 002 - Integrate Project Search API

Статус: todo

Додати allowed filters до `ProjectModel` та окремий `POST search` endpoint, який використовує Laravel Scout і підтримує `filters[]` для полів `name` і `prefix`. Поточний `index` має лишитись простим paginated list без filters.

### 003 - Frontend Shared Filters Infrastructure

Статус: todo

Створити shared frontend module для filter types, definitions, match modes та resolver, який формує API-compatible `filters[]` payload.

### 004 - Integrate Project Table Filters

Статус: todo

Додати мінімальну filter UI integration до Projects table для перевірки end-to-end flow. Дизайн-деталі залишаються placeholder до окремого уточнення.

## Dependencies

* Sprint 3 Project table implementation.
* Existing Projects API list endpoint.
* Existing frontend API request layer.
* Existing shared pagination and sorting params.
* Design details for Projects table filter UI are not finalized yet.

## Risks

* Filter payload contract має бути однаковим на frontend і backend; розбіжність призведе до прихованих runtime помилок.
* Generic filters без strict allowed fields можуть випадково відкрити фільтрацію по небажаних DB columns.
* Nullable filter через `equals` / `notEquals` може бути неочевидним для UI, тому match mode labels треба описати в task.
* Інтеграція UI без фінального дизайну може потребувати follow-up polish task.
* Якщо filter exception handling буде зроблено глобально занадто широко, це може вплинути на інші API errors.

## Open Questions

* Остаточний дизайн Projects table filter UI ще не визначений.
* Чи потрібно після Sprint 4 створити окремий sprint/task для flat `/task-lists` та `/tasks` endpoints, якщо вони залишаються актуальними для глобальних списків.

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
* Backend filter class сам визначає свій filter key.
* Resolver має працювати тільки через model whitelist.
* Generic filters мають перевіряти `allowed_fields`.
* Invalid filter, invalid field або unsupported match mode мають повертати `400 Bad Request`.
* Для nullable filter використовувати окремий filter class:
  * `matchMode: 'equals'` означає `whereNull(field)`;
  * `matchMode: 'notEquals'` означає `whereNotNull(field)`.
* Для Projects першими allowed fields є тільки `name` і `prefix`.
* Якщо на момент реалізації task 004 дизайн filter UI ще не буде готовий, Developer Agent має зупинитись і нагадати автору про потребу додати дизайн-опис або підтвердити minimal UI.
