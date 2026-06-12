---
type: task
status: draft
---

# 006 - Frontend Tag Entity Layer

## Goal

Створити frontend entity модуль `entities/tag/` з API, типами, query/mutation композаблами та конфігом.

## Context

Sprint 006 додає теги до Task і Project на frontend. Перед UI-компонентами потрібен entity layer, який інкапсулює доступ до backend API і інтегрується з TanStack Vue Query так само, як інші entities проєкту (`entities/project`, `entities/task`).

## Scope

Створити модуль `resources/js/entities/tag/`:

- `api/` — функції викликів API:
  - create tag (`POST /api/tags`);
  - search tags (`GET /api/tags?search=...`);
  - get record tags (`GET /api/tasks/{id}/tags`, `GET /api/projects/{id}/tags`).
- `types/` — TypeScript типи:
  - `Tag` (id, name, color);
  - `CreateTagPayload` (name, color?).
- `queries/` — TanStack Query композаблы:
  - `useTagsSearch(query)`;
  - `useRecordTags(recordType, recordId)`.
- `mutations/` — TanStack mutation:
  - `useCreateTag()`.
- `config/` — константи: `TAG_QUERY_KEYS`.
- `index.ts` — public API (re-exports).

## Out Of Scope

- UI компоненти (`TagBadge`, `TagInput`, dialogs) — task 007.
- Інтеграція в EditTaskPage / EditProjectPage / filters — task 008.

## Expected Behavior

- `useTagsSearch('bug')` повертає reactive результат пошуку.
- `useRecordTags('task', taskId)` повертає повний список тегів сутності.
- `useCreateTag()` повертає mutation, після успіху invalidate-ить tags search query.
- Усі API виклики йдуть через існуючий HTTP клієнт у `shared/api/`.

## Technical Notes

- Слідувати структурі і конвенціям existing entities (`entities/project`, `entities/task`).
- Query keys — централізовані у `config/`.
- Помилки — повертати через existing error type з `shared/api/`.

## Acceptance Criteria

- [ ] `resources/js/entities/tag/` існує з api, types, queries, mutations, config, index.
- [ ] `useTagsSearch` працює з реактивним query string.
- [ ] `useRecordTags` приймає тип сутності і ID, формує правильний URL.
- [ ] `useCreateTag` invalidate-ить пошуковий query після успіху.
- [ ] Format, lint, types check проходять.

## Open Questions

- N/A

## Notes For Developer Agent

- Не використовувати ці queries/mutations всередині цього task — тільки експорт.
- Search endpoint реалізований у task 002.
