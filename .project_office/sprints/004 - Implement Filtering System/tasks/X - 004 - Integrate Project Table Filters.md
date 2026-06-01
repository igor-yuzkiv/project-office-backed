---
type: task
status: draft
---

# 004 - Integrate Project Table Filters

## Goal

Додати Projects table search/filter toolbar та filter sidebar integration для end-to-end перевірки backend і frontend filter infrastructure.

## Context

Backend Projects API має окремий search endpoint на Laravel Scout з підтримкою filters по `name` і `prefix`, а frontend має shared resolver для формування `filters[]`. Ця задача перевіряє повний flow на реальному UI.

UI має враховувати два дизайн-орієнтири:

* `.project_office/design/concept/projects_table_page.png` — toolbar над таблицею з search field і кнопкою Filters;
* `.project_office/design/references/zoho_project_filters_ui_reference.png` — full-height filter sidebar/popup pattern.

## Scope

Що входить у задачу:

* Додати toolbar section над Projects table згідно з `projects_table_page`.
* Додати search input у toolbar.
* Додати Filters button у toolbar.
* Відкривати full-height filter sidebar/popup через Filters button.
* Використати shared filter sidebar components з task 003.
* Підтримати filters:
  * `name`;
  * `prefix`.
* Підключити shared frontend filters resolver.
* Передавати `filters[]` у Projects search API request.
* Зберегти існуючу pagination і sorting behavior.
* Додати базові loading/empty/error states, якщо вони вже існують у Projects table patterns.

## Out Of Scope

Що не входить у задачу:

* Pixel-perfect copy of Zoho Projects UI.
* Final visual polish beyond MVP sidebar integration.
* Advanced filter builder.
* Saved filters.
* Filter presets.
* Relationship filters.
* Task List або Task filters.
* Backend changes.
* Route changes.

## Expected Behavior

Користувач бачить над Projects table toolbar із search field та кнопкою Filters.

Клік по Filters відкриває full-height filter sidebar/popup.

Користувач може відфільтрувати Projects table по `name` і `prefix` через sidebar.

Frontend формує backend-compatible `filters[]` payload і передає його у Projects search request.

Pagination і sorting продовжують працювати разом із filters.

Якщо filter value очищено, відповідний filter не має передаватись у request.

Reset у sidebar очищає filter state.

Cancel закриває sidebar без застосування незбережених змін, якщо UI реалізує draft state.

## Technical Notes

* Використати shared filters infrastructure з task 003.
* Використати shared filter sidebar components з task 003.
* Використати існуючі PrimeVue/Tailwind patterns.
* Не встановлювати нові packages.
* Не створювати складний кастомний UI поза описаним sidebar pattern.
* `projects_table_page` визначає наявність toolbar над таблицею.
* Zoho reference визначає interaction pattern sidebar, але не є вимогою pixel-perfect копіювання.

## Acceptance Criteria

* [ ] Projects table має toolbar над таблицею.
* [ ] Toolbar містить search field.
* [ ] Toolbar містить Filters button.
* [ ] Filters button відкриває full-height filter sidebar/popup.
* [ ] Sidebar використовує shared filter components.
* [ ] Sidebar має filter controls для `name` і `prefix`.
* [ ] Filters формуються через shared resolver.
* [ ] Projects search API request отримує `filters[]`.
* [ ] Reset очищає filters.
* [ ] Empty filter values не потрапляють у request.
* [ ] Pagination працює разом із filters.
* [ ] Sorting працює разом із filters.
* [ ] UI не додає business logic поза filter state/resolver.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* Потрібно узгодити, чи Projects table після додавання filters завжди використовує search endpoint, чи тільки коли активний search/filter state.
* Потрібно узгодити exact behavior для Cancel: закривати sidebar без змін через draft state чи просто закривати вже синхронізований state.

## Notes For Developer Agent

Не вигадувати advanced filter UX поза описаним sidebar pattern.

Якщо залишаються незакриті open questions щодо search endpoint usage або Cancel behavior, зупинитись і уточнити перед реалізацією.
