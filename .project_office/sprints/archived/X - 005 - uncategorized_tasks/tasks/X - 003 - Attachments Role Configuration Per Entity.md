---
type: task
status: draft
---

# 003 - Attachments Role Configuration For Task Entity

## Goal

Замінити довільні string значення `attachment.role` на типизований brand type і per-entity конфіг ролей. У межах цієї задачі реалізувати інфраструктуру + конкретний набір ролей для Task. Project/TaskList ролі залишаються поза скоупом і додаватимуться окремими задачами коли з'являться відповідні UI-споживачі.

## Context

Поточно `attachments.role` — довільний string як на backend (`AttachmentModel.role`, `UploadAttachmentRequest::rules` приймає `nullable string`), так і на frontend (`IAttachment.role: string | null`, `IUploadAttachmentInput.role?: string`). Бекенд лишається неструктурованим — типізація вводиться лише на frontend.

Існуючий споживач literal'у:

- `resources/js/pages/tasks/edit/TaskEditPage.vue:160` — `image_role="task_description"` (зображення вставлене через MD editor у task description).

Запланований споживач:

- `004 - Manual Attachment Upload For Task` — manual upload з вкладки `TaskAttachmentsPage`. Очікувана роль: `task_attachment`. Поточний драфт 004 передає `role={null}`; після цієї задачі сторінка зможе передавати типізовану константу.

Цю задачу не блокує `004`: 004 може запуститись з `role=null`, а після завершення 003 — оновити TaskAttachmentsPage на типізовану константу. Якщо 003 завершиться раніше — 004 одразу використовує конкретне значення.

`002 - Introduce ModuleName Brand Type` уже завершено. Brand type pattern (`string & { __brand: ... }`) узятий звідти і повторюється для `AttachmentRole`.

## Dependencies

- `002 - Introduce ModuleName Brand Type` — патерн brand type повторюється тут (інформаційна, не блокуюча — `002` уже завершено).

## Scope

**Frontend — shared types:**

- Додати brand type `AttachmentRole` у `resources/js/shared/types/` поруч із `ModuleName` (`attachment-role.types.ts` або еквівалентне ім'я узгоджене з існуючою організацією).
- Експортувати `AttachmentRole` через `resources/js/shared/types/index.ts`.
- Реалізація:

```ts
export type AttachmentRole = string & { readonly __brand: 'AttachmentRole' }
```

  Без cross-field generic обмежень за entity. Контроль допустимих значень — на рівні per-entity конфігів і прийняття агентом під час review.

**Frontend — entity-level constants:**

- Додати `resources/js/entities/task/config/task-attachment-roles.config.ts` за патерном `task-module.config.ts` (з `entities/task_list`, який має `task-list-module.config.ts`).
- Структура:

```ts
import type { AttachmentRole } from '@/shared/types'

export const TASK_ATTACHMENT_ROLE = {
    DESCRIPTION: 'task_description' as AttachmentRole,
    ATTACHMENT:  'task_attachment' as AttachmentRole,
} as const
```

- Реекспорт через `entities/task/config/index.ts`.

**Frontend — entities/attachment types:**

- `resources/js/entities/attachment/types/attachment.types.ts`:
  - `IAttachment.role: AttachmentRole | null` (заміна `string | null`);
  - `IUploadAttachmentInput.role?: AttachmentRole` (заміна `string`).

**Frontend — refactor існуючих споживачів:**

- `resources/js/pages/tasks/edit/TaskEditPage.vue:160` — замінити літерал `image_role="task_description"` на binding `:image_role="TASK_ATTACHMENT_ROLE.DESCRIPTION"`.
- `resources/js/shared/components/md-editor/ui/MarkdownEditor.vue` — оновити тип props:
  - `image_role?: AttachmentRole` (заміна `image_role?: string`).
  - Якщо інші споживачі MD editor'а передають довільні string у `image_role` — застосувати м'яке розширення `AttachmentRole | string`. Перевірити grep'ом перед звуженням типу.

## Out Of Scope

- Project, TaskList або інші entity attachment roles — додавати окремими задачами під реальних споживачів.
- Backend enum або column-level constraint для `attachments.role`.
- Runtime валідація `AttachmentRole`.
- UI metadata: label, icon, description (rich role config). Цю opcію відкладено до моменту, коли з'явиться role picker / role filter UI.
- Cross-field generic constraint `EntityRoles<Entity>` (вибрано plain brand type).
- Динамічна реєстрація ролей з backend.
- Зміни в `004 - Manual Attachment Upload For Task` поза наведеним нижче рефактором TaskAttachmentsPage.

## Expected Behavior

- Тип `IAttachment.role` фігурує як `AttachmentRole | null` у всіх компонентах, що використовують `IAttachment`.
- Тип `IUploadAttachmentInput.role` фігурує як `AttachmentRole | undefined`.
- Жоден код у проекті не передає `role` як literal string у upload/payload — використовує `TASK_ATTACHMENT_ROLE.*`.
- `TaskEditPage.vue` передає `TASK_ATTACHMENT_ROLE.DESCRIPTION` у `MarkdownEditor`.
- API контракт із backend не змінено: на проводі `role` залишається string ("task_description" / "task_attachment").
- TypeScript блокує спробу передати випадковий string в API, що очікує `AttachmentRole`.

## Technical Notes

- Patten повторює `ModuleName` з 002: brand type через intersection із phantom field; значення створюються `as AttachmentRole` у місці визначення константи.
- Не вводити `createAttachmentRole(value)` helper'а — простий cast достатній.
- `TASK_ATTACHMENT_ROLE` оголошений `as const` для того щоб ключі (`DESCRIPTION`, `ATTACHMENT`) були readonly literal types.
- Перед звуженням типу `MarkdownEditor.image_role` пройтись grep'ом по `image_role=` у `resources/js`. Якщо знаходиться лише `TaskEditPage.vue` — звузити до `AttachmentRole`. Якщо є інші довільні споживачі — лишити м'яке розширення.
- Frontend validation: `npm run format`, `npm run lint`, `npm run types:check`.
- Не встановлювати нові packages.
- Не змінювати backend.

## Coordination With Task 004

Якщо `003` завершено раніше за `004`:

- `004` у `TaskAttachmentsPage` передає `TASK_ATTACHMENT_ROLE.ATTACHMENT` замість `null`.
- Це повинно бути зафіксовано через update 004-го task-файлу (приймається в межах 004, не 003).

Якщо `004` завершено раніше за `003`:

- Після 003 — окремий невеликий refactor у TaskAttachmentsPage: замінити `role={null}` на `role={TASK_ATTACHMENT_ROLE.ATTACHMENT}`. Цей refactor входить у scope 003.

## Acceptance Criteria

- [ ] `AttachmentRole` brand type додано в `resources/js/shared/types/` і експортовано через `shared/types/index.ts`.
- [ ] `TASK_ATTACHMENT_ROLE` константа з полями `DESCRIPTION` і `ATTACHMENT` додана в `entities/task/config/` і експортована через `entities/task/config/index.ts`.
- [ ] `IAttachment.role` має тип `AttachmentRole | null`.
- [ ] `IUploadAttachmentInput.role` має тип `AttachmentRole | undefined`.
- [ ] `TaskEditPage.vue` використовує `TASK_ATTACHMENT_ROLE.DESCRIPTION` замість літералу.
- [ ] `MarkdownEditor.vue` prop `image_role` типізовано як `AttachmentRole` (або `AttachmentRole | string`, якщо є інші споживачі з довільним string).
- [ ] У `resources/js` немає літералу `'task_description'` поза `task-attachment-roles.config.ts`.
- [ ] У `resources/js` немає літералу `'task_attachment'` (для контролю після можливого 004 refactor).
- [ ] Якщо `004` уже завершено — `TaskAttachmentsPage` передає `TASK_ATTACHMENT_ROLE.ATTACHMENT` замість `null`.
- [ ] Backend і API контракт не змінено.
- [ ] Frontend validation: `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Open Questions

- Точне ім'я файлу типу (`attachment-role.types.ts` vs інша назва) — на розсуд агента, дотримуючись угоди в `shared/types/`.
- Чи лишати `AttachmentRole | string` у `MarkdownEditor.image_role` (м'яке розширення) — рішення на основі grep'у існуючих споживачів.

## Notes For Developer Agent

- Не додавати `PROJECT_ATTACHMENT_ROLE` / `TASK_LIST_ATTACHMENT_ROLE` у межах цієї задачі — лише коли з'являться реальні споживачі.
- Не вводити UI metadata (label/icon/description) для ролей — це окрема задача якщо колись знадобиться role picker.
- Не торкатися backend `AttachmentModel`/`UploadAttachmentRequest`/`AttachmentResource`.
- Не змінювати behavior MD editor поза типом props.
- Якщо 004 уже виконано на момент старту цієї задачі — додатково оновити TaskAttachmentsPage щоб передавала `TASK_ATTACHMENT_ROLE.ATTACHMENT`.
