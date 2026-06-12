---
type: task
status: draft
---

# 004 - Manual Attachment Upload For Task

## Goal

Дати користувачу можливість завантажити файл вручну на вкладку Attachments сторінки Task Details: через кнопку Upload над таблицею або через drag-and-drop на область таблиці. Після успішного upload новий запис з'являється у `AttachmentsTable`.

## Dependencies

- `001 - Attachments Table` — забезпечує `AttachmentsTable` widget, backend search endpoint, `useAttachmentsSearchQuery`. Інтеграція upload-кнопки і drop-zone відбувається поверх цього widget'а.
- `002 - Introduce ModuleName Brand Type` — типова обгортка для `entity_type`. Upload widget приймає `entityType: ModuleName`.

## Context

Бекенд upload endpoint вже існує:

- `POST /attachments` (`AttachmentsController::store`) приймає `file`, `entity_type`, `entity_id`, `role`;
- `UploadAttachmentRequest` дозволяє файл до 25 MB (`'file' => ['required', 'file', 'max:25600']`);
- Frontend API функція `uploadAttachmentRequest` (`resources/js/entities/attachment/api/attachment.api.ts`) уже виконує `multipart/form-data` запит, але не використовується через TanStack mutation.

`entities/attachment` поки не має `mutations/`.

`TaskAttachmentsPage` після завершення `001` показуватиме `AttachmentsTable` без toolbar. Ця задача додає upload-функціонал поверх — кнопку Upload над таблицею та overlay drop-zone, що з'являється при перетягуванні файлу.

PrimeVue v4 (`primevue: ^4.5.5`) уже надає компонент `FileUpload`, який підтримує single-file mode, custom triggers і file picker.

## Scope

**Frontend — entities/attachment mutation:**

- Створити папку `resources/js/entities/attachment/mutations/`.
- Додати `use.upload-attachment.mutation.ts` за патерном `use.create-task.mutation.ts`:
  - mutationFn: `uploadAttachmentRequest`;
  - on success: `queryClient.invalidateQueries({ queryKey: AttachmentQueryKey.all })` (константа з `001`).
- Реекспорт через `mutations/index.ts` і `entities/attachment/index.ts` (якщо існує root barrel).

**Frontend — widget `widgets/attachments/attachment-uploader` (новий):**

- Створити widget поруч із `widgets/attachments/attachments-table/` (введеним у `001`).
- Структура:
  - `ui/UploadAttachmentButton.vue` — кнопка Upload, що відкриває file picker;
  - `ui/AttachmentDropZone.vue` — wrapper, який показує overlay при drag-over і обробляє drop;
  - `composables/use.attachment-upload.ts` — спільний composable, що інкапсулює виклик mutation, валідацію розміру, toast feedback;
  - `index.ts` з реекспортом обох компонентів.
- Обидва компоненти приймають однаковий набір props:
  - `entityType: ModuleName`;
  - `entityId: string`;
  - `role?: string | null` (default `null`);
  - `maxFileSizeBytes?: number` (default `25 * 1024 * 1024` = 25 MB, відповідає бекенд-ліміту).
- Single-file upload only (один файл на запит).
- Обидва компоненти використовують `use.attachment-upload.ts` composable для виклику mutation, тож логіка не дублюється.

**UploadAttachmentButton:**

- Реалізувати через PrimeVue `<FileUpload mode="basic" auto>` (або еквівалентний basic режим).
- Single file (`multiple={false}`).
- При виборі файлу — викликати composable.
- Зовнішній вигляд: звичайна кнопка з текстом "Upload" або іконкою (узгодити з існуючими action button патернами у layout header / `useAppLayoutStore`).
- Disabled-state під час активного upload (`isPending`).

**AttachmentDropZone:**

- Wrapper, що приймає default slot (всередину передаватиметься `AttachmentsTable` зі сторінки).
- Overlay показується **тільки під час drag** файлу над зоною (event `dragenter` / `dragleave` / `dragover`). У стандартному стані overlay прихований.
- Реалізація drag-state — через VueUse `useDropZone` (`@vueuse/core` уже встановлений) або PrimeVue `FileUpload` advanced mode з кастомним overlay; обрати простіший варіант, який не дублює функціонал.
- На drop — взяти перший файл і викликати composable. Якщо файлів декілька — взяти лише перший, показати інформаційний toast про обмеження.
- Disabled drop під час активного upload.

**use.attachment-upload composable:**

- Приймає `entityType`, `entityId`, `role`, `maxFileSizeBytes`.
- Експортує `uploadFile(file: File)` і `isPending`.
- Перед запитом перевіряє `file.size <= maxFileSizeBytes`. Якщо перевищено — показати error toast (`useToast().error(...)`) і не виконувати запит.
- При успіху — success toast.
- При помилці mutation — error toast з повідомленням з backend (якщо доступне) або generic.

**Frontend — TaskAttachmentsPage:**

- Огортає `AttachmentsTable` в `AttachmentDropZone` зі значеннями `entityType={TASK_MODULE_NAME}`, `entityId={taskId}`.
- Над `AttachmentDropZone` додає action area з `UploadAttachmentButton` зі тими самими props.
- Layout розташування: action area з кнопкою справа над таблицею. Точний layout узгодити з існуючими `app-card` патернами на інших вкладках (`TaskOverviewPage`, `TaskDescriptionPage`).
- `role` поки не передається (default `null`), бо per-entity role config — це задача `003`. Після `003` сторінка зможе передавати конкретну роль.

## Out Of Scope

- Multi-file upload (декілька файлів за один drop / file select).
- Upload progress indicator per file (вистачає disabled + toast).
- Upload queue / resumable uploads.
- Editing / renaming / deleting attachments.
- File type restrictions (`accept` attribute) — приймати все. Можна додати окремою задачею, коли з'являться обмеження.
- Реалізація per-entity role config — це задача `003`.
- Upload UI поза `TaskAttachmentsPage` (для інших entity types).
- Зміни бекенд endpoint, validation, ліміту розміру.
- Зміни в `MarkdownEditor` upload поведінці.

## Expected Behavior

- На вкладці Attachments користувач бачить кнопку Upload над таблицею.
- Клік по Upload відкриває native file picker (single-file).
- Після вибору файлу кнопка переходить у disabled / loading state, виконується upload запит.
- При перетягуванні файлу з робочого столу у зону таблиці з'являється overlay з підказкою (наприклад, "Drop file to upload"). При drop файл завантажується.
- Поки drag не активний — overlay прихований, таблиця взаємодіє звичайно (row click, paginator).
- Після успішного upload показується success toast, таблиця оновлюється (новий запис видно завдяки інвалідації query).
- Якщо файл перевищує ліміт — error toast, запит не виконується.
- При помилці backend — error toast з причиною (або generic).
- Якщо у drop потрапило декілька файлів — береться лише перший, виводиться info toast про обмеження.

## Technical Notes

- Використовувати уже встановлені компоненти/утиліти; не вводити нові packages.
- Не дублювати upload logic у двох компонентах — composable `use.attachment-upload.ts` обовʼязковий.
- `AttachmentDropZone` має лишатись wrapper-компонентом (не знати про `AttachmentsTable`). Слот для контенту.
- `UploadAttachmentButton` не знає про таблицю.
- Тримати компоненти entity-agnostic — параметризація через `entityType`/`entityId`/`role`.
- Для invalidation використовувати `AttachmentQueryKey.all` (введена у `001`).
- Toast викликати через існуючий `useToast` composable (`@/shared/composables`).
- Не вводити власну `formatFileSize` тут — якщо потрібна для UI підказки "Max 25 MB", переюзати утиліту з `001`.
- Розмір файлу валідувати на frontend перед запитом, але бекенд лишається source of truth (`max:25600`).

## Acceptance Criteria

- [ ] У `entities/attachment/mutations/` існує `use.upload-attachment.mutation.ts`, що використовує `uploadAttachmentRequest` і інвалідовує `AttachmentQueryKey.all` на успіх.
- [ ] У `widgets/attachments/attachment-uploader/` існують `UploadAttachmentButton.vue`, `AttachmentDropZone.vue` і `use.attachment-upload.ts`.
- [ ] Обидва компоненти приймають props `entityType: ModuleName`, `entityId: string`, `role?: string | null`, `maxFileSizeBytes?: number`.
- [ ] `UploadAttachmentButton` відкриває native file picker через PrimeVue `FileUpload basic auto`.
- [ ] `AttachmentDropZone` показує overlay тільки під час drag і обробляє drop одного файлу.
- [ ] Composable `use.attachment-upload.ts` інкапсулює виклик mutation, перевірку розміру, toast feedback.
- [ ] `TaskAttachmentsPage` показує `UploadAttachmentButton` над таблицею і огортає таблицю в `AttachmentDropZone`.
- [ ] Після успішного upload список оновлюється без перезавантаження сторінки.
- [ ] Перевищення `maxFileSizeBytes` блокує запит і показує error toast.
- [ ] Помилки backend показуються через error toast.
- [ ] Multi-file drop вибирає лише перший файл і показує info toast.
- [ ] Frontend validation: `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Open Questions

- Точний дизайн overlay (повний backdrop vs dashed border, текст, іконка) — узгодити під час review.
- Точне розташування Upload action area (окрема `app-card` верхня смуга vs inline-кнопка справа). Узгодити з patternами на сусідніх вкладках Task Details.
- Чи додати "Max 25 MB" hint під/біля кнопки Upload, чи лишити info тільки на error.
- Чи показувати у `UploadAttachmentButton` назву поточного файлу під час upload, чи достатньо disabled-state.

## Notes For Developer Agent

- Не починати, поки `001` не завершено (потрібен `AttachmentsTable` і `AttachmentQueryKey`).
- Не починати, поки `002` не завершено (потрібен `ModuleName` для типів props).
- Не вводити role-конфіги в межах цієї задачі — це задача `003`. У 004 widget приймає `role` як generic prop, page передає `null`.
- Не реалізовувати multi-file upload навіть якщо UI здається тривіальним для розширення — це окрема задача з власним queue/error handling.
- Тримати widget entity-agnostic, не зашивати `TASK_MODULE_NAME` у самому widget.
