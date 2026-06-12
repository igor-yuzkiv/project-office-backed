---
task: 004 - Backend Record Tags Endpoints
status: done
---

# 004 - Backend Record Tags Endpoints

## What Was Implemented

- `TasksController::tags(TaskModel $task)` — повертає `TagResource::collection` з усіма тегами Task, orderBy `taggables.created_at asc`.
- `ProjectsController::tags(ProjectModel $project)` — аналогічно для Project.
- Routes: `GET /api/tasks/{task}/tags` і `GET /api/projects/{project}/tags` з `auth:sanctum`.
- 404 через route model binding — автоматично.

## Files Modified

- `app/Http/Controllers/Tasks/TasksController.php`
- `app/Http/Controllers/Projects/ProjectsController.php`
- `routes/api.php`

## Files Created

- `tests/Feature/Tags/RecordTagsTest.php` — 8 тестів (повний список, порядок, порожній, 404 — для обох endpoints)

## Checks Run

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — 0 errors
- `php artisan test --filter=RecordTagsTest` — 8/8
