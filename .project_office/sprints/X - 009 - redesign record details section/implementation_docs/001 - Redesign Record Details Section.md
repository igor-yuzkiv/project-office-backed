# 001 - Redesign Record Details Section

## Що реалізовано

Перехід Overview-табів проєктів і задач на Zoho-CRM-подібний layout: inline label/value поля, згруповані у згортувані PrimeVue Panel секції. Реалізовано через перевикористовувані компоненти з config-driven підходом.

## Змінені файли

### Нові файли

- `resources/js/shared/utils/date.util.ts` — утиліти форматування дат:
  - `formatDate(date, fmt?)` — дата без часу (дефолт `'MMM d, yyyy'`)
  - `formatDateTime(date)` — дата з часом (`'MMM d, yyyy HH:mm'`)

- `resources/js/shared/components/display/ui/DisplayFields.vue` — новий generic компонент:
  - Generic `<T extends object>`, props: `item`, `fields`, `inline` (дефолт `true`)
  - `DisplayFieldConfig<T>` тип: `{ name, label, value? }`
  - Resolver: function → `value(item)`, string → `lodashGet(item, path)`, undefined → `lodashGet(item, name)`
  - `computed resolvedFields` — pre-resolved масив `{ field, rawValue, displayValue }`, не викликає методи в template
  - Grid layout: `grid-cols-1 md:grid-cols-2`
  - Слоти `field:<name>:value` і `field:<name>:label` для override кожного поля

### Змінені файли

- `resources/js/shared/components/display/ui/DisplayField.vue`:
  - `inline` дефолт: `false → true`
  - Адаптивні класи: `inline=true` → stacked на `< md`, inline (flex-row) на `md+`

- `resources/js/shared/components/display/ui/DisplayDate.vue`:
  - Ті самі зміни дефолту та адаптивних класів, що й у `DisplayField`

- `resources/js/shared/components/display/index.ts`:
  - Додано експорти `DisplayFields` і `DisplayFieldConfig`

- `resources/js/pages/projects/details/tabs/ProjectOverviewPage.vue`:
  - Переписана: 4 Panel секції — General / Dates / System / Description
  - General: Name, Prefix, Status (slot), Tags (slot)
  - Dates: Start Date, End Date (function resolvers через `formatDate`)
  - System: Created By (slot), Created At, Updated By (slot), Updated At, Archived At / Archived By (умовно через computed)
  - Description: MarkdownPreview в кінці

- `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue`:
  - Переписана: 3 Panel секції — General / Dates / System
  - General: Key, Sequence Number, Status (slot), Priority (slot), Project (RouterLink slot), Task List, Tags (slot)
  - Dates: Start Date, Due Date (function resolvers)
  - System: Created By (slot), Created At, Updated By (slot), Updated At
  - Description секцію не додавали — є окрема вкладка

## Важливі рішення та компроміси

- **Дати через function resolver**, не через слоти — `value: (p) => formatDate(p.start_date)` повертає відформатований рядок; slot-override залишено для компонентних рендерів (Tag, Avatar, RouterLink)
- **`DisplayDate` також оновлено** — синхронізовано дефолт `inline` і адаптивні класи разом із `DisplayField`
- **Conditional Archived полів у Project** — `systemFields` є `computed`, що реактивно включає `archived_at` / `archived_by` тільки якщо вони не null
- **`computed resolvedFields`** у `DisplayFields` — значення резолвляться один раз, не при кожному render pass template
- **`TaskDetailsPage.vue` не змінювався** — вже мав явний `inline` prop, зміна дефолту не впливає

## Перевірки

- `npm run format` ✅
- `npx eslint` (на змінених файлах) ✅
- `npm run types:check` ✅
