---
type: task
status: draft
---

# 001 - Attachments Table

## Goal

Додати end-to-end відображення атачментів, привʼязаних до сутності, на прикладі вкладки Attachments сторінки Task Details. Для цього зробити мінімальну backend підтримку пошуку атачментів з фільтрацією, додати спільний `AttachmentsTable` widget і підключити його у `TaskAttachmentsPage`.

## Dependencies

- `002 - Introduce ModuleName Brand Type` має бути завершена **перед** цією задачею. Ця задача використовує константу `TASK_MODULE_NAME: ModuleName` для значення `entity_type` у filter payload замість літералу.

## Context

Сутність Attachment вже існує:

- backend модель `AttachmentModel` (`app/Domains/Attachment/Models/AttachmentModel.php`) зі схемою таблиці `attachments` (`original_name`, `extension`, `mime_type`, `size_bytes`, `entity_type`, `entity_id`, `role`, ...);
- контролер `AttachmentsController` (`app/Http/Controllers/Attachments/AttachmentsController.php`) з методами `store` і `content`;
- `AttachmentResource` (`app/Http/Resources/Attachments/AttachmentResource.php`) уже формує `url`, `original_name`, `extension`, `mime_type`, `size_bytes`, `entity_type`, `entity_id`, `role`;
- frontend модуль `entities/attachment` (`resources/js/entities/attachment/`) містить лише `types` і `api` для upload.

Поточний `AttachmentsController` не має методу пошуку/list, а `AttachmentModel` не має фільтрації — для інших сутностей це реалізовано через `HasFilters` + `allowedFilters()` (див. `TaskModel`, `ProjectModel`) та окремий `search` метод у контролері з валідацією через `App\Http\Requests\Shared\SearchRequest`.

На frontend сторінка `TaskAttachmentsPage` (`resources/js/pages/tasks/details/tabs/TaskAttachmentsPage.vue`) зараз показує лише плейсхолдер "Not implemented".

## Scope

Що входить у задачу:

**Backend:**

- Додати `HasFilters` трейт до `AttachmentModel` і реалізувати `allowedFilters()` з підтримкою фільтрації по `entity_type`, `entity_id`, `role` (через відповідні фільтри з `app/Libs/EloquentFilters/Filters/` за патерном `TaskModel::allowedFilters()`).
- Додати метод `search(SearchRequest $request)` у `AttachmentsController`, який повертає `AnonymousResourceCollection` із `AttachmentResource`, використовуючи `AttachmentModel::query()->filter(...)` (без Laravel Scout) і стандартну `paginate`/`sort` інфраструктуру контролера (`getPaginationParams`, `getSortParams`).
- Підвантажувати у запиті відношення `createdBy`, `updatedBy`.
- Зареєструвати маршрут `POST /attachments/search` з middleware `auth:sanctum` у `routes/api.php` поряд із наявними маршрутами атачментів за патерном `tasks/search`.

**Frontend — entities/attachment:**

- Розширити модуль:
  - типи: `AttachmentSearchParams`, `AttachmentInclude` (за патерном `TaskSearchParams`);
  - `api/`: функція `searchAttachmentsRequest` (POST `/attachments/search`) за патерном `searchTasksRequest`;
  - `queries/use.attachments-search.query.ts` (за патерном `use.tasks-search.query.ts`);
  - `config/attachment-query-keys.config.ts` (за патерном `task-query-keys.config.ts`).

**Frontend — widgets/attachments/attachments-table (новий widget):**

- Створити widget `widgets/attachments/attachments-table/` зі структурою:
  - `ui/AttachmentsTable.vue`;
  - `index.ts` з реекспортом.
- Компонент pure-presentational за патерном `widgets/tasks/tasks-table/ui/TasksTable.vue`:
  - props: `attachments: IAttachment[]`, `isPending: boolean`, `paginationMeta?: PaginationMeta`, `page: number`;
  - emits: `pageChange`;
  - PrimeVue `DataTable` + `Column` + `Paginator` у footer (як у `TasksTable`).
- Колонки:
  - **Name** — `original_name`;
  - **Type** — `extension` (значення поля як є, нижній регістр; якщо `extension` `null` — порожнє значення);
  - **Size** — `size_bytes`, відформатований у людиночитному вигляді (B / KB / MB / GB) через нову shared utility-функцію в `resources/js/shared/utils/` (наприклад `formatFileSize`). Якщо існує готова utility — переюзати її;
  - **Download** — посилання `attachment.url` (відкривається у новій вкладці, target="_blank", з текстом "Download" або іконкою-посиланням; без додаткової логіки скачування).

**Frontend — TaskAttachmentsPage:**

- Замінити плейсхолдер на інтеграцію `AttachmentsTable`.
- Зчитувати `task.id` з route params (`route.params.id`).
- Використати `useAttachmentsSearchQuery` із фіксованими `filters`:
  - `entity_type = TASK_MODULE_NAME` (константа з `entities/task/config/`, введена у задачі `002`);
  - `entity_id = <task.id>`.
- Підтримати pagination через локальний `page` ref і `Paginator` у таблиці (як у `TasksPage`).
- Без toolbar (без search input, filter button, sort button).
- Підвантажувати атачменти тільки коли `taskId` визначено.

## Out Of Scope

Що не входить у задачу:

- Прев'ю файлів (зображення, PDF), thumbnails, іконки за типом.
- Upload UI у `TaskAttachmentsPage` (поточна задача — тільки відображення).
- Delete / Rename / Reorder атачментів.
- Search input, filter sidebar чи sort UI у `TaskAttachmentsPage` або `AttachmentsTable`.
- Зміни в `AttachmentResource` (форма відповіді не змінюється).
- Додавання Laravel Scout (Searchable) до `AttachmentModel`.
- Підтримка фільтрації по інших полях (`original_name`, `extension`, `mime_type`, `size_bytes`).
- Інтеграція `AttachmentsTable` поза вкладкою Task Details.
- Frontend filters config / filter sidebar для атачментів.
- Зміни в `routes/api.php` поза реєстрацією `POST /attachments/search`.
- Нові міграції чи зміни схеми таблиці `attachments`.

## Expected Behavior

Користувач переходить на сторінку Task Details, обирає вкладку Attachments і бачить таблицю з атачментами, привʼязаними до цієї задачі.

Таблиця показує колонки: Name, Type, Size, Download.

Колонка Download містить посилання на скачування файлу (`attachment.url`), що відкривається у новій вкладці.

Якщо у задачі немає атачментів, таблиця показує стандартний empty state PrimeVue `DataTable`.

Якщо атачментів більше ніж одна сторінка, у footer таблиці зʼявляється `Paginator`, переключення сторінок працює без перезавантаження сторінки.

Під час завантаження таблиця показує loading state.

Backend `POST /attachments/search` приймає `filters[]` за форматом `SearchRequest` і повертає paginated `AttachmentResource` колекцію з підвантаженими `createdBy`/`updatedBy`.

Пустий `entity_id` або `entity_type` у фільтрі не передається у запит (за патерном `TasksPage` — поведінка shared filter resolver).

## Technical Notes

- Backend search метод реалізовується **без** Laravel Scout: `AttachmentModel::query()->with([...])->filter($filters)->orderBy(...)->paginate(...)`. Не додавати трейт `Searchable` і не реалізовувати `toSearchableArray`.
- Для `entity_type` і `role` використати `TextFilter`, для `entity_id` — `LookupFilter` (за патерном `LookupFilter::class` для `project_id`/`task_list_id` у `TaskModel`).
- Frontend filter payload для прив'язки до задачі формувати напряму у `TaskAttachmentsPage` як `FilterPayloadItem[]` зі значеннями `entity_type = TASK_MODULE_NAME` та `entity_id = task.id`. Не вводити нову `attachment-filters.config.ts` у цій задачі.
- `AttachmentsTable` має лишатися pure-presentational: не містити query-логіки, не знати про `taskId`. Запит виконує сторінка.
- Розмір файлу форматувати у людиночитному вигляді через shared utility. Якщо utility ще немає — створити мінімальну `formatFileSize(bytes: number | null): string` у `resources/js/shared/utils/` і додати `index.ts` reexport, дотримуючись існуючої організації shared utils.
- Дотримуватись наявних патернів `entities/task` при організації `entities/attachment` (`api`, `queries`, `config`, `types` + `index.ts` reexports).
- Не встановлювати нові packages.
- `PAGE_SIZE` брати з `@/app/config` як у `TasksPage`.

## Acceptance Criteria

**Backend:**

- [ ] `AttachmentModel` має трейт `HasFilters` і метод `allowedFilters()` з підтримкою фільтрації по `entity_type`, `entity_id`, `role`.
- [ ] `AttachmentsController` має метод `search(SearchRequest $request)` за патерном `TasksController::search` (без Scout).
- [ ] Маршрут `POST /attachments/search` зареєстрований у `routes/api.php` з `auth:sanctum`.
- [ ] Search endpoint повертає paginated `AttachmentResource` колекцію.
- [ ] Search endpoint поважає `filters[]` payload з `SearchRequest`.
- [ ] Search endpoint підтримує `sort_by` / `sort_order` / `page` / `per_page` як інші сутності.
- [ ] У відповіді підвантажені `createdBy`, `updatedBy`.
- [ ] PHP validation: `./vendor/bin/pint` і `./vendor/bin/phpstan analyse` проходять.

**Frontend:**

- [ ] `entities/attachment` містить `searchAttachmentsRequest`, `useAttachmentsSearchQuery`, `AttachmentQueryKey`, `AttachmentSearchParams` тип.
- [ ] Створений widget `widgets/attachments/attachments-table` з компонентом `AttachmentsTable.vue` (pure-presentational, props: `attachments`, `isPending`, `paginationMeta`, `page`; emits: `pageChange`).
- [ ] `AttachmentsTable` має колонки Name, Type (extension), Size (human-readable), Download (посилання на `attachment.url`, відкривається у новій вкладці).
- [ ] `TaskAttachmentsPage` рендерить `AttachmentsTable` із фіксованими фільтрами `entity_type = TASK_MODULE_NAME` і `entity_id = <task.id>`.
- [ ] Pagination працює у вкладці Attachments (footer `Paginator` при `last_page > 1`).
- [ ] Empty state показується, коли у задачі немає атачментів.
- [ ] Loading state показується під час завантаження.
- [ ] Frontend validation: `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Open Questions

- Семантика колонки "Type" обрана як значення поля `extension`. Якщо `extension` має бути приведене до lower-case або без крапки — буде уточнено візуально під час review.
- Точний presentation Download (текстове посилання vs іконка) не зафіксований дизайном; реалізувати мінімальний варіант (текст "Download") і узгодити під час review.
- Якщо `shared/utils/formatFileSize` (або еквівалент) уже існує — переюзати; інакше створити мінімальну реалізацію в межах цієї задачі.

## Notes For Developer Agent

- Не додавати Laravel Scout до `AttachmentModel`.
- Не реалізовувати фільтрацію по інших полях, окрім `entity_type`, `entity_id`, `role`.
- Не вводити toolbar (search/filter/sort) у `TaskAttachmentsPage` у межах цієї задачі.
- Не додавати upload UI на вкладку Attachments.
- Якщо зʼявляється потреба у новій абстракції, спочатку перевірити існуючі патерни в `entities/task` і `widgets/tasks/tasks-table`.
- Тримати `AttachmentsTable` pure-presentational, query робити на рівні сторінки.
