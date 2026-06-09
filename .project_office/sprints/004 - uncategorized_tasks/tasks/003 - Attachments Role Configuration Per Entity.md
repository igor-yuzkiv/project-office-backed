---
type: task
status: draft
---

# 003 - Attachments Role Configuration Per Entity

## Goal

Замінити довільні string значення `attachment.role` на типизований per-entity конфіг ролей. Кожна сутність, що має атачменти, експортує свій набір допустимих ролей; UI та клієнтський код використовують ці константи замість літералів.

## Context

Зараз `attachments.role` — довільний string на бекенді й на frontend (`IAttachment.role: string | null`, `IUploadAttachmentInput.role?: string`). Літерали з'являються point-of-use, наприклад:

- `resources/js/pages/tasks/edit/TaskEditPage.vue:160` — `image_role="task_description"` передається у `MarkdownEditor`.

З розвитком UI довкола атачментів (upload-діалоги, прев'ю, фільтрація по ролі) ризик розсинхрону значень зростає. Бекенд поки не вводить enum для `role`, але frontend може зафіксувати очікувані значення per-entity у конфігах.

Ця задача стає актуальною коли:

- з'являється UI, який обирає роль (upload dialog, role picker) або
- з'являється потреба фільтрувати атачменти по `role` у списках/таблицях, або
- кількість літералів `role` у коді перевищує 2–3 точки.

До настання цих умов задача лишається відкладеною.

## Scope (preliminary, to be refined when activated)

Що очікується у задачі (буде уточнено перед стартом):

- Brand type `AttachmentRole` (або еквівалент) у `shared/types/` поруч із майбутнім `ModuleName`.
- Per-entity конфіг ролей: для кожної сутності, що має атачменти, окремий файл у `entities/<entity>/config/`, наприклад `task-attachment-roles.config.ts`, з константами на кшталт `TASK_ATTACHMENT_ROLE.DESCRIPTION = 'task_description'`.
- Тип-помічник `EntityAttachmentRoles<Entity>` або pattern, що обмежує вибір ролі певною сутністю.
- Refactor існуючих літералів (`TaskEditPage.vue` і подальших споживачів) на нові константи.
- Оновлення типів `IAttachment.role` і `IUploadAttachmentInput.role` до `AttachmentRole | null` / `AttachmentRole`.
- (Опціонально) introspection helper для рендерингу labels ролей у UI.

## Out Of Scope

- Бекенд enum або зміна column type `attachments.role` (тільки frontend контракт у межах цієї задачі).
- Runtime валідація ролей.
- Динамічна реєстрація ролей з бекенду.

## Open Questions

- Чи обмежувати ролі для апплоадера типом за `entity_type` (cross-field constraint), чи мати плоский union усіх допустимих ролей.
- Чи потрібен label/icon metadata per role, чи достатньо звичайних string-констант.
- Як саме інтегрувати з upload dialog (виноситься у задачу, що додає dialog).

## Notes For Developer Agent

- Задача **відкладена**. Не починати, поки не з'явиться явний споживач (upload dialog, role filter, role picker) або поки `001 - Attachments Table` і `002 - Introduce ModuleName Brand Type` не завершені.
- Перед стартом — переузгодити scope і open questions з користувачем.
- Не розширювати backend без окремого запиту.
