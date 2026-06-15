---
task: 002 - Backend Create And Search Tag Endpoints
status: done
---

# 002 - Backend Create And Search Tag Endpoints

## What Was Implemented

- `CreateTagCommand` — input DTO з `name` і `color`.
- `CreateTagHandler` — нормалізує `name` (`trim` + `strtolower`), зберігає через `TagModel::create`.
- `CreateTagRequest` — нормалізує `name` у `prepareForValidation` перед `unique:tags,name`; `color` nullable з regex `#RRGGBB`; кастомне повідомлення при дублікаті.
- `TagResource` — повертає `id`, `name`, `color`.
- `TagsController` — `store` (POST /api/tags, 201) і `index` (GET /api/tags, search substring, orderBy name asc, limit 50).
- Рандомний HEX колір генерується в контролері (`sprintf('#%06X', mt_rand(0, 0xFFFFFF))`) якщо `color` не передано.
- Routes у `routes/api.php`: `GET /api/tags` і `POST /api/tags` з `auth:sanctum`.

## Files Created

- `app/Domains/Tag/Actions/CreateTag/CreateTagCommand.php`
- `app/Domains/Tag/Actions/CreateTag/CreateTagHandler.php`
- `app/Http/Requests/Tag/CreateTagRequest.php`
- `app/Http/Resources/Tags/TagResource.php`
- `app/Http/Controllers/Tags/TagsController.php`
- `tests/Feature/Http/Tags/CreateTagTest.php` — 7 тестів

## Files Modified

- `routes/api.php` — додано два routes

## Checks Run

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — 0 errors
- `php artisan test --filter=CreateTagTest` — 7/7

## Notes For Next Agent

- `TagResource` реалізований і готовий для task 003 і 004.
- Рандомний HEX генерується в контролері, тому `CreateTagCommand::$color` завжди `string`.
