# Plan: Attachments — Polymorphic Refactoring & Cross-Cutting Contract  
  
**Status:** pending approval  
  
## Overview  
  
Refactor `Attachment` to follow the Cross-Cutting Entities contract (same pattern as the comments refactor):  
  
- **Backend:** replace manual `entity_type/entity_id` columns with Laravel's native `morphTo/morphMany`  
- **Backend:** add `TaskAttachmentsController` — consuming entity owns its attachment operations  
- **Frontend:** move task-scoped ops (`fetchTaskAttachments`, `uploadTaskAttachment`) to `entities/task/`  
- **Frontend:** add `useUploadTaskAttachment(taskId)` composable pattern  
- **Frontend:** make `AttachmentDropZone` and `UploadAttachmentButton` emit-based (no entity knowledge)  
- **Frontend:** decouple `MarkdownEditor` from `uploadAttachmentRequest`  
  
---  
  
## Requirements Summary  
  
1. `AttachmentModel` uses `morphTo('attachable')` — columns `attachable_type`/`attachable_id` instead of `entity_type`/`entity_id`  
2. `TaskModel` and `ProjectModel` expose `attachments(): MorphMany`  
3. `GET /tasks/{task}/attachments` and `POST /tasks/{task}/attachments` replace entity-filtered search + generic upload  
4. `AttachmentsController` retains only `destroy` and `content` (entity-agnostic ops by attachment ID)  
5. `entities/attachment/` has no task-scoped API calls, queries, or mutations  
6. `AttachmentDropZone` and `UploadAttachmentButton` emit files; no `entityType/entityId/role` props  
7. `useUploadTaskAttachment(taskId)` in `entities/task/composables/` encapsulates upload + toast + mutation  
8. `MarkdownEditor` accepts `imageUploadFn?: (file: File) => Promise<string>` instead of entity props  
  
---  
  
## Acceptance Criteria  
  
### Backend  
- [ ] `attachments` table has `attachable_type` and `attachable_id`; `entity_type` and `entity_id` dropped  
- [ ] `AttachmentModel::attachable()` returns `MorphTo`  
- [ ] `TaskModel::attachments()` returns `MorphMany<AttachmentModel>`  
- [ ] `ProjectModel::attachments()` returns `MorphMany<AttachmentModel>`  
- [ ] `GET /tasks/{task}/attachments` returns paginated `AttachmentResource` collection  
- [ ] `POST /tasks/{task}/attachments` accepts `file` + optional `role`, returns 201 `AttachmentResource`  
- [ ] `DELETE /attachments/{id}` and `GET /attachments/{id}/content` unchanged  
- [ ] `POST /attachments` and `POST /attachments/search` routes removed  
- [ ] `./vendor/bin/pint` passes  
- [ ] `./vendor/bin/phpstan analyse` passes at level 5  
  
### Frontend  
- [ ] `IAttachment` has `attachable_type: string | null` and `attachable_id: string | null`  
- [ ] `entities/attachment/` exports only: `IAttachment`, `deleteAttachmentRequest`, `useDeleteAttachmentMutation`, `AttachmentQueryKey`  
- [ ] `useTaskAttachmentsQuery(taskId)` exists in `entities/task/queries/`  
- [ ] `useUploadTaskAttachmentMutation(taskId)` exists in `entities/task/mutations/`  
- [ ] `useUploadTaskAttachment(taskId)` exists in `entities/task/composables/`, returns `{ upload, isPending }`  
- [ ] `AttachmentDropZone` props: `isUploading?: boolean`, `maxFileSizeBytes?: number`; emits `file-drop: [File]`  
- [ ] `UploadAttachmentButton` props: `isUploading?: boolean`; emits `file-selected: [File]`  
- [ ] `TaskAttachmentsPage.vue` uses `useTaskAttachmentsQuery` + `useUploadTaskAttachment`, no entity props on upload components  
- [ ] `MarkdownEditor` uses `imageUploadFn` prop, no direct `uploadAttachmentRequest` import  
- [ ] `npm run types:check` passes  
  
---  
  
## Implementation Steps  
  
### Phase 1 — Backend: Database & Model  
  
#### 1.1 — Migration: convert to polymorphic  
**File:** `database/migrations/{timestamp}_convert_attachments_to_polymorphic.php`  
  
- Add `attachable_type` (string, nullable) and `attachable_id` (string, nullable) after existing columns  
- Raw SQL copy: `UPDATE attachments SET attachable_type = entity_type, attachable_id = entity_id`  
- Drop `entity_type`, `entity_id`  
- Add composite index on `[attachable_type, attachable_id]`  
- `down()`: reverse — add entity_type/entity_id back, copy data, drop attachable_* columns  
  
> **Note:** morph map (step 1.2) must be registered before this migration runs so that stored values  
> `'tasks'`/`'projects'` resolve to the correct models via `morphTo`.  
  
#### 1.2 — Register morph map  
**File:** `app/Providers/AppServiceProvider.php`  
  
Add in `boot()`:  
```php  
Relation::morphMap([  
    'tasks'    => TaskModel::class,  
    'projects' => ProjectModel::class,  
]);  
```  
  
This maps existing stored values directly — no value transformation needed in migration.  
  
#### 1.3 — AttachmentModel  
**File:** `app/Domains/Attachment/Models/AttachmentModel.php`  
  
- Replace `entity_type`, `entity_id` in `#[Fillable]` with `attachable_type`, `attachable_id`  
- Add relationship:  
  ```php  
  public function attachable(): MorphTo  
  {      return $this->morphTo();  }  
  ```- Update `allowedFilters()`: remove or rename entity_type/entity_id filter definitions  
  
#### 1.4 — TaskModel  
**File:** `app/Domains/Task/Models/TaskModel.php`  
  
```php  
public function attachments(): MorphMany  
{  
    return $this->morphMany(AttachmentModel::class, 'attachable');}  
```  
  
#### 1.5 — ProjectModel  
**File:** `app/Domains/Project/Models/ProjectModel.php`  
  
Same `attachments(): MorphMany` as TaskModel.  
  
---  
  
### Phase 2 — Backend: Actions & Services  
  
#### 2.1 — UploadAttachmentCommand  
**File:** `app/Domains/Attachment/Actions/UploadAttachment/UploadAttachmentCommand.php`  
  
Replace `?EntityRef $entityRef` with `?Model $attachable` (`Illuminate\Database\Eloquent\Model`).  
  
#### 2.2 — AttachmentStorageService interface  
**File:** `app/Domains/Attachment/Services/AttachmentStorageService.php`  
  
Update `store()` signature:  
```php  
public function store(  
    UploadedFile $file,    ?Model $attachable = null,    ?string $role = null): AttachmentModel;  
```  
  
#### 2.3 — S3AttachmentStorageService  
**File:** `app/Domains/Attachment/Services/S3AttachmentStorageService.php`  
  
- Update `store()` signature to match interface  
- Replace `'entity_type' => $entityRef?->module, 'entity_id' => $entityRef?->id` in the model constructor with:  
  ```php  
  if ($attachable !== null) {  
      $attachment->attachable()->associate($attachable);  }  
  ```  Call this after `new AttachmentModel([...])`, before `$attachment->save()`.  
  
#### 2.4 — UploadAttachmentHandler  
**File:** `app/Domains/Attachment/Actions/UploadAttachment/UploadAttachmentHandler.php`  
  
Pass `$command->attachable` instead of `$command->entityRef` to the storage service.  
  
---  
  
### Phase 3 — Backend: HTTP Layer  
  
#### 3.1 — StoreTaskAttachmentRequest (new)  
**File:** `app/Http/Requests/Tasks/StoreTaskAttachmentRequest.php`  
  
```php  
rules(): ['file' => 'required|file|max:25600', 'role' => 'nullable|string']  
```  
No `entity_type`/`entity_id` — those come from the route parameter.  
  
#### 3.2 — TaskAttachmentsController (new)  
**File:** `app/Http/Controllers/Tasks/TaskAttachmentsController.php`  
  
```php  
index(TaskModel $task): AnonymousResourceCollection  
    → $task->attachments()  
          ->with(['createdBy', 'updatedBy'])  
          ->orderBy('created_at', 'desc')  
          ->paginate(50)  
    → AttachmentResource::collection(...)  
  
store(StoreTaskAttachmentRequest $request, TaskModel $task): JsonResponse  
    → new UploadAttachmentCommand(          file: $request->file('file'),  
          attachable: $task,          role: $request->validated('role'),  
      )    → UploadAttachmentHandler::handle($command)  
    → 201 AttachmentResource  
```  
  
Pattern mirrors `TaskCommentsController`.  
  
#### 3.3 — AttachmentsController (update)  
**File:** `app/Http/Controllers/Attachments/AttachmentsController.php`  
  
- Remove `store()` and `search()` methods  
- Remove `UploadAttachmentHandler` injection and `SearchRequest` import  
- Keep `destroy()` and `content()` unchanged  
  
#### 3.4 — AttachmentResource (update)  
**File:** `app/Http/Resources/Attachments/AttachmentResource.php`  
  
Replace:  
```php  
'entity_type' => $this->entity_type,  
'entity_id'   => $this->entity_id,  
```  
With:  
```php  
'attachable_type' => $this->attachable_type,  
'attachable_id'   => $this->attachable_id,  
```  
  
#### 3.5 — Routes  
**File:** `routes/api.php`  
  
Add task attachments group (alongside existing Task Comments group):  
```php  
Route::group([  
    'prefix'     => 'tasks/{task}/attachments',    'as'         => 'tasks.attachments.',    'middleware' => ['auth:sanctum'],    'controller' => TaskAttachmentsController::class,  
], function () {  
    Route::get('/', 'index')->name('index');  
    Route::post('/', 'store')->name('store');  
});  
```  
  
Remove from Attachments group:  
- `Route::post('search', 'search')`  
- `Route::post('/', 'store')`  
  
---  
  
### Phase 4 — Frontend: entities/attachment/  
  
#### 4.1 — IAttachment type  
**File:** `resources/js/entities/attachment/types/attachment.types.ts`  
  
```ts  
// remove:  
entity_type: ModuleName | null  
entity_id: string | null  
  
// add:  
attachable_type: string | null  
attachable_id: string | null  
```  
  
Also remove `IUploadAttachmentInput` and `AttachmentSearchParams` types (from `attachment.api.types.ts`).  
  
#### 4.2 — attachment.api.ts (update)  
**File:** `resources/js/entities/attachment/api/attachment.api.ts`  
  
Remove `uploadAttachmentRequest` and `searchAttachmentsRequest`. Keep only `deleteAttachmentRequest`.  
  
#### 4.3 — Remove upload mutation  
**File:** `resources/js/entities/attachment/mutations/use.upload-attachment.mutation.ts` → **delete**  
  
#### 4.4 — Remove search query  
**File:** `resources/js/entities/attachment/queries/use.attachments-search.query.ts` → **delete**  
  
#### 4.5 — AttachmentQueryKey (update)  
**File:** `resources/js/entities/attachment/config/attachment-query-keys.config.ts`  
  
Remove `search` key. Keep:  
```ts  
export const AttachmentQueryKey = {  
    all: ['attachments'] as const,}  
```  
  
> `useDeleteAttachmentMutation` invalidates `['attachments']`. Because task attachment query keys  
> will be structured as `['attachments', 'tasks', taskId]`, TanStack Query's prefix matching  
> automatically covers them — no extra invalidation needed.  
  
#### 4.6 — Update index.ts barrel files  
`api/index.ts`, `mutations/index.ts`, `queries/index.ts`, `types/index.ts` — remove deleted exports.  
  
---  
  
### Phase 5 — Frontend: entities/task/  
  
#### 5.1 — task-attachments.api.ts (new)  
**File:** `resources/js/entities/task/api/task-attachments.api.ts`  
  
```ts  
fetchTaskAttachmentsRequest(taskId: string, page?: number, perPage?: number)  
    → GET /tasks/{taskId}/attachments  
uploadTaskAttachmentRequest(taskId: string, file: File, role?: string)  
    → POST /tasks/{taskId}/attachments  (FormData: file, role?)    → returns { data: IAttachment }  
```  
  
#### 5.2 — TaskAttachmentQueryKey (update task-query-keys.config.ts)  
**File:** `resources/js/entities/task/config/task-query-keys.config.ts`  
  
Add:  
```ts  
export const TaskAttachmentQueryKey = {  
    taskAttachments: (taskId: MaybeRefOrGetter<string>) =>        ['attachments', 'tasks', taskId] as const,    taskAttachmentsPaginated: (taskId: MaybeRefOrGetter<string>, pagination?: MaybeRefOrGetter<PagingParams>) =>        [...TaskAttachmentQueryKey.taskAttachments(taskId), pagination] as const,}  
```  
  
Key starts with `'attachments'` → prefix-invalidated by `useDeleteAttachmentMutation`.  
  
#### 5.3 — useTaskAttachmentsQuery (new)  
**File:** `resources/js/entities/task/queries/use.task-attachments.query.ts`  
  
Mirrors `useTaskCommentsQuery` pattern. Accepts `taskId` + optional pagination, queries  
`TaskAttachmentQueryKey.taskAttachmentsPaginated`.  
  
#### 5.4 — useUploadTaskAttachmentMutation (new)  
**File:** `resources/js/entities/task/mutations/use.upload-task-attachment.mutation.ts`  
  
```ts  
export function useUploadTaskAttachmentMutation(taskId: MaybeRefOrGetter<string>) {  
    // mutationFn: ({ file, role? }) => uploadTaskAttachmentRequest(toValue(taskId), file, role)    // onSuccess: invalidate TaskAttachmentQueryKey.taskAttachments(taskId)}  
```  
  
#### 5.5 — useUploadTaskAttachment composable (new)  
**File:** `resources/js/entities/task/composables/use.upload-task-attachment.ts`  
  
This is the composable consuming widgets use directly:  
  
```ts  
export function useUploadTaskAttachment(  
    taskId: MaybeRefOrGetter<string>,    role?: AttachmentRole) {  
    const toast = useToast()    const { mutate, isPending } = useUploadTaskAttachmentMutation(taskId)  
    function upload(file: File) {        mutate(            { file, role },            {                onSuccess: () => toast.success('File uploaded successfully.'),                onError: (error) => toast.error(/* ... */),            }        )    }  
    return { upload, isPending }}  
```  
  
Usage in consuming widget:  
```ts  
const { upload, isPending } = useUploadTaskAttachment(taskId, TASK_ATTACHMENT_ROLES.UPLOAD)  
```  
```html  
<AttachmentDropZone @file-drop="upload" :is-uploading="isPending" />  
<UploadAttachmentButton @file-selected="upload" :is-uploading="isPending" />  
```  
  
#### 5.6 — Update task entity index.ts  
Re-export new composable, query, mutation, and api from `entities/task/index.ts` and barrel files.  
  
---  
  
### Phase 6 — Frontend: Upload widget components  
  
#### 6.1 — AttachmentDropZone.vue (refactor)  
**File:** `resources/js/widgets/attachments/attachment-uploader/ui/AttachmentDropZone.vue`  
  
- **Remove:** `entityType`, `entityId`, `role` props; `useAttachmentUpload` import  
- **Add:** `isUploading?: boolean` prop  
- **Keep:** `maxFileSizeBytes?: number` prop (validate file size before emit; show toast if exceeded)  
- **Emit:** `file-drop: [file: File]`  
- `onDrop`: validate size → emit `'file-drop', files[0]`; disable interaction while `isUploading`  
  
#### 6.2 — UploadAttachmentButton.vue (refactor)  
**File:** `resources/js/widgets/attachments/attachment-uploader/ui/UploadAttachmentButton.vue`  
  
- **Remove:** `entityType`, `entityId`, `role` props; `useAttachmentUpload` import  
- **Add:** `isUploading?: boolean` prop  
- **Emit:** `file-selected: [file: File]`  
- Button `:disabled="isUploading"`  
  
#### 6.3 — Remove useAttachmentUpload composable  
**File:** `resources/js/widgets/attachments/attachment-uploader/composables/use.attachment-upload.ts` → **delete**  
  
Upload + toast logic moves to `useUploadTaskAttachment` (and future `useUploadProjectAttachment`).  
  
#### 6.4 — Update widget index.ts  
Remove `useAttachmentUpload` export.  
  
---  
  
### Phase 7 — Frontend: Update consumers  
  
#### 7.1 — TaskAttachmentsPage.vue (update)  
**File:** `resources/js/pages/tasks/details/tabs/TaskAttachmentsPage.vue`  
  
**Remove:**  
- `useAttachmentsSearchQuery` + complex filter params  
- `TASK_MODULE_NAME` import (no longer passed to components)  
- `entityType`, `entityId`, `role` props on `AttachmentDropZone` and `UploadAttachmentButton`  
  
**Add:**  
- `useTaskAttachmentsQuery(taskId)` — replaces search query  
- `useUploadTaskAttachment(taskId, TASK_ATTACHMENT_ROLES.UPLOAD)` — returns `{ upload, isPending }`  
- `AttachmentDropZone`: `@file-drop="upload" :is-uploading="isPending"`  
- `UploadAttachmentButton`: `@file-selected="upload" :is-uploading="isPending"`  
  
**Keep:**  
- `useDeleteAttachmentMutation`, pagination, row menu (delete/download), `AttachmentsTableView`  
  
#### 7.2 — MarkdownEditor.vue (update)  
**File:** `resources/js/shared/components/md-editor/ui/MarkdownEditor.vue`  
  
Same emit pattern as `AttachmentDropZone` and `UploadAttachmentButton` — component emits, consumer resolves.  
  
- **Remove:** `uploadAttachmentRequest` import; `image_entity_type`, `image_entity_id`, `image_role` props  
- **Add emit:** `upload-images: [files: File[], callback: (urls: string[]) => void]`  
- **Update `handleUploadImages`:**  
  ```ts  
  function handleUploadImages(files: File[], callback: (urls: string[]) => void) {  
      if (!files.length) return      emit('upload-images', files, callback)  }  
  ```  
Consumer (e.g. task description editor):  
```ts  
async function handleImageUpload(files: File[], callback: (urls: string[]) => void) {  
    const urls = await Promise.all(files.map(file => uploadTaskFile(file)))  
    callback(urls)  
}  
```  
```html  
<MarkdownEditor v-model="content" @upload-images="handleImageUpload" />  
```  
  
- Find all call sites that passed `image_entity_*` props and update them to handle `@upload-images` instead (grep: `image_entity_type`).  
  
---  
  
## Risks and Mitigations  
  
| Risk | Mitigation |  
|------|------------|  
| Stored `entity_type` values ('tasks', 'projects') won't match full Eloquent class names | Register morph map in `AppServiceProvider::boot()` before migration — stored short aliases resolve correctly |  
| PHPStan: `Model` type in command/service too broad | Use `@param \Illuminate\Database\Eloquent\Model $attachable` PHPDoc; level 5 accepts this |  
| Delete mutation needs to invalidate task attachment queries after rename | `TaskAttachmentQueryKey` uses `['attachments', 'tasks', ...]` prefix — `invalidateQueries({ queryKey: ['attachments'] })` covers it via TanStack prefix matching |  
| `MarkdownEditor` call sites passing `image_entity_*` props will break at type-check | Grep for `image_entity_type` before removing props; update all call sites in same PR |  
| `associate()` on unsaved model — timing issue | `associate()` only sets FK columns on the in-memory model; save happens after — this is safe |  
  
---  
  
## Verification Steps  
  
1. `./vendor/bin/pint` — no formatting errors  
2. `./vendor/bin/phpstan analyse` — no errors at level 5  
3. `php artisan migrate` — migration runs cleanly; `SELECT attachable_type, attachable_id FROM attachments` shows expected values  
4. `npm run format && npm run lint && npm run types:check` — all pass  
5. Manual: upload file on `TaskAttachmentsPage` → appears in table  
6. Manual: drag-and-drop onto `AttachmentDropZone` → file appears  
7. Manual: click `UploadAttachmentButton` → file picker → file appears  
8. Manual: delete attachment → row removed from table  
9. Manual: `GET /tasks/{id}/content` still redirects to signed S3 URL