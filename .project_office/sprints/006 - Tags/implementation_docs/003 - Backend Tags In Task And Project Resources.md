---
task: 003 - Backend Tags In Task And Project Resources
status: done
---

# 003 - Backend Tags In Task And Project Resources

## What Was Implemented

- `tagsLimited()` relation на `TaskModel` і `ProjectModel` — `morphToMany` з `orderBy('taggables.created_at', 'asc')` і `limit(4)`.
- `tags` поле у `TaskResource` і `ProjectResource` через `whenLoaded('tagsLimited')`.
- Eager loading `'tagsLimited'` у `TasksController::index/search` і `ProjectsController::index/search`.
- `tag_ids` / `tag_ids.*` валідація у `StoreTaskRequest`, `UpdateTaskRequest`, `StoreProjectRequest`, `UpdateProjectRequest`.
- `?array $tagIds = null` у `CreateTaskCommand`, `UpdateTaskCommand`, `CreateProjectCommand`, `UpdateProjectCommand`.
- `tags()->sync($tagIds)` у всіх 4 handlers після збереження, якщо `tagIds !== null`.

## Key Decisions

- Відсутній `tag_ids` у payload передається як `null` → sync не викликається (прив'язки не змінюються).
- `tag_ids: []` → sync з порожнім масивом → всі прив'язки видаляються.

## Files Modified

- `app/Domains/Task/Models/TaskModel.php`
- `app/Domains/Project/Models/ProjectModel.php`
- `app/Http/Resources/Tasks/TaskResource.php`
- `app/Http/Resources/Projects/ProjectResource.php`
- `app/Http/Controllers/Tasks/TasksController.php`
- `app/Http/Controllers/Projects/ProjectsController.php`
- `app/Http/Requests/Tasks/StoreTaskRequest.php`
- `app/Http/Requests/Tasks/UpdateTaskRequest.php`
- `app/Http/Requests/Projects/StoreProjectRequest.php`
- `app/Http/Requests/Projects/UpdateProjectRequest.php`
- `app/Domains/Task/Actions/CreateTask/CreateTaskCommand.php`
- `app/Domains/Task/Actions/Task/UpdateTask/UpdateTaskCommand.php`
- `app/Domains/Project/Actions/CreateProject/CreateProjectCommand.php`
- `app/Domains/Project/Actions/UpdateProject/UpdateProjectCommand.php`
- `app/Domains/Task/Actions/CreateTask/CreateTaskHandler.php`
- `app/Domains/Task/Actions/UpdateTask/UpdateTaskHandler.php`
- `app/Domains/Project/Actions/CreateProject/CreateProjectHandler.php`
- `app/Domains/Project/Actions/UpdateProject/UpdateProjectHandler.php`

## Files Created

- `tests/Feature/Tags/TaskTagSyncTest.php` — 4 тести

## Checks Run

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — 0 errors
- `php artisan test` — 72 passed, 1 skipped (pre-existing)
