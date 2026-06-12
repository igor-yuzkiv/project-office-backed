---
type: task
status: draft
---

# 008 - Delete Attachment From Task Attachments Tab

## Goal

Дати користувачу можливість видалити атачмент з вкладки Attachments сторінки Task Details через ellipsis-меню у рядку таблиці. Видалення проходить з confirm-діалогом, видаляє запис у БД і фізичний файл у storage. Після успіху таблиця оновлюється.

## Dependencies

- `001 - Attachments Table` — використовується `AttachmentsTable`, `AttachmentQueryKey`. У межах цієї задачі widget розширюється опціональною колонкою row-actions.

Не блокує і не залежить від `003`/`004`. Може виконуватись паралельно.

## Context

Backend:

- `AttachmentStorageService` (`app/Domains/Attachment/Services/AttachmentStorageService.php:24`) уже має метод `delete(AttachmentModel $attachment): void`. Конкретна реалізація — `S3AttachmentStorageService`.
- `AttachmentsController` (`app/Http/Controllers/Attachments/AttachmentsController.php`) має лише `store` і `content`; `destroy` ще немає.
- `routes/api.php` має лише `POST attachments` і `GET attachments/{attachment}/content`. DELETE-маршрут не зареєстрований.
- Конвенція delete-handler'ів існує: див. `app/Domains/Project/Actions/DeleteProject/DeleteProjectHandler.php`.

Frontend:

- Pattern delete-mutation із confirm уже існує: `resources/js/entities/project/mutations/use.delete-project.mutation.ts` (`useMutation` + `mutateWithConfirm(id, message)` через `useConfirmDialog` із `@/shared/composables/use.confirm-dialog`).
- Pattern row-action menu існує: `resources/js/pages/projects/list/ProjectsPage.vue` (PrimeVue `Menu` з ellipsis-кнопкою, `selectedProject` ref, `mutateWithConfirm` у `command`).
- `entities/attachment` уже має `api`, `types`. Папки `mutations/` поки немає (буде створена у `004`, ця задача може створити її першою — координація нижче).

`AttachmentsTable` (з `001`) — pure-presentational з колонками Name / Type / Size / Download. Без row-actions.

## Decisions Locked In

- **UI**: ellipsis-меню в окремій колонці рядка, як у `ProjectsPage`. Menu містить пункт **Delete**.
- **Confirm**: shared `useConfirmDialog` через `mutateWithConfirm`-helper у мутації.
- **Авторизація**: будь-який автентифікований user (`auth:sanctum`). Без додаткової policy. Frontend не приховує Delete за `created_by` або іншою умовою.
- **Storage cleanup**: при видаленні запису фізичний файл також видаляється через `AttachmentStorageService::delete`.

## Scope

**Backend — DeleteAttachmentHandler:**

- Створити `app/Domains/Attachment/Actions/DeleteAttachment/DeleteAttachmentHandler.php` за патерном `DeleteProjectHandler`.
- Конструктор приймає `AttachmentStorageService`.
- Метод `handle(AttachmentModel $attachment): void`:
  - викликає `$this->storage->delete($attachment)` (storage cleanup);
  - викликає `$attachment->delete()` (DB row).
- Якщо у проекті є транзакційні patterns для delete-handler'ів — повторити; інакше двоступенева операція без транзакції допустима.

**Backend — AttachmentsController:**

- Додати метод `destroy(AttachmentModel $attachment): JsonResponse`:
  - інжектити `DeleteAttachmentHandler` через конструктор;
  - викликати `$this->deleteHandler->handle($attachment)`;
  - повертати `response()->json(['message' => 'Attachment deleted.'])` за патерном `TasksController::destroy`.

**Backend — Route:**

- У `routes/api.php` додати:

```php
Route::delete('attachments/{attachment}', [AttachmentsController::class, 'destroy'])
    ->middleware(['auth:sanctum'])
    ->name('attachments.destroy');
```

- Розмістити поруч із наявними attachment маршрутами.

**Backend — validation:**

- `./vendor/bin/pint` і `./vendor/bin/phpstan analyse` проходять.

**Frontend — entities/attachment api:**

- Додати функцію `deleteAttachmentRequest(id: string)` у `resources/js/entities/attachment/api/attachment.api.ts` за патерном `deleteProjectRequest`/`deleteTaskRequest`:

```ts
export async function deleteAttachmentRequest(id: string): Promise<{ message: string }> {
    return httpClient.delete<{ message: string }>(`/attachments/${id}`).then((res) => res.data)
}
```

- Реекспорт через `api/index.ts` якщо потрібно.

**Frontend — entities/attachment mutations:**

- Якщо папки `mutations/` ще немає (залежить від того, чи `004` уже стартувала) — створити її.
- Додати `use.delete-attachment.mutation.ts` за патерном `use.delete-project.mutation.ts`:
  - `useMutation` з `mutationFn: (id) => deleteAttachmentRequest(id)`;
  - `onSuccess`: `queryClient.invalidateQueries({ queryKey: AttachmentQueryKey.all })`;
  - експортує `mutate`, `mutateWithConfirm(id, message?)` (default message — `'Are you sure you want to delete this attachment?'`), і інші результати з `useMutation`.
- Реекспорт через `mutations/index.ts`.

**Frontend — AttachmentsTable розширення:**

- У `widgets/attachments/attachments-table/ui/AttachmentsTable.vue`:
  - Додати prop `withRowActions?: boolean` (default `false`).
  - Якщо `withRowActions === true` — рендерити додаткову `Column` зі стилем `style="width: 3rem"` і body slot із ellipsis-кнопкою (`pi pi-ellipsis-v` стиль як у `ProjectsPage`).
  - Кнопка викликає emit `'rowActionOpen': [event: MouseEvent, attachment: IAttachment]`. Зупиняти propagation (`@click.stop`).
- Не змінювати наявні колонки і поведінку для випадку `withRowActions === false`.

**Frontend — TaskAttachmentsPage інтеграція:**

- У `resources/js/pages/tasks/details/tabs/TaskAttachmentsPage.vue`:
  - Передати `:with-row-actions="true"` у `AttachmentsTable`.
  - Завести `rowMenu = ref<InstanceType<typeof Menu>>()` і `selectedAttachment = ref<IAttachment>()`.
  - Імпортувати PrimeVue `Menu` і тип `MenuItem`.
  - `rowMenuItems` містить один пункт:
    - label: `'Delete'`;
    - icon: `'pi pi-trash'`;
    - command: викликає `deleteAttachment.mutateWithConfirm(selectedAttachment.value!.id, \`Are you sure you want to delete "${selectedAttachment.value!.original_name}"?\`)`.
  - Handler `onRowActionOpen(event, attachment)` встановлює `selectedAttachment` і викликає `rowMenu.value?.toggle(event)`.
  - Рендерить `<Menu ref="rowMenu" :model="rowMenuItems" popup />` поруч із таблицею.
- Використати `useDeleteAttachmentMutation()` із `entities/attachment/mutations`.

**Frontend — validation:**

- `npm run format`, `npm run lint`, `npm run types:check` проходять.

## Out Of Scope

- Bulk delete.
- Soft delete / trash / restore.
- Rename, move, change role атачмента.
- Будь-яка policy/authorization за межами `auth:sanctum`.
- Delete з інших entity attachment вкладок (наприклад, `ProjectAttachmentsPage`). Це окрема задача.
- Cascade-обробка: якщо атачмент референсується з опису задачі чи коментарів (наприклад, image у MD), link перестане працювати — це залишається існуючим обмеженням і не вирішується тут.
- Авторекавері/undo після видалення.
- Optimistic UI (видаляти рядок до підтвердження backend).
- Transaction wrapper навколо storage+DB delete (одна точка failure прийнятна для MVP).

## Expected Behavior

- На вкладці Task Details → Attachments у кожному рядку таблиці справа з'являється ellipsis-кнопка.
- Клік по ellipsis відкриває PrimeVue Menu з пунктом **Delete**.
- Клік по **Delete** відкриває confirm-діалог з message: `Are you sure you want to delete "{original_name}"?` і кнопками `Delete` / `Cancel`.
- Підтвердження викликає `DELETE /attachments/{id}`. На успіх:
  - запис видаляється з БД;
  - файл видаляється з storage через `AttachmentStorageService::delete`;
  - запит повертає 200 з JSON `{ message: 'Attachment deleted.' }`;
  - frontend інвалідовує `AttachmentQueryKey.all`, таблиця оновлюється (рядок зникає).
- Cancel закриває confirm без дії.
- Помилки backend (наприклад, 500 при storage delete) відображаються через стандартний error toast (як у решті mutations через `httpClient`).
- Інші місця, де використовується `AttachmentsTable` без `withRowActions` — поведінка без змін (без ellipsis-колонки).

## Technical Notes

- Дотримуватись існуючих delete-патернів backend і frontend без винаходу нових.
- `DeleteAttachmentHandler` — single responsibility: storage delete + DB delete. Не додавати event/notification без явного запиту.
- `mutateWithConfirm` сигнатура повторює `useDeleteProjectMutation`.
- Не вводити нові packages.
- Перед stage перевірити, що `004 - Manual Attachment Upload For Task` (якщо стартує паралельно) використовує спільну `entities/attachment/mutations/` папку — координація через index.ts.
- `Menu` ref і `selectedAttachment` ref локалізовані у `TaskAttachmentsPage`. Виносити в окремий composable не потрібно для одного пункту.

## Coordination With Other Tasks

- `004 - Manual Attachment Upload For Task` теж створює `entities/attachment/mutations/`. Перша задача, що стартує, створює папку; друга — лише додає файл і оновлює `mutations/index.ts`.
- `001 - Attachments Table` уже завершено (X- prefix). Розширення `AttachmentsTable` `withRowActions` — adıtive, не ламає існуючих споживачів (default `false`).

## Acceptance Criteria

**Backend:**

- [ ] `DeleteAttachmentHandler` існує і виконує storage delete + DB delete.
- [ ] `AttachmentsController::destroy` додано, делегує до `DeleteAttachmentHandler`.
- [ ] Маршрут `DELETE /attachments/{attachment}` зареєстровано з `auth:sanctum`.
- [ ] PHP validation: pint + phpstan проходять.

**Frontend:**

- [ ] `deleteAttachmentRequest` додано в `entities/attachment/api`.
- [ ] `useDeleteAttachmentMutation` додано в `entities/attachment/mutations`, інвалідовує `AttachmentQueryKey.all`, експортує `mutateWithConfirm`.
- [ ] `AttachmentsTable` приймає prop `withRowActions`, при `true` рендерить ellipsis-колонку і емітить `rowActionOpen: [event, attachment]`.
- [ ] За замовчуванням (`withRowActions=false`) поведінка таблиці і колонки без змін.
- [ ] `TaskAttachmentsPage` передає `:with-row-actions="true"`, рендерить PrimeVue `Menu` з пунктом Delete.
- [ ] Confirm dialog показується перед DELETE-запитом з message що містить `original_name`.
- [ ] Після успіху таблиця оновлюється без перезавантаження сторінки.
- [ ] Frontend validation: format + lint + types:check проходять.

## Open Questions

- Чи додавати у Menu ще пункт Download (як зручний shortcut), чи лишати тільки Delete у межах цієї задачі — за замовчуванням лише Delete. Download уже доступний через колонку Download.
- Чи показувати success toast після видалення — за замовчуванням ні (поведінка delete-project такої не має; інвалідація таблиці є достатнім фідбеком). Узгодити під час review.

## Notes For Developer Agent

- Не міняти інші колонки `AttachmentsTable`.
- Не вводити row click navigation у `AttachmentsTable` — лишається без navigation.
- Не змінювати `AttachmentStorageService::delete` контракт; якщо реалізація `S3AttachmentStorageService::delete` має edge cases (наприклад, файл уже відсутній у storage) — обробляти всередині service, без bleed-through у controller.
- Не змінювати `AttachmentResource` структуру.
- Якщо `004` уже виконано — координувати `mutations/index.ts` без конфлікту імпортів.
