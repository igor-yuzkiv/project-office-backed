---
type: task
status: draft
---

# 003 - Frontend Shared Filters Infrastructure

## Goal

Створити shared frontend infrastructure для опису filters і формування backend-compatible `filters[]` payload.

## Context

Frontend має формувати standardized filter payload, але не повинен знати PHP class names або backend implementation details. Infrastructure має бути reusable для Project, Task List і Task list views у майбутньому.

Reference implementation існує в:

* `/var/www/sir/backend/artisan_direct_frontend/packages/vue-data-filters`

Reference потрібно використовувати тільки як орієнтир. Не переносити package structure один-в-один, якщо вона не відповідає поточній архітектурі `resources/js/shared`.

## Scope

Що входить у задачу:

* Створити shared frontend filters module в `resources/js/shared`.
* Додати TypeScript types для:
  * filter payload item;
  * filter definitions;
  * filter UI state;
  * match modes;
  * params.
* Додати resolver, який перетворює frontend filter state у API `filters[]`.
* Додати підтримку filter keys:
  * text;
  * integer;
  * boolean;
  * datetime;
  * nullable.
* Додати підтримку `params`.
* Експортувати module через shared public API згідно з існуючими frontend patterns.

## Out Of Scope

Що не входить у задачу:

* Projects table UI integration.
* Advanced query builder UI.
* Nested filter groups.
* Relationship filters.
* Sorting.
* Backend changes.
* Новий npm package.

## Expected Behavior

Frontend може описати filter definitions і отримати API payload у форматі:

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

* пропускає disabled/empty filters;
* не включає invalid empty values у payload;
* підтримує `params`;
* для nullable filter формує payload з `matchMode: 'equals'` або `matchMode: 'notEquals'`;
* не використовує PHP class names.

## Technical Notes

* Дотримуватись Feature-Sliced inspired structure.
* Shared module має бути entity-agnostic.
* Не встановлювати нові packages.
* Використати існуючі TypeScript conventions.
* Якщо потрібні базові UI input types, тримати їх generic і не прив'язувати до Projects.

## Acceptance Criteria

* [ ] Існує shared frontend filters module.
* [ ] Є typed filter payload.
* [ ] Є typed filter definitions/state.
* [ ] Є typed match modes.
* [ ] Resolver формує `filters[]` payload.
* [ ] Resolver підтримує `params`.
* [ ] Resolver підтримує text, integer, boolean, datetime, nullable.
* [ ] Frontend не містить PHP class names.
* [ ] Module експортується згідно з existing shared patterns.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* N/A

## Notes For Developer Agent

Не додавати Projects-specific logic у shared module. Projects integration запланована окремою task.
