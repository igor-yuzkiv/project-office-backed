---
type: task
status: draft
---

# 004 - Create TasksPage With Global Tasks List

## Goal

Створити `TasksPage`, яка показує read-only список усіх задач у системі за аналогією з поточною `ProjectsPage`.

## Context

Sprint 3 готує базовий frontend CRUD для основних сутностей. Після переходу backend API до плоскої структури потрібна окрема сторінка глобального списку задач, не прив'язана до конкретного project route.

`TasksPage` має повторювати table/list підхід `ProjectsPage`, адаптований під task entity, але без create, update або delete flows.

## Scope

Що входить у задачу:

* Створити route-level `TasksPage`.
* Показувати paginated список усіх tasks у системі.
* Використати плоский Tasks API endpoint після його появи в Sprint 3 backend API refactor.
* Додати toolbar/list functionality за аналогією з `ProjectsPage`, тільки для read-only перегляду.
* Підтримати search, filters і sort, якщо відповідна shared infrastructure вже доступна на момент реалізації.
* Використати існуючі shared/table/page patterns з `ProjectsPage`.
* Не дублювати generic shared logic, якщо вона вже винесена для ProjectsPage.

## Out Of Scope

Що не входить у задачу:

* Backend changes.
* Створення tasks.
* Оновлення tasks.
* Видалення tasks.
* Детальний task details view.
* Kanban view.
* Calendar view.
* Advanced task workflow UI.
* Pixel-perfect redesign.

## Expected Behavior

Користувач відкриває Tasks page і бачить загальний список задач з усієї системи.

Сторінка поводиться аналогічно до read-only частини `ProjectsPage`: pagination і search/filter/sort controls використовують ті самі підходи та shared components, якщо вони вже реалізовані. Create, update і delete actions не відображаються та не реалізуються.

## Technical Notes

* Реалізувати по аналогії з `resources/js/pages/projects/ProjectsPage.vue`.
* Перед реалізацією перевірити актуальний контракт плоского Tasks API.
* Якщо Tasks API або filter/sort contract ще не готові, не вигадувати payload і зупинитись для уточнення.
* Компоненти, які можуть бути reused між ProjectsPage і TasksPage, виносити тільки якщо це не розширює scope непропорційно.

## Acceptance Criteria

* [ ] Існує route-level `TasksPage`.
* [ ] `TasksPage` показує paginated список усіх tasks.
* [ ] `TasksPage` використовує плоский Tasks API endpoint.
* [ ] UI і поведінка read-only списку побудовані за аналогією з `ProjectsPage`.
* [ ] Search/filter/sort підключені через shared infrastructure, якщо вона доступна.
* [ ] Create/update/delete actions відсутні на `TasksPage`.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* Потрібно підтвердити фінальний контракт плоского Tasks API перед реалізацією.
* Потрібно підтвердити, які task fields мають бути видимі в таблиці першої версії.
* Потрібно підтвердити allowed filter/sort fields для Tasks page, якщо це ще не описано в backend task.

## Notes For Developer Agent

Не розширювати задачу за межі read-only аналога `ProjectsPage`.

Не вигадувати API payload, table columns або business behavior без підтвердженого backend contract.
