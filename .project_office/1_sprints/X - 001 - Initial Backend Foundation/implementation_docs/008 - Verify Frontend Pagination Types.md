# 008 - Verify Frontend Pagination Types

## Що реалізовано

Оновлено `pagination.types.ts` відповідно до фактичної Laravel Resource Collection pagination response.

## Змінені файли

| Дія | Файл |
| --- | --- |
| Оновлено | `resources/js/shared/types/pagination.types.ts` |

## Що змінилось

| До | Після |
| --- | --- |
| `PaginationResponseMeta` з `page`, `has_more` | `PaginationMeta` з `current_page`, `from`, `to`, `path`, `links[]` |
| `PaginatedResponse.meta: { pagination: ... }` | `PaginatedResponse.meta: PaginationMeta` (flat) |
| `PaginatedResponse<T>` з `data: T` | `PaginatedResponse<T>` з `data: T[]` |
| Відсутній `PaginationLinks` | `PaginationLinks` з `first`, `last`, `prev`, `next` |
| `PagingParams` з required полями | `PagingParams` з optional полями (backend має defaults) |
| Відсутній `SortParams` | `SortParams` з `sort_by?`, `sort_order?: 'asc' \| 'desc'` |

## Перевірки

- `tsc --noEmit` — TypeScript чистий.

## Commit message

```
fix(frontend): align pagination types with Laravel paginated response
```
