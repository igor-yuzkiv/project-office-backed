---
type: task
status: draft
---

# 004 - Integrate Project Table Filters

## Goal

Додати мінімальну filter UI integration до Projects table для end-to-end перевірки backend і frontend filter infrastructure.

## Context

Backend Projects API має окремий search endpoint на Laravel Scout з підтримкою filters по `name` і `prefix`, а frontend має shared resolver для формування `filters[]`. Ця задача перевіряє повний flow на реальному UI.

Фінальний дизайн filter UI ще не визначений. Ця task повинна залишити місце для дизайн-опису і не змушувати Developer Agent вигадувати фінальний UX.

## Scope

Що входить у задачу:

* Додати filter controls до Projects table.
* Підтримати filters:
  * `name`;
  * `prefix`.
* Підключити shared frontend filters resolver.
* Передавати `filters[]` у Projects search API request.
* Зберегти існуючу pagination і sorting behavior.
* Додати базові loading/empty/error states, якщо вони вже існують у Projects table patterns.

## Out Of Scope

Що не входить у задачу:

* Final visual polish.
* Advanced filter builder.
* Saved filters.
* Filter presets.
* Relationship filters.
* Task List або Task filters.
* Backend changes.
* Route changes.

## Expected Behavior

Користувач може відфільтрувати Projects table по `name` і `prefix`.

Frontend формує backend-compatible `filters[]` payload і передає його у Projects search request.

Pagination і sorting продовжують працювати разом із filters.

Якщо filter value очищено, відповідний filter не має передаватись у request.

## Technical Notes

* Використати shared filters infrastructure з task 003.
* Використати існуючі PrimeVue/Tailwind patterns.
* Не встановлювати нові packages.
* Не створювати складний кастомний UI без погодженого дизайну.
* Якщо дизайн на момент реалізації відсутній, реалізувати тільки після підтвердження minimal UI від автора.

## Acceptance Criteria

* [ ] Projects table має filter controls для `name` і `prefix`.
* [ ] Filters формуються через shared resolver.
* [ ] Projects search API request отримує `filters[]`.
* [ ] Empty filter values не потрапляють у request.
* [ ] Pagination працює разом із filters.
* [ ] Sorting працює разом із filters.
* [ ] UI не додає business logic поза filter state/resolver.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* Остаточний дизайн Projects table filter UI ще не визначений.
* Потрібно узгодити, чи Projects table після додавання filters завжди використовує search endpoint, чи тільки коли активний search/filter state.

## Notes For Developer Agent

Перед реалізацією перевірити, чи автор додав дизайн-опис для Projects table filters.

Якщо дизайн-опису немає, не вигадувати фінальний UX. Зупинитись і нагадати автору, що потрібно або додати дизайн, або явно підтвердити minimal UI для цієї task.
