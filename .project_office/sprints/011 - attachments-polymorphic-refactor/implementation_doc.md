# Implementation: Attachments Polymorphic Refactor

## Summary

Refactored the `Attachment` entity to follow the Cross-Cutting Entities contract (same pattern as the comments refactor in sprint 010). Replaced manual `entity_type`/`entity_id` columns with Laravel's native polymorphic relationships and moved task-scoped attachment operations out of the universal `Attachment` entity into `Task`-owned modules.

---

## Backend Changes

### Database Migration

**File:** `database/migrations/2026_06_17_194854_convert_attachments_to_polymorphic.php`

- Renamed `entity_type` → `attachable_type`, `entity_id` → `attachable_id`.
- Migrated existing data: `'tasks'` → `App\Domains\Task\Models\TaskModel::class`, `'projects'` → `App\Domains\Project\Models\ProjectModel::class`.
- Dropped old columns, added composite index on `(attachable_type, attachable_id)`.
- Uses full model class names directly — no morph map aliases.

### AttachmentModel

**File:** `app/Domains/Attachment/Models/AttachmentModel.php`

- Updated `$fillable`: replaced `entity_type`/`entity_id` with `attachable_type`/`attachable_id`.
- Added `morphTo('attachable')` relationship.
- Simplified `allowedFilters()` (removed entity filter stubs).

### TaskModel / ProjectModel

**Files:** `app/Domains/Task/Models/TaskModel.php`, `app/Domains/Project/Models/ProjectModel.php`

- Added `attachments(): MorphMany` relationship on both consuming models.

### UploadAttachmentCommand + Handler

**Files:** `app/Domains/Attachment/Actions/UploadAttachment/UploadAttachmentCommand.php`, `UploadAttachmentHandler.php`

- Replaced `?EntityRef $entityRef` with `?Model $attachable` (Illuminate\Database\Eloquent\Model).
- Handler passes `$command->attachable` to the storage service.

### AttachmentStorageService / S3AttachmentStorageService

**Files:** `app/Domains/Attachment/Services/AttachmentStorageService.php`, `S3AttachmentStorageService.php`

- Updated `store()` signature to accept `?Model $attachable`.
- Replaced manual `entity_type`/`entity_id` assignment with `$attachment->attachable()->associate($attachable)`.

### TaskAttachmentsController (new)

**Files:** `app/Http/Controllers/Tasks/TaskAttachmentsController.php`, `app/Http/Requests/Tasks/StoreTaskAttachmentRequest.php`

- `GET /tasks/{task}/attachments` — paginates `$task->attachments()` with `createdBy`/`updatedBy` eager loads.
- `POST /tasks/{task}/attachments` — validates file + role, dispatches `UploadAttachmentCommand` with `attachable=$task`.

### AttachmentsController (simplified)

**File:** `app/Http/Controllers/Attachments/AttachmentsController.php`

- Removed `store()` and `search()` methods (now task-owned). Retained `destroy()` and `content()`.

### AttachmentResource

**File:** `app/Http/Resources/Attachments/AttachmentResource.php`

- Renamed response fields: `entity_type`/`entity_id` → `attachable_type`/`attachable_id`.

### Routes

**File:** `routes/api.php`

- Added route group `tasks/{task}/attachments` → `TaskAttachmentsController` (index, store).
- Removed `POST /attachments` and `POST /attachments/search` from the universal attachments group.

---

## Frontend Changes

### entities/attachment/ (universal — narrowed)

- **`types/attachment.types.ts`**: `entity_type`/`entity_id` → `attachable_type`/`attachable_id`. Removed `ModuleName` dependency.
- **`types/attachment.api.types.ts`**: Removed `IUploadAttachmentInput`, `AttachmentSearchParams` (moved to task scope).
- **`api/attachment.api.ts`**: Only `deleteAttachmentRequest(attachmentId)` remains.
- **`mutations/`**: Only `useDeleteAttachmentMutation` remains.
- **`queries/`**: Empty (no universal queries).
- **`config/attachment-query-keys.config.ts`**: Only `all: ['attachments'] as const`.

### entities/task/ (new attachment slice)

**New files:**

| File | Purpose |
|------|---------|
| `api/task-attachments.api.ts` | `fetchTaskAttachmentsRequest`, `uploadTaskAttachmentRequest` |
| `queries/use.task-attachments.query.ts` | `useTaskAttachmentsQuery(taskId, pagination)` |
| `mutations/use.upload-task-attachment.mutation.ts` | `useUploadTaskAttachmentMutation(taskId)` |
| `composables/use.upload-task-attachment.ts` | `useUploadTaskAttachment(taskId, role?)` — encapsulates mutation + toast |

**Query keys** (`config/task-query-keys.config.ts`):
```ts
TaskAttachmentQueryKey = {
    taskAttachments: (taskId) => ['attachments', 'tasks', taskId],
    taskAttachmentsPaginated: (taskId, pagination?) => [...taskAttachments, pagination],
}
```

Query key starts with `['attachments']` so `useDeleteAttachmentMutation`'s prefix invalidation covers task attachments automatically — no extra invalidation needed.

### widgets/attachments/ (emit-based, entity-agnostic)

**`AttachmentDropZone.vue`:**
- Removed `entityType`/`entityId` props.
- Emits `file-drop: [File]` after local size validation.
- Accepts `isUploading?: boolean` prop for loading state.

**`UploadAttachmentButton.vue`:**
- Removed `entityType`/`entityId` props.
- Emits `file-selected: [File]`.
- Accepts `isUploading?: boolean` prop for loading state.

Deleted `composables/use.attachment-upload.ts` (logic moved to entity-specific composables).

### shared/components/md-editor/MarkdownEditor.vue

- Removed `image_entity_type`, `image_entity_id`, `image_role` props and `uploadAttachmentRequest` import.
- Added `emit('upload-images', files: File[], callback: (urls: string[]) => void)`.
- No longer handles upload internally — consumers resolve uploads via the emitted event.

### widgets/comments/ui/CommentInputForm.vue

- Removed `image_entity_*` props.
- Added `handleImageUpload?: (files: File[], callback: (urls: string[]) => void) => void` prop.
- Passes `:@upload-images="handleImageUpload"` to `MarkdownEditor`.

### Consumer pages updated

| Page | Change |
|------|--------|
| `pages/tasks/details/tabs/TaskAttachmentsPage.vue` | Uses `useTaskAttachmentsQuery` + `useUploadTaskAttachment`; wires `@file-drop`/`@file-selected` to upload |
| `pages/tasks/details/tabs/TaskCommentsPage.vue` | Defines `handleCommentImageUpload` via `uploadTaskAttachmentRequest` with `COMMENTS` role; passes to `CommentInputForm` |
| `pages/tasks/edit/TaskEditPage.vue` | Defines `handleImageUpload` via `uploadTaskAttachmentRequest` with `DESCRIPTION` role; passes to `MarkdownEditor` |
| `pages/projects/edit/ProjectEditPage.vue` | Removed `image_entity_*` props from `MarkdownEditor` (project attachment upload deferred — not in scope) |

---

## Validation

| Check | Result |
|-------|--------|
| `./vendor/bin/pint` | ✅ passed |
| `./vendor/bin/phpstan analyse` | ✅ passed (0 errors) |
| `npm run format` | ✅ passed |
| `npm run lint` | ✅ passed (unrelated docker permission warning) |
| `npm run types:check` | ✅ passed |

---

## Architecture Decisions

### Full class names over morph map aliases

Laravel's morph map allows short aliases (`'tasks'` → `TaskModel::class`), but the project convention is to use full class names directly. The migration converts legacy short strings to full class names. No `Relation::morphMap()` registration was added.

### Consuming entity owns its cross-cutting operations

`TaskAttachmentsController`, `useTaskAttachmentsQuery`, `useUploadTaskAttachmentMutation`, and `useUploadTaskAttachment` all live in the `Task` module. The universal `Attachment` entity has no knowledge of tasks or projects. When project attachment upload is needed, a parallel `ProjectAttachmentsController` + `useUploadProjectAttachment` set will be added to the `Project` module.

### Emit-based upload components

`AttachmentDropZone` and `UploadAttachmentButton` emit files; consumers resolve how to upload. `MarkdownEditor` emits `upload-images` with a callback pattern. This keeps UI components entity-agnostic and avoids prop drilling of entity IDs through multiple layers.

### Query invalidation via prefix

`useDeleteAttachmentMutation` invalidates `['attachments']`. Because `TaskAttachmentQueryKey.taskAttachments(taskId)` starts with `['attachments', 'tasks', ...]`, TanStack Query's prefix matching ensures task attachment lists are invalidated after deletion without any additional wiring.
