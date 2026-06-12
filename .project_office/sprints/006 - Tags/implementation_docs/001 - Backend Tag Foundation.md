---
task: 001 - Backend Tag Foundation
status: done
---

# 001 - Backend Tag Foundation

## What Was Implemented

- `TagModel` with ULID, `$timestamps = false`, `$fillable = ['name', 'color']`.
- Migration `create_tags_table`: ULID PK, unique `name`, `color` (HEX with `#`), no timestamps.
- Migration `create_taggables_table`: `tag_id` FK (cascade delete), `taggable_id`, `taggable_type`, `created_at`; composite unique on (`tag_id`, `taggable_id`, `taggable_type`); index on (`taggable_id`, `taggable_type`); index on `tag_id`.
- `tags(): MorphToMany` added to `TaskModel` and `ProjectModel`.
- `@property Collection<int, TagModel> $tags` PHPDoc added to `TaskModel` and `ProjectModel`.
- `@property` PHPDoc for all fields added to `TagModel`.

## Files Created

- `app/Domains/Tag/Models/TagModel.php`
- `database/migrations/2026_06_12_100000_create_tags_table.php`
- `database/migrations/2026_06_12_100001_create_taggables_table.php`

## Files Modified

- `app/Domains/Task/Models/TaskModel.php` — added `tags()` relation + PHPDoc
- `app/Domains/Project/Models/ProjectModel.php` — added `tags()` relation + PHPDoc

## Key Decisions

- `withTimestamps()` not used on the pivot relation — pivot table has only `created_at`, no `updated_at`. `withPivot('created_at')` is used instead.
- Color stored with `#` prefix (e.g. `#ff0000`) — matches HTML/CSS standard, no transformation needed on frontend.
- No morph map found in the project — standard Eloquent class-based morph names used.

## Checks Run

- `php artisan migrate` — passed
- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — 0 errors

## Notes For Next Agent

- `TagModel`, `tags()` relation, and pivot structure are ready for task 002 (CreateTag endpoint) and task 003 (Resource integration).
- The pivot has `created_at` accessible via `withPivot('created_at')` — use `orderBy('taggables.created_at')` for ordering in task 003.
