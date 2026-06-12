---
type: task
status: draft
---

# 002 - Backend Create And Search Tag Endpoints

## Goal

Реалізувати endpoints створення тега (з валідацією, нормалізацією `name`, дефолтним рандомним HEX кольором) і пошуку тегів за `name` для frontend autocomplete у `TagInput`.

## Context

Frontend `CreateTagDialog` потребує endpoint для створення нового тега. Frontend `TagInput` multi-select потребує endpoint пошуку існуючих тегів. Обидва логічно належать одному `TagsController` і працюють із тим самим `TagResource`.

Прив'язка тегів до сутностей відбувається окремим flow (через sync у Task/Project handlers, окрема task).

## Scope

- Створити `app/Domains/Tag/Actions/CreateTag/CreateTagHandler.php`.
- Створити `app/Domains/Tag/Actions/CreateTag/CreateTagCommand.php` (input DTO).
- Створити FormRequest `app/Http/Requests/Tag/CreateTagRequest.php` з валідацією.
- Створити `app/Http/Controllers/Tags/TagsController.php` з actions:
  - `store` — `POST /api/tags`;
  - `index` — `GET /api/tags` з опціональним query параметром `search`.
- Створити resource `app/Http/Resources/Tags/TagResource.php`.
- Додати routes `POST /api/tags` та `GET /api/tags`.
- Реалізувати нормалізацію `name`: `trim` + `lowercase` у `CreateTagHandler` перед збереженням.
- Реалізувати дефолтну генерацію рандомного HEX кольору, якщо `color` не переданий.
- Реалізувати `index`:
  - якщо `search` переданий — `where('name', 'like', '%' || lower(trim(search)) || '%')`;
  - сортування за `name ASC`;
  - ліміт результатів (запропоновано 50) щоб не повертати весь список.

## Out Of Scope

- Update / delete існуючого тега.
- Tags listing з пагінацією.
- Інтеграція в API resources Task/Project.
- Sync прив'язок.
- Frontend.

## Expected Behavior

Request:

```
POST /api/tags
{
    "name": "Bug",
    "color": "#ff0000"   // optional
}
```

- `name` обов'язкове, string, max довжина — за existing convention проєкту (запропоновано 64).
- `color` опціональне. Якщо передано — string у форматі HEX (узгодити формат з task 001).
- `name` нормалізується (`trim` + `lowercase`) у handler.
- Якщо `color` не переданий — генерується рандомний HEX.
- Якщо тег із таким нормалізованим `name` уже існує — повертати `422 Unprocessable Entity` через validation rule `unique`.

Response: `201 Created` з `TagResource`.

Search request:

```
GET /api/tags?search=bu
```

Response:

```json
{
    "data": [
        {"id": "...", "name": "bug", "color": "#ff0000"},
        {"id": "...", "name": "build", "color": "#00ff00"}
    ]
}
```

- Без `search` — повертати перші 50 тегів за `name ASC`.
- З `search` — фільтрувати по нормалізованому substring match, повертати до 50.

## Technical Notes

- `CreateTagHandler` приймає `CreateTagCommand` і повертає `TagModel`.
- Валідація унікальності — на рівні FormRequest, але порівняння має бути на нормалізованому значенні. Можна нормалізувати `name` у `prepareForValidation` FormRequest, тоді стандартний rule `unique:tags,name` спрацює коректно.
- Рандомний HEX: `sprintf('#%06X', mt_rand(0, 0xFFFFFF))`.
- Без auth логіки понад те, що вже є на інших write endpoints.

## Acceptance Criteria

- [ ] `POST /api/tags` створює тег і повертає `TagResource`.
- [ ] `name` нормалізується (`trim` + `lowercase`) перед збереженням.
- [ ] Створення тега з дубльованим нормалізованим `name` повертає `422`.
- [ ] Якщо `color` не переданий — у БД зберігається рандомний HEX.
- [ ] Існує `CreateTagHandler` як єдина точка створення тега.
- [ ] `GET /api/tags` повертає до 50 тегів за `name ASC`.
- [ ] `GET /api/tags?search=...` фільтрує за substring match по нормалізованому `name`.
- [ ] Контролер тонкий: валідація через FormRequest, делегація в handler, відповідь через resource.
- [ ] Pint і PHPStan проходять без нових помилок.
- [ ] Додано targeted test для нормалізації `name`, генерації дефолтного кольору і search фільтра.

## Open Questions

- N/A

## Notes For Developer Agent

- Не додавати list / show / update / delete у цій задачі.
- Не дозволяти створення тега через інший flow — handler має бути єдиною точкою.
- Помилку про duplicate `name` краще повертати з повідомленням, зрозумілим користувачу, який не знає про нормалізацію (наприклад: "Tag with this name already exists").
