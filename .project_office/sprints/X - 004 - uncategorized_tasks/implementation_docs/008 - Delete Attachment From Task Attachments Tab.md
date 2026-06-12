# 008 - Delete Attachment From Task Attachments Tab

## What was implemented

Added Delete (and Download) actions to the Task Attachments tab via a context menu. Download column removed from the table and moved into the menu.

## Changed files

### Backend

| File | Change |
|------|--------|
| `app/Domains/Attachment/Actions/DeleteAttachment/DeleteAttachmentHandler.php` | New — calls `storage->delete()` then `attachment->delete()` |
| `app/Http/Controllers/Attachments/AttachmentsController.php` | Added `DeleteAttachmentHandler` injection and `destroy` method |
| `routes/api.php` | Added `DELETE attachments/{attachment}` with `auth:sanctum` |

### Frontend

| File | Change |
|------|--------|
| `resources/js/entities/attachment/api/attachment.api.ts` | Added `deleteAttachmentRequest` |
| `resources/js/entities/attachment/mutations/use.delete-attachment.mutation.ts` | New — `mutateWithConfirm`, invalidates `AttachmentQueryKey.all` |
| `resources/js/entities/attachment/mutations/index.ts` | Added `useDeleteAttachmentMutation` reexport |
| `resources/js/widgets/attachments/views/table/ui/AttachmentsTable.vue` | Removed Download column |
| `resources/js/pages/tasks/details/tabs/TaskAttachmentsPage.vue` | Added context menu (Download + Delete) via `#actions` slot and `Menu` popup |

## Decisions

- Download column removed from `AttachmentsTable` and moved into the context menu — cleaner table, consistent with the actions pattern.
- `#actions` slot used instead of the `withRowActions` prop described in the original spec — consistent with the established pattern across all other tables.
- Download uses `window.open(url, '_blank', 'noopener,noreferrer')` in the menu `command`.
- No success toast after delete — consistent with `useDeleteProjectMutation` behavior.

## Validation

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — passed
- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
