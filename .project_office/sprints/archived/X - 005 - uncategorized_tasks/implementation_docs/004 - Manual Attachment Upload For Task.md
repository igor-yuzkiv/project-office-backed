# 004 - Manual Attachment Upload For Task

## What was implemented

Manual attachment upload on the Task Details → Attachments tab. Added upload mutation to `entities/attachment`, created `widgets/attachments/attachment-uploader` widget with two components and a shared composable, and integrated both into `TaskAttachmentsPage`.

## Changed files

**Frontend — entities/attachment**

| File | Change |
|------|--------|
| `entities/attachment/mutations/use.upload-attachment.mutation.ts` | New file — `useUploadAttachmentMutation`, invalidates `AttachmentQueryKey.all` on success |
| `entities/attachment/mutations/index.ts` | New file — barrel export |

**Frontend — widget**

| File | Change |
|------|--------|
| `widgets/attachments/attachment-uploader/composables/use.attachment-upload.ts` | New file — shared composable: file size validation, mutation call, toast feedback |
| `widgets/attachments/attachment-uploader/ui/UploadAttachmentButton.vue` | New file — PrimeVue Button (severity=info, outlined, text, small) + hidden `<input type="file">` + Iconify `material-symbols-light:upload-rounded` |
| `widgets/attachments/attachment-uploader/ui/AttachmentDropZone.vue` | New file — wrapper with VueUse `useDropZone`; overlay visible only during drag |
| `widgets/attachments/attachment-uploader/index.ts` | New file — barrel export |

**Frontend — page**

| File | Change |
|------|--------|
| `pages/tasks/details/tabs/TaskAttachmentsPage.vue` | Added `UploadAttachmentButton` above table; wrapped `AttachmentsTable` in `AttachmentDropZone` |

## Decisions

- `UploadAttachmentButton` uses PrimeVue `Button` + hidden `<input type="file">` instead of PrimeVue `FileUpload` — gives full control over styling and matches `FilterButton` pattern exactly.
- Icon: `material-symbols-light:upload-rounded` via `@iconify/vue` (already installed).
- `AttachmentDropZone` uses VueUse `useDropZone` — handles `dragenter`/`dragleave` counter internally, no flickering.
- Multi-file drop: takes first file only, shows `toast.info`.
- `role` passed as `null` from `TaskAttachmentsPage` — per-entity role assignment is task 003.
- Both components instantiate their own `useAttachmentUpload` — independent pending states, shared invalidation via `AttachmentQueryKey.all`.

## Validation

- `npm run format` — passed
- `npm run lint` (targeted) — passed
- `npm run types:check` — passed
