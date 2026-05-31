# 001 - Prepare Project JSON Resource

## Що реалізовано

Створено `ProjectResource` і `UserOverviewResource`.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Створено | `app/Http/Resources/Projects/ProjectResource.php` |
| Створено | `app/Http/Resources/Users/UserOverviewResource.php` |

## Response shape

```json
{
  "id": "01j...",
  "name": "My Project",
  "prefix": "MP",
  "created_by": { "id": "01j...", "name": "Igor" },
  "updated_by": { "id": "01j...", "name": "Igor" },
  "created_at": "2026-05-31T10:00:00.000000Z",
  "updated_at": "2026-05-31T10:00:00.000000Z"
}
```

## Рішення

- Розміщення: `app/Http/Resources/Projects/` (HTTP-layer, стандарт Laravel).
- `created_by` / `updated_by` — `UserOverviewResource` коли relation завантажено.
- Якщо relation не завантажено — поле відсутнє у response (`whenLoaded` без fallback).
- Controller відповідає за eager-loading (`->with(['createdBy', 'updatedBy'])`).

## Перевірки

- `php -l` — синтаксис чистий.
- `pint --test` — стиль пройдений.

## Для наступного агента

- `ProjectResource` готовий до використання у controller.
- Потрібно eager-load `createdBy` і `updatedBy` при поверненні resource з controller.
- Наступна task: `002 - Implement Project CRUD API`.

## Commit message

```
feat(project): add ProjectResource and UserOverviewResource
```
