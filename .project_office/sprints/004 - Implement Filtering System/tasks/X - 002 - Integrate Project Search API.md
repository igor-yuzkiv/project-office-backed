---
type: task
status: draft
---

# 002 - Integrate Project Search API

## Goal

Додати окремий Projects search endpoint, який використовує Laravel Scout і підтримує backend filter infrastructure.

## Context

Після створення backend filter foundation потрібно перевірити її на реальному API endpoint.

Поточний `ProjectsController@index` не повинен підтримувати filters. Він має залишитися простим endpoint для paginated projects list.

Для пошуку потрібен окремий `POST search` method, який використовує Laravel Scout. Laravel Scout вже є в проєкті, а `ProjectModel` вже використовує `Searchable`.

## Scope

Що входить у задачу:

* Додати allowed filters до `ProjectModel`.
* Дозволити фільтрацію Projects по полях:
  * `name`;
  * `prefix`.
* Додати окремий `POST search` route для Projects.
* Додати `search` method до `ProjectsController`.
* Використати Laravel Scout для пошуку Projects.
* Підтримати `filters[]` у search endpoint.
* Зберегти існуючу pagination behavior для search results.
* Додати feature tests для Projects search filtering.

## Out Of Scope

Що не входить у задачу:

* Додавання filters до `ProjectsController@index`.
* Зміна поведінки `GET /api/projects`.
* Зміна існуючих API routes.
* Зміна Projects CRUD behavior.
* Flat endpoints для Task Lists або Tasks.
* Фільтрація Task Lists або Tasks.
* Frontend integration.
* Новий дизайн.

## Expected Behavior

`GET /api/projects` продовжує працювати як раніше: повертає paginated projects list без filter support.

Новий `POST /api/projects/search` виконує пошук Projects через Laravel Scout і підтримує `filters[]`.

Search endpoint:

* приймає search query для Scout;
* приймає `filters[]`;
* підтримує фільтрацію по `name`;
* підтримує фільтрацію по `prefix`;
* повертає paginated response у тому ж resource shape, що й Projects list;
* повертає `400 Bad Request` для invalid field;
* повертає `400 Bad Request` для invalid filter key.

Pagination має працювати разом із search і filters.

## Technical Notes

* Не змінювати `ProjectsController@index`.
* Не додавати filters до `GET /api/projects`.
* Не змінювати response resource shape.
* Не додавати FormRequest/DTO для filters.
* Використати infrastructure з task 001.
* Allowed fields для Projects: тільки `name`, `prefix`.
* Filter infrastructure має бути сумісною з Laravel Scout builder там, де це потрібно для search endpoint.

## Acceptance Criteria

* [ ] `ProjectModel` визначає allowed filters.
* [ ] `GET /api/projects` не застосовує `filters[]`.
* [ ] Додано `POST /api/projects/search`.
* [ ] `ProjectsController@search` використовує Laravel Scout.
* [ ] Search endpoint підтримує `filters[]`.
* [ ] `name` filter працює в search endpoint.
* [ ] `prefix` filter працює в search endpoint.
* [ ] Invalid field повертає `400 Bad Request`.
* [ ] Invalid filter key повертає `400 Bad Request`.
* [ ] Pagination сумісна з search і filters.
* [ ] Додані feature tests для Projects search filtering.
* [ ] Backend validation виконана пропорційно зміні: Pint, PHPStan, relevant tests.

## Open Questions

* Потрібно уточнити точну назву поля для Scout search query в request payload: `query`, `search`, чи `q`.
* Потрібно уточнити поведінку search endpoint при порожньому search query: повертати всі projects з filters чи вимагати непорожній query.

## Notes For Developer Agent

Не розширювати allowed fields поза `name` і `prefix` без окремого погодження.

Перед реалізацією уточнити open questions щодо search request payload і empty query behavior, якщо вони ще не закриті в документації.
