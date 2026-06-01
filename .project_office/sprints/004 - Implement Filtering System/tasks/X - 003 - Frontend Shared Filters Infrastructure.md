---
type: task
status: done
---

# 003 - Frontend Shared Filters Infrastructure

## Goal

Створити shared frontend infrastructure для опису filters, формування backend-compatible `filters[]` payload та generic UI components для filter sidebar.

## Context

Frontend має формувати standardized filter payload, але не повинен знати PHP class names або backend implementation details. Infrastructure має бути reusable для Project, Task List і Task list views у майбутньому.

Filter UI планується як reusable sidebar/popup на всю висоту, який відкривається окремою кнопкою з toolbar над таблицею. Компоненти sidebar мають бути generic і розміщені на рівні `shared`.

Reference implementation існує в:

* `/var/www/sir/backend/artisan_direct_frontend/packages/vue-data-filters`

UI reference:

* `.project_office/design/references/zoho_project_filters_ui_reference.png`

References потрібно використовувати тільки як орієнтир. Не переносити package structure один-в-один і не копіювати Zoho UI pixel-perfect.

## Scope

Що входить у задачу:

* Створити shared frontend filters module в `resources/js/shared`.
* Додати TypeScript types для:
  * filter payload item;
  * filter definitions;
  * filter UI state;
  * match modes;
  * params.
* Додати resolver і фабрику, який перетворює frontend filter state у API `filters[]`.
* Додати підтримку filter keys:
  * text;
  * integer;
  * boolean;
  * datetime;
  * nullable.
* Додати підтримку `params`.
* Додати generic shared UI components для filter sidebar/panel:
  * full-height sidebar/popup container;
  * optional filter search field всередині sidebar;
  * filter group/section rendering;
  * reset/apply/cancel action slots або props;
  * support для input controls, потрібних initial filters.
* Експортувати module через shared public API згідно з існуючими frontend patterns.

## Out Of Scope

Що не входить у задачу:

* Projects-specific table UI integration.
* Advanced query builder UI.
* Nested filter groups.
* Relationship filters.
* Sorting.
* Backend changes.
* Новий npm package.
* Pixel-perfect copy of Zoho Projects UI.

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

Shared UI components:

* можуть бути використані з різними entity filter definitions;
* не містять Projects-specific fields або API calls;
* дозволяють parent component відкрити/закрити full-height sidebar;
* дозволяють parent component обробити apply/reset/cancel.

## Technical Notes

* Дотримуватись Feature-Sliced inspired structure.
* Shared module має бути entity-agnostic.
* Не встановлювати нові packages.
* Використати існуючі TypeScript conventions.
* Якщо потрібні базові UI input types, тримати їх generic і не прив'язувати до Projects.
* Sidebar має адаптувати Zoho reference до поточного PrimeVue/Tailwind стилю, а не копіювати його один-в-один.

## Acceptance Criteria

* [x] Існує shared frontend filters module.
* [x] Є typed filter payload.
* [x] Є typed filter definitions/state.
* [x] Є typed match modes.
* [x] Resolver формує `filters[]` payload.
* [x] Resolver підтримує `params`.
* [x] Resolver підтримує text, integer, boolean, datetime, nullable.
* [x] Існують generic shared UI components для full-height filter sidebar/panel.
* [x] Sidebar components підтримують reset/apply/cancel integration.
* [x] Sidebar components не містять Projects-specific logic.
* [x] Frontend не містить PHP class names.
* [x] Module експортується згідно з existing shared patterns.
* [x] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* N/A

## Notes For Developer Agent

Не додавати Projects-specific logic у shared module. Projects integration запланована окремою task.

Shared components мають бути достатніми, щоб task 004 могла відкрити filter sidebar з кнопки Filters над Projects table.
