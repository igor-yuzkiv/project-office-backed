---
type: task
status: draft
---

# 002 - Introduce ModuleName Brand Type

## Goal

Замінити магічний literal у frontend коді, який використовується для прив'язки атачментів до сутності (`entity_type`), на typed brand type `ModuleName` і константу `TASK_MODULE_NAME`. Створити мінімальну інфраструктуру, на яку зможуть спиратись наступні задачі (зокрема `001 - Attachments Table`).

## Context

Бекенд зберігає прив'язку атачмента до сутності у полі `attachments.entity_type` як string. Значення приходить через `EntityRef::$module` (`app/Domains/Shared/ValueObjects/EntityRef.php`) і у поточній реалізації відповідає назві таблиці модуля (наприклад, `"tasks"`).

На frontend поточно існує літерал у двох місцях:

- `resources/js/pages/tasks/edit/TaskEditPage.vue:158` — `image_entity_type="tasks"` передається у `MarkdownEditor`;
- `resources/js/entities/attachment/types/attachment.types.ts` — `entity_type: string | null` у `IAttachment` і `entity_type?: string` у `IUploadAttachmentInput` без типового обмеження.

Наступна задача (`001 - Attachments Table`) додає ще один споживач (`TaskAttachmentsPage`), який також потребуватиме `entity_type = "tasks"` для прив'язки запиту до задачі. Без типового обмеження ризик отримати неузгоджені значення зростає з кожним новим споживачем.

Backend у межах цієї задачі не змінюється: на бекенді поки немає enum модулів, значення лишається `string`. Контракт `EntityRef::$module` лишається відкритим string.

## Scope

Що входить у задачу:

**Frontend — shared types:**

- Створити brand type `ModuleName` у `resources/js/shared/types/` поруч із `IEntity`. Реалізувати як `string & { __brand: 'ModuleName' }` (або еквівалентний opaque brand патерн, узгоджений із проєктним стилем).
- Експортувати `ModuleName` через `resources/js/shared/types/index.ts`.
- Розмістити у файлі, узгодженому з існуючою організацією (`module-name.types.ts` поруч з `entity.types.ts`, або інакше, якщо в проєкті вже діє інша угода).

**Frontend — entity-level constant:**

- Додати константу `TASK_MODULE_NAME: ModuleName` у `resources/js/entities/task/config/` (новий файл `task-module.config.ts` або еквівалентне ім'я у стилі вже наявних `task-*.config.ts`).
- Значення: `"tasks"` (відповідає поточному використанню в `TaskEditPage.vue`).
- Зареєструвати reexport через `resources/js/entities/task/config/index.ts`.

**Frontend — refactor існуючих споживачів:**

- `resources/js/pages/tasks/edit/TaskEditPage.vue`: замінити літерал `image_entity_type="tasks"` на binding `:image_entity_type="TASK_MODULE_NAME"`. Якщо props `MarkdownEditor` потребує `string`, оновити тип props на `ModuleName | string` (м'яке розширення без поломки інших споживачів).
- `resources/js/entities/attachment/types/attachment.types.ts`:
  - `IAttachment.entity_type: ModuleName | null` (заміна `string | null`);
  - `IUploadAttachmentInput.entity_type?: ModuleName` (заміна `string`).
- `resources/js/entities/attachment/api/attachment.api.ts`: серіалізація `formData.append('entity_type', input.entity_type)` лишається валідною, бо brand type — це звужений `string` на runtime.

## Out Of Scope

Що не входить у задачу:

- Backend enum модулів або зміни в `EntityRef`/`UploadAttachmentRequest`.
- Зміни в `attachments.entity_type` column type, нові міграції.
- Per-entity attachment **role** config (виноситься в окрему задачу `003`).
- Додавання `PROJECT_MODULE_NAME`, `TASK_LIST_MODULE_NAME` чи інших module-name констант. Додавати на вимогу, коли з'явиться реальний споживач.
- Введення runtime-валідації `ModuleName` (rumtime guards, zod схема для brand).
- Зміни в `MarkdownEditor` поза розширенням типу props.
- Зміни поведінки upload UI чи бекенд-валідації.

## Expected Behavior

- Після задачі весь frontend code, який передає `entity_type` у бекенд або зчитує його з відповіді, працює через типізований `ModuleName`.
- Наступна задача `001 - Attachments Table` використовує `TASK_MODULE_NAME` напряму в filter payload без літералів.
- Runtime значення `entity_type`, що передається у бекенд для задач, лишається `"tasks"` — без змін API контракту.
- Тип `IAttachment.entity_type` фігурує як `ModuleName | null` у компонентах, що використовують `IAttachment`.

## Technical Notes

- Patten brand type:

```ts
export type ModuleName = string & { readonly __brand: 'ModuleName' }
```

  Створення значень — через приведення у місці визначення константи:

```ts
export const TASK_MODULE_NAME = 'tasks' as ModuleName
```

- Не вводити helper-функцію `createModuleName(value)` у межах цієї задачі — простий cast достатній і відповідає принципу мінімальних змін.
- Не змінювати `EntityRef`/backend сигнатури.
- Дотримуватись наявних патернів організації `entities/<entity>/config/`.
- Не встановлювати нові packages.

## Acceptance Criteria

- [ ] `ModuleName` brand type додано в `resources/js/shared/types/` і експортовано через `shared/types/index.ts`.
- [ ] `TASK_MODULE_NAME: ModuleName = 'tasks' as ModuleName` додано в `entities/task/config/` і експортовано через `entities/task/config/index.ts`.
- [ ] `TaskEditPage.vue` використовує `TASK_MODULE_NAME` замість літералу `"tasks"`.
- [ ] `IAttachment.entity_type` має тип `ModuleName | null`.
- [ ] `IUploadAttachmentInput.entity_type` має тип `ModuleName | undefined`.
- [ ] Backend і API контракт не змінено.
- [ ] Frontend validation: `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Open Questions

- Точне ім'я файлу типу (`module-name.types.ts` vs інша назва) — залишити на розсуд агента, дотримуючись існуючої угоди в `shared/types/`.
- Чи розширювати prop `image_entity_type` в `MarkdownEditor` до `ModuleName | string` чи замінити повністю на `ModuleName`. Якщо інші споживачі `MarkdownEditor` передають довільні string — лишити м'яке розширення; інакше можна звузити.

## Notes For Developer Agent

- Цю задачу варто виконати **перед** `001 - Attachments Table`, оскільки `001` спирається на `TASK_MODULE_NAME`.
- Не вводити нові entity-level module-name константи (project, task list) поки немає прямого споживача.
- Не вводити runtime валідацію brand type.
- Не торкатися attachment-role системи — це окрема задача `003`.
