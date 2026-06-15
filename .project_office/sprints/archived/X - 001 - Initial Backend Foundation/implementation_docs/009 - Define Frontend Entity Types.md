# 009 - Define Frontend Entity Types

## Що реалізовано

Додано input types до існуючих entity types і створено типи для `Attachment`.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Оновлено | `resources/js/entities/project/types/project.types.ts` |
| Оновлено | `resources/js/entities/task_list/types/task_list.types.ts` |
| Оновлено | `resources/js/entities/task/types/task.types.ts` |
| Створено | `resources/js/entities/attachment/types/attachment.types.ts` |
| Створено | `resources/js/entities/attachment/types/index.ts` |

## Додані types

### Input types (create/update)

| Entity | Types |
| --- | --- |
| Project | `ICreateProjectInput`, `IUpdateProjectInput` |
| TaskList | `ICreateTaskListInput`, `IUpdateTaskListInput` |
| Task | `ICreateTaskInput`, `IUpdateTaskInput` |
| Attachment | `IUploadAttachmentInput` |

### Нова сутність

`IAttachment extends IEntity` — поля: `original_name`, `extension`, `mime_type`, `size_bytes`, `storage_provider`, `storage_key`, `entity_type`, `entity_id`, `role`.

## Рішення

- Auditable columns (`created_by`, `updated_by`, `created_at`, `updated_at`) не включені в entity interfaces — консистентно з `IProject`, `ITaskList`, `ITask`.
- Input types розміщені у відповідних entity types файлах (як `ILoginCredentials` у `user.types.ts`).
- `IUpdateTaskInput.task_list_id` типізований як `string | null` — дозволяє виразити намір очистити значення (навіть якщо backend поки не підтримує через `array_filter`).

## Перевірки

- `tsc --noEmit` — TypeScript чистий.

## Commit message

```
feat(frontend): add entity input types and IAttachment interface
```
