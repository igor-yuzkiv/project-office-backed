# 007 - Implement Attachments Upload API

## Що реалізовано

`POST /api/attachments` — універсальний endpoint для завантаження файлів під сутність або без прив'язки.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Domains/Attachment/Actions/UploadAttachment/UploadAttachmentCommand.php` |
| Створено | `app/Domains/Attachment/Actions/UploadAttachment/UploadAttachmentHandler.php` |
| Створено | `app/Http/Requests/Attachments/UploadAttachmentRequest.php` |
| Створено | `app/Http/Resources/Attachments/AttachmentResource.php` |
| Створено | `app/Http/Controllers/Attachments/AttachmentsController.php` |
| Оновлено | `routes/api.php` |

## Route

| Method | Path | Response |
| --- | --- | --- |
| POST | `/api/attachments` | `AttachmentResource` 201 |

Middleware: `auth:sanctum`.

## Request params

| Поле | Тип | Обов'язковий |
| --- | --- | --- |
| `file` | file | так, max 25MB (`max:25600`) |
| `entity_type` | string | ні |
| `entity_id` | string | ні |
| `role` | string | ні |

## Рішення

- Storage layer (`AttachmentStorageService` / `S3AttachmentStorageService`) вже існував — handler просто делегує до нього.
- `EntityRef` створюється тільки якщо надані обидва `entity_type` і `entity_id`. Якщо тільки одне з двох — `entityRef = null` (MVP, без cross-validation).
- `entity_type` — вільний рядок, без whitelist (MVP).
- `EntityRef` не перейменовано (залишено як є).
- Resource повертає всі поля моделі, включно з `storage_key`.

## Перевірки

- `php -l` — синтаксис чистий.
- `pint --test` — стиль пройдений.
- `php artisan route:list` — `POST api/attachments` зареєстровано.

## Commit message

```
feat(attachments): implement attachment upload API endpoint
```
