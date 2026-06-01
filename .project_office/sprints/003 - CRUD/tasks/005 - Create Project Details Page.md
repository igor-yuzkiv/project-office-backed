---
type: task
status: draft
---

# 005 - Create Project Details Page

## Goal

Створити read-only `ProjectDetailsPage` для перегляду основної інформації про project та базової tab структури майбутніх project-related секцій.

## Context

У `.project_office/design/concept/project_detail_page.png` є концепт project details page. Його не потрібно повторювати pixel-perfect: частини дизайну не входять у MVP scope цієї задачі.

Ця задача продовжує Sprint 3 frontend CRUD flow для Project, але в межах задачі реалізується тільки сторінка перегляду project details, без edit/delete behavior.

Для завантаження project можна використати існуючий `show` method у `ProjectsController`.

## Scope

Що входить у задачу:

* Створити route-level сторінку деталей project.
* Додати project details section з read-only полями запису project.
* Відобразити поля project:
  * `name`;
  * `prefix`;
  * `created_by`;
  * `updated_by`;
  * `created_at`;
  * `updated_at`.
* Додати біля назви project квадратний avatar з тимчасовим статичним синім кольором, як у дизайн-концепті.
* Не реалізовувати dashboard section з дизайн-концепту.
* Створити shared display field component для відображення label/value.
* Розмістити display field component на рівні `resources/js/shared/components/display/`.
* Рефакторити `DisplayDate`, щоб він підтримував optional `label`.
* Додати tabs section для project details page.
* Додати tabs:
  * `Task Lists`;
  * `Tasks`;
  * `Issues`;
  * `Attachments`;
  * `Documentation`.
* Для кожної tab поки показати placeholder content або `Not implemented`.
* Використати існуючі frontend patterns, PrimeVue і Tailwind.

## Out Of Scope

Що не входить у задачу:

* Pixel-perfect повторення `project_detail_page.png`.
* Dashboard section.
* Реалізація контенту tabs.
* Edit project flow.
* Delete project flow.
* Project activity/business widgets.
* Backend changes.

## Expected Behavior

Користувач відкриває сторінку details конкретного project і бачить основні read-only поля project: `name`, `prefix`, `created_by`, `updated_by`, `created_at`, `updated_at`.

Біля назви project відображається квадратний синій avatar placeholder.

Нижче details section відображається tabs section з вкладками `Task Lists`, `Tasks`, `Issues`, `Attachments`, `Documentation`. Tabs перемикаються, але їхній контент поки є placeholder або `Not implemented`.

## Technical Notes

* Використати дизайн-концепт як орієнтир: `.project_office/design/concept/project_detail_page.png`.
* Для завантаження project використати існуючий `ProjectsController::show` endpoint.
* `ProjectResource` вже містить потрібні поля: `name`, `prefix`, `created_by`, `updated_by`, `created_at`, `updated_at`.
* `DisplayField` має бути generic shared component, не project-specific.
* `DisplayDate` має лишитись backward-compatible: `label` optional.
* Не додавати edit/delete actions на сторінку details у межах цієї задачі.

## Acceptance Criteria

* [ ] Існує route-level сторінка details конкретного project.
* [ ] Сторінка завантажує project через існуючий `ProjectsController::show` flow.
* [ ] Details section відображає read-only поля `name`, `prefix`, `created_by`, `updated_by`, `created_at`, `updated_at`.
* [ ] Біля назви project є квадратний синій avatar placeholder.
* [ ] Dashboard section не реалізована.
* [ ] Існує shared `DisplayField` component.
* [ ] `DisplayDate` підтримує optional `label` і не ламає існуюче використання.
* [ ] Tabs section відображається на сторінці.
* [ ] Tabs: `Task Lists`, `Tasks`, `Issues`, `Attachments`, `Documentation`.
* [ ] Tabs мають placeholder content або `Not implemented`.
* [ ] Edit/delete behavior не реалізований у межах задачі.
* [ ] Backend changes не потрібні в межах задачі.
* [ ] Frontend validation виконана пропорційно зміні: format, lint, type check.

## Open Questions

* Немає критичних відкритих питань для старту реалізації.

## Notes For Developer Agent

Не повторювати дизайн pixel-perfect.

Не реалізовувати dashboard section.

Не реалізовувати edit/delete actions у межах цієї задачі.

Не вносити backend changes у межах цієї задачі, якщо існуючий `ProjectsController::show` flow достатній.
