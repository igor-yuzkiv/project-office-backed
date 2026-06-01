---
type: task
status: draft
---

# 001 - Backend Eloquent Filters Foundation

## Goal

Створити backend foundation для декларативної фільтрації list/search queries через standardized `filters[]` payload.

## Context

Sprint 3 frontend CRUD потребує стабільного підходу до фільтрації list endpoints. Поточні endpoints вже мають pagination і sorting, але не мають єдиного механізму filters.

Ця задача створює reusable infrastructure, яку наступні задачі зможуть підключати до конкретних моделей та контролерів.

Reference implementation існує в:

* `/var/www/sir/backend/pilot_back_artisan_direct/app/Core/Services/Filter`
* `/var/www/sir/backend/pilot_back_artisan_direct/app/Core/Services/Filter/Filters`

Reference потрібно використовувати тільки як орієнтир. Не переносити aliases, string schema format або зайву універсальність.

## Scope

Що входить у задачу:

* Створити backend module в `app/Libs/EloquentFilters`.
* Додати base abstraction/contract для filter classes.
* Додати resolver для `filters[]` payload.
* Додати механізм allowed filters на рівні моделі.
* Додати підтримку allowed fields для generic filters.
* Додати match mode enum/value object.
* Додати domain exception для invalid filter payload.
* Додати trait/scope/helper для застосування фільтрів до query.
* Реалізувати initial filters:
  * text;
  * integer;
  * boolean;
  * datetime;
  * nullable.
* Додати targeted backend tests для resolver та базових filters.

## Out Of Scope

Що не входить у задачу:

* Інтеграція filters у конкретні API endpoints.
* Зміна routes.
* Flat `/task-lists` або `/tasks` endpoints.
* Relationship filters.
* Nested filter groups.
* Full `AND` / `OR` logic.
* Sorting.
* Frontend changes.

## Expected Behavior

Backend приймає масив filter payload items у форматі:

```ts
{
    filter: string
    field: string
    value: unknown
    matchMode: string | null
    params: Record<string, unknown>
}
```

Resolver:

* перевіряє, що filter key існує в allowed filters моделі;
* створює тільки allowed filter classes;
* передає payload у filter class;
* перевіряє `field` через `allowed_fields` для generic filters;
* кидає domain exception для invalid filter, invalid field або unsupported match mode.

Nullable filter:

* `matchMode: 'equals'` застосовує `whereNull(field)`;
* `matchMode: 'notEquals'` застосовує `whereNotNull(field)`.

Invalid filter payload має бути перетворений API layer у `400 Bad Request`.

## Technical Notes

* Не використовувати aliases.
* Не використовувати string schema format.
* Filter class сам визначає свій key.
* Model whitelist має бути explicit.
* Generic filters не повинні дозволяти довільні DB columns.
* Не вводити FormRequest або DTO для `filters[]` у цій задачі. Resolver сам валідить payload.
* Якщо потрібен global exception mapping, зробити його мінімально scoped до filter exception.
* Infrastructure має бути сумісною з Eloquent Builder і Laravel Scout Builder там, де конкретний filter може бути застосований до Scout search.

## Acceptance Criteria

* [ ] Існує `app/Libs/EloquentFilters` з основними класами filter infrastructure.
* [ ] Модель може явно визначити allowed filters.
* [ ] Generic filters підтримують `allowed_fields`.
* [ ] Resolver не приймає filter key поза whitelist.
* [ ] Resolver не приймає field поза `allowed_fields`.
* [ ] Invalid filter payload кидає dedicated domain exception.
* [ ] API response для filter domain exception має статус `400 Bad Request`.
* [ ] Реалізовані filters: text, integer, boolean, datetime, nullable.
* [ ] Nullable filter підтримує `equals` та `notEquals`.
* [ ] Filter infrastructure може бути використана search endpoint на Laravel Scout.
* [ ] Додані targeted tests для resolver та initial filters.
* [ ] Backend validation виконана пропорційно зміні: Pint, PHPStan, relevant tests.

## Open Questions

* N/A

## Notes For Developer Agent

Це foundation task. Не підключати filters до Projects API в цій задачі, якщо це не потрібно для тестового fixture. Інтеграція Projects API запланована окремою task.
