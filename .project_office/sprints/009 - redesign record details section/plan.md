---
type: sprint
status: in_progress
---

# Sprint 009 - Redesign Record Details Section

## Goal

Привести таб Overview сторінок проєктів і задач до Zoho-CRM-подібного вигляду: компактний inline `label / value` layout, поля згруповані у згортувані секції. Реалізувати через перевикористовувані компоненти, без хардкоду розмітки на сторінках.

Референс: `.project_office/design/references/record_details_zoho_crm_reference.png`.

## Expected Outcome

- `DisplayField` підтримує два режими (inline / stacked) з автоматичним переключенням у stacked на ширині `< md`.
- Новий компонент `DisplayFields` рендерить список полів за конфігом, з підтримкою lodash-path, function-resolver і slot-override.
- Сторінки `ProjectOverviewPage.vue` і `TaskOverviewPage.vue` переписані на нову систему, поля розбиті по згортуваних секціях через PrimeVue `Panel`.

## Scope

- Refactor `DisplayField` (inline + stacked + адаптивність).
- Новий компонент `DisplayFields` з конфігом і slot-override.
- Інтеграція PrimeVue `Panel` для секцій на сторінках Overview.
- Переписати `ProjectOverviewPage.vue` і `TaskOverviewPage.vue`.

## Out Of Scope

- Будь-які зміни на backend.
- Зміни DTO / API resources.
- Зміни інших сторінок (тільки Overview-таби).
- Додавання нових полів project / task.

## Tasks

### 001 - Redesign Record Details Section

Статус: todo

Удосконалення `DisplayField`, новий `DisplayFields`, інтеграція у Project і Task Overview зі згортуваними секціями.

## Dependencies

* PrimeVue `Panel` (уже встановлено).
* `lodash.get` — перед використанням перевірити чи lodash (або lodash-es) вже встановлено у проєкті; якщо ні — узгодити з автором перед `npm install`.

## Risks

* lodash може бути не встановлено — потенційно потрібне встановлення пакета.
* Slot-name синтаксис `field:<name>:value` у Vue валідний, але потребує екранування у компонентах-споживачах (`<template #[\`field:${name}:value\`]>`).

## Open Questions

(всі вирішені до старту реалізації)

## Notes For Developer Agent

- Задачу декомпозувати по саб-агентах: (1) `DisplayField` refactor, (2) `DisplayFields` новий компонент, (3) Project Overview, (4) Task Overview. (1) і (2) виконувати послідовно (2 залежить від 1); (3) і (4) можна паралельно після (2).
- Backend не чіпати ні за яких умов.
- Layout 2-колонок робити на рівні `DisplayFields`, не на сторінці.
