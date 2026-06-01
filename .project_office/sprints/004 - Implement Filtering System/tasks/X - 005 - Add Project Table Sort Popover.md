---
type: task
status: draft
---

# 005 - Add Project Table Sort Popover

## Goal

Додати Projects table sort popover, який дозволяє користувачу вибрати поле сортування та напрямок сортування через toolbar над таблицею.

## Context

Projects table вже має pagination і sorting params, а task 004 додає toolbar із search field та Filters button. Ця задача додає окремий shared sort module і UI control для sorting, не змішуючи sorting із filter infrastructure.

UI має враховувати два дизайн-орієнтири:

* `.project_office/design/concept/projects_table_page.png` — toolbar над таблицею з прикладом Sort button;
* `.project_office/design/references/zoho_project_srot_popover_ui_reference.png` — compact popover pattern з `Sort By`, select для поля, select для direction та close action.

## Scope

Що входить у задачу:

* Створити shared sort module у `resources/js/shared/sort/`.
* Додати shared sort types/config для опису sort fields і sort direction.
* Додати composable для керування sort popover і sort state за підходом, подібним до `shared/filters/composables/use.filter-sidebar.ts`.
* Додати reusable sort UI components у `shared/sort/ui`, якщо вони лишаються entity-agnostic.
* Додати Sort button у Projects table toolbar поруч із search і Filters controls.
* Відкривати compact sort popover через Sort button.
* Додати в popover select для sort field.
* Додати в popover select для sort direction.
* Додати close action у popover.
* Показувати поточне sort field у label кнопки, якщо це відповідає існуючому toolbar pattern.
* Підключити вибраний sort до існуючих Projects table sorting params.
* Забезпечити, що sorting працює разом із search, filters і pagination.
* Використати існуючі PrimeVue/Tailwind patterns.

## Out Of Scope

Що не входить у задачу:

* Backend changes.
* Зміна існуючого sorting API contract.
* Sorting як частина `filters[]` payload.
* Додавання sorting logic у `shared/filters`.
* Advanced multi-column sorting.
* Saved sort presets.
* Pixel-perfect copy of Zoho Projects UI.
* Full redesign Projects table toolbar.

## Expected Behavior

Користувач бачить Sort button у toolbar над Projects table.

Клік по Sort button відкриває compact popover.

У popover користувач може вибрати sort field і direction.

Sort popover visibility, draft/current sort state і apply/reset behavior керуються shared composable з `shared/sort`.

Після зміни sort state Projects table оновлюється з відповідними sorting params.

Sorting не скидає активні search/filter state.

Pagination має перейти на першу сторінку після зміни sorting, якщо така поведінка вже використовується для search/filter state.

## Technical Notes

* Новий код для generic sorting розміщувати в `resources/js/shared/sort/`.
* `shared/sort` має мати public exports через `resources/js/shared/sort/index.ts`.
* Структуру модуля зробити подібною до `shared/filters`: `types`, `composables`, `ui`, за потреби `lib` або `config`.
* Composable має відповідати за popover visibility, draft/current sort state і handlers для apply/reset/close, якщо такий state потрібен UI.
* Не додавати sorting у `filters[]`.
* Не змінювати backend sorting contract у межах цієї задачі.
* Використати існуючий frontend query/composable state для Projects table sorting.
* Якщо потрібен reusable компонент, розміщувати його тільки якщо він лишається entity-agnostic.
* Якщо існуючий API не підтримує потрібне sort field, зупинитись і уточнити contract перед реалізацією.
* Reference image показує field value `Priority` і direction `Desc`, але для Projects table потрібно використовувати тільки поля, підтримані поточним Projects API.

## Acceptance Criteria

* [ ] Існує shared sort module у `resources/js/shared/sort/`.
* [ ] `shared/sort` експортує reusable types/composable/components через `index.ts`.
* [ ] Shared sort composable керує popover visibility.
* [ ] Shared sort composable керує sort state.
* [ ] Projects table toolbar має Sort button.
* [ ] Sort button відкриває compact popover.
* [ ] Popover містить label `Sort By`.
* [ ] Popover містить select для sort field.
* [ ] Popover містить select для sort direction.
* [ ] Popover має close action.
* [ ] Зміна sort field оновлює Projects table query params.
* [ ] Зміна sort direction оновлює Projects table query params.
* [ ] Sorting працює разом із search.
* [ ] Sorting працює разом із filters.
* [ ] Sorting працює разом із pagination.
* [ ] Sorting не використовує `filters[]` payload.
* [ ] Sorting code не додається в `shared/filters`.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* Потрібно підтвердити exact list of allowed sort fields для Projects table.
* Потрібно підтвердити labels для sort fields у UI.
* Потрібно підтвердити, чи sort changes мають застосовуватись одразу після select change, чи через окрему Apply action.

## Notes For Developer Agent

Не реалізовувати backend sorting changes у цій задачі.

Не додавати sorting у shared filters resolver або будь-який `shared/filters` module.

Перед реалізацією перевірити `shared/filters/composables/use.filter-sidebar.ts` і використати схожий підхід для sort popover/state, не копіюючи зайву filter-specific logic.

Якщо allowed sort fields або apply behavior не підтверджені перед реалізацією, зупинитись і уточнити.
