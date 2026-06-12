---
task: 006 - Frontend Tag Entity Layer
status: done
---

# 006 - Frontend Tag Entity Layer

## What Was Implemented

`resources/js/entities/tag/` — entity module з api, types, queries, mutations, config, index.ts.

- `Tag` і `CreateTagPayload` types.
- `createTagRequest` і `searchTagsRequest` у `tag.api.ts`.
- `TagQueryKey` із `all` і `search` keys.
- `useTagsSearchQuery(query)` — реактивний пошук тегів.
- `useTaskTagsQuery(taskId)` і `useProjectTagsQuery(projectId)` — повний список тегів сутності.
- `useCreateTagMutation()` — створення тега з `invalidateQueries` на `TagQueryKey.all`.

## Architecture Decisions

- `fetchTaskTagsRequest` → `entities/task/api/task.api.ts` (з `import type { Tag }`)
- `fetchProjectTagsRequest` → `entities/project/api/project.api.ts` (те саме)
- Query keys для tags сутностей — у відповідних entity configs:
  - `TaskQueryKey.tags(taskId)` → `entities/task/config/task-query-keys.config.ts`
  - `ProjectQueryKey.tags(projectId)` → `entities/project/config/index.ts`
- `useTaskTagsQuery` / `useProjectTagsQuery` імпортують api і query keys з відповідних entity шарів.
- `import type { Tag }` в task/project apis — type-only, не створює runtime circular dependency.

## Files Created

- `resources/js/entities/tag/` — 12 файлів

## Files Modified

- `resources/js/entities/task/api/task.api.ts` — додано `fetchTaskTagsRequest`
- `resources/js/entities/task/config/task-query-keys.config.ts` — додано `tags` key
- `resources/js/entities/project/api/project.api.ts` — додано `fetchProjectTagsRequest`
- `resources/js/entities/project/config/index.ts` — додано `tags` key

## Checks Run

- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
