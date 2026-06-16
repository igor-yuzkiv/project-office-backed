---
type: task
status: todo
---

# 001 - Redesign Record Details Section

## Goal

Привести таб Overview проєктів і задач до layout-у в стилі Zoho CRM: компактний inline label/value формат, поля згруповані у згортувані секції. Реалізувати через нові перевикористовувані компоненти.

## Context

Поточні `ProjectOverviewPage.vue` і `TaskOverviewPage.vue` рендерять поля у grid 2-колонки через ручне розставляння `DisplayField`. Розмітка дублюється, нема секцій, нема inline-режиму. Кожне поле з custom-рендером (avatar, tag, status, link) вимагає окремого блоку розмітки.

Референс: `.project_office/design/references/record_details_zoho_crm_reference.png`.

Існуючі файли:
- `resources/js/shared/components/display/ui/DisplayField.vue`
- `resources/js/shared/components/display/index.ts`
- `resources/js/pages/projects/details/tabs/ProjectOverviewPage.vue`
- `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue`

## Scope

### 1. Refactor `DisplayField`

Файл: `resources/js/shared/components/display/ui/DisplayField.vue`

- Додати prop `inline: boolean` (default `true`). Раніше default був `false` — це поведінкова зміна; перевірити інших споживачів `DisplayField` і за потреби явно проставити `:inline="false"`, щоб не зламати наявні екрани.
- Inline-режим: label і value в одному рядку, vertical center, з gap.
- Stacked-режим: label над value (поточна поведінка).
- Адаптивність: при `inline === true` і ширині екрану `< md` (Tailwind 768px) автоматично переключатись у stacked.
- Зберегти існуючі props: `label`, `value`, `emptyValue`, `format`, слоти `default`, `label`.

### 2. Новий компонент `DisplayFields`

Файл: `resources/js/shared/components/display/ui/DisplayFields.vue` + експорт із `resources/js/shared/components/display/index.ts`.

Тип конфігу (експортувати з public API модуля):

```ts
type DisplayFieldConfig<T> = {
  name: string                                        // обовʼязковий, унікальний ідентифікатор поля та slot-ключ
  label: string                                       // підпис для відображення
  value?: string | ((item: T) => unknown)             // опційний resolver
}
```

Props:

```ts
defineProps<{
  item: T
  fields: DisplayFieldConfig<T>[]
  inline?: boolean                                    // default: true, прокидати у DisplayField
}>()
```

Слоти:

- `field:<name>:value` — override value-частини конкретного поля. Slot props: `{ item, value, field }`.
- `field:<name>:label` — override label-частини. Slot props: `{ field }`.

Поведінка резолвінгу значення для кожного поля:

- якщо `value` — function → виклик `value(item)`;
- якщо `value` — string → `lodashGet(item, value)`;
- якщо `value` не передано → `lodashGet(item, name)`.

Резолвлене значення передавати у `DisplayField` (приводити до string через `String(...)` де потрібно), якщо нема slot-override `field:<name>:value`.

Layout `DisplayFields`: `grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2`. Поля заповнюють grid у порядку конфігу.

TypeScript-generic через `<script setup lang="ts" generic="T">`.

### 3. Переписати `ProjectOverviewPage.vue`

Файл: `resources/js/pages/projects/details/tabs/ProjectOverviewPage.vue`

Розбити на 4 секції через PrimeVue `Panel` (`:toggleable="true"`, `:collapsed="false"`):

1. **General** — Name, Prefix, Status, Tags.
2. **Dates** — Start Date, End Date.
3. **Description** — `MarkdownPreview` (як зараз). Окремий контент у Panel, без `DisplayFields`.
4. **System** — Created By, Created At, Updated By, Updated At, Archived At (умовно), Archived By (умовно).

Custom рендер (Status tag, User avatar, Tag list, дати) — через слоти `field:<name>:value` на сторінці.

### 4. Переписати `TaskOverviewPage.vue`

Файл: `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue`

Розбити на 3–4 секції через `Panel`:

1. **General** — Key, Sequence Number, Status, Priority, Project (RouterLink), Task List, Tags.
2. **Dates** — Start Date, Due Date.
3. **Description** — `MarkdownPreview`. Рендерити секцію тільки якщо тип `TaskResource` має поле `description`. Якщо ні — секцію не додавати.
4. **System** — Created By, Created At, Updated By, Updated At.

Custom рендер через слоти.

## Out Of Scope

- Зміни на backend / DTO / API resources.
- Додавання нових полів у Project або Task.
- Зміни інших таб-сторінок (`TimelinePage`, `TasksPage` тощо).
- Зміни сторінок списку / форми.
- Персистенція стану колапсу секцій.
- Зміна `emptyValue` дефолту (`'N/A'` залишається).

## Expected Behavior

- На широкому екрані (`≥ md`) поля рендеряться у 2-колонковому grid, кожне поле — inline label/value.
- На вузькому екрані (`< md`) — 1 колонка, поля у stacked-режимі.
- Кожна секція — згортувана `Panel`, default expanded. Стан колапсу не персиститься (скидається при перезавантаженні).
- Custom-рендер полів (avatar, tag, status, дата, link) виконується через слоти `field:<name>:value` на сторінках.
- Empty value → `'N/A'` (як у поточному `DisplayField`).
- Конфіг полів — звичайний масив; додавання / переупорядкування полів не потребує зміни розмітки сторінки.

## Technical Notes

- Залежність `lodash.get`: перед використанням перевірити чи lodash (або `lodash-es`) уже встановлено у проєкті. Якщо немає — узгодити з автором перед `npm install`. Не встановлювати самостійно.
- Slot-name syntax у Vue: `<template #[\`field:${name}:value\`]="...">`. Перевірити SFC-компіляцію.
- Тип `DisplayFieldConfig<T>` експортувати з `display/index.ts` для використання на сторінках.
- Layout grid (2 колонки на `md+`, 1 на `< md`) реалізувати на рівні `DisplayFields`, не на сторінках.
- Обгортка `app-content-background p-4` — лишити на рівні сторінки або обгорнути контент `Panel` content, узгодити в процесі.
- Розбиття роботи між саб-агентами:
  - sub-agent A → `DisplayField` refactor;
  - sub-agent B → `DisplayFields` новий компонент (залежить від A);
  - sub-agent C → `ProjectOverviewPage` (залежить від B);
  - sub-agent D → `TaskOverviewPage` (залежить від B, паралельно з C).

## Acceptance Criteria

- [ ] `DisplayField` підтримує prop `inline` із дефолтом `true`.
- [ ] `DisplayField` адаптивно переключається в stacked на ширині `< md`, навіть коли `inline=true`.
- [ ] Існуючі споживачі `DisplayField` (раніше використовували stacked default) — продовжують рендеритись без візуальних регресій (явно проставлений `:inline="false"` де потрібно).
- [ ] `DisplayFields` рендерить поля з конфігу, читає значення через `lodash.get` для string-шляхів.
- [ ] `DisplayFields` підтримує функціональний `value` із сигнатурою `(item: T) => unknown`.
- [ ] `DisplayFields` підтримує слоти `field:<name>:value` і `field:<name>:label` для override label/value кожного поля.
- [ ] Тип `DisplayFieldConfig<T>` експортується з `shared/components/display`.
- [ ] `ProjectOverviewPage` розбита на 4 згортувані секції General / Dates / Description / System через PrimeVue `Panel`.
- [ ] `TaskOverviewPage` розбита на 3–4 секції General / Dates / (Description, якщо є) / System.
- [ ] Секції за дефолтом expanded.
- [ ] `npm run format`, `npm run lint`, `npm run types:check` проходять без помилок.

## Open Questions

(всі вирішені до старту реалізації)

## Notes For Developer Agent

- Backend не чіпати ні за яких умов.
- Не додавати нові поля; працювати тільки з тими що вже є в `ProjectResource` / `TaskResource`.
- Перед `npm install` (lodash) — підтвердити з автором.
- Перед початком реалізації Task Overview — перевірити чи `TaskResource` має поле `description`; від цього залежить наявність секції Description у Task Overview.
- Layout grid — на рівні `DisplayFields`, не сторінки.
- Зберегти існуючий стиль empty value (`'N/A'`).
- Дефолтна зміна `inline` з `false` на `true` — це поведінкова зміна для `DisplayField`. Знайти всіх споживачів через grep і явно проставити `:inline="false"` де потрібна стара поведінка.
