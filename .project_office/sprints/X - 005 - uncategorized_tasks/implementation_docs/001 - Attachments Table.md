# 001 - Attachments Table

## What was implemented

End-to-end attachment listing for the Task Details → Attachments tab. Added backend search endpoint, extended `entities/attachment` module with search infrastructure, created a pure-presentational `AttachmentsTable` widget, and integrated it into `TaskAttachmentsPage`.

Also fixed two post-implementation bugs:
- Attachment download links returned "Route [login] not defined" — fixed by switching to signed URLs and `signed` middleware.
- S3 `UnableToRetrieveMetadata` on file size — fixed by redirecting to S3 temporary URL instead of proxying through Laravel.

## Changed files

**Backend**

| File | Change |
|------|--------|
| `app/Domains/Attachment/Models/AttachmentModel.php` | Added `HasFilters`, `allowedFilters()` (entity_type/role → TextFilter, entity_id → LookupFilter) |
| `app/Http/Controllers/Attachments/AttachmentsController.php` | Added `search()` method; `content()` now redirects to S3 temporary URL |
| `app/Http/Resources/Attachments/AttachmentResource.php` | `url` generated via `URL::temporarySignedRoute()` with 1h TTL |
| `routes/api.php` | Added `POST /attachments/search`; content route uses `signed` middleware instead of `auth:sanctum` |

**Frontend — entities/attachment**

| File | Change |
|------|--------|
| `entities/attachment/types/attachment.types.ts` | Added `AttachmentSearchParams`, `AttachmentInclude` types |
| `entities/attachment/api/attachment.api.ts` | Added `searchAttachmentsRequest` |
| `entities/attachment/config/attachment-query-keys.config.ts` | New file — `AttachmentQueryKey` |
| `entities/attachment/config/index.ts` | New file |
| `entities/attachment/queries/use.attachments-search.query.ts` | New file — `useAttachmentsSearchQuery` |
| `entities/attachment/queries/index.ts` | New file |

**Frontend — shared / widget / page**

| File | Change |
|------|--------|
| `shared/utils/file.util.ts` | New file — `formatFileSize(bytes)` utility |
| `widgets/attachments/attachments-table/ui/AttachmentsTable.vue` | New pure-presentational component (columns: Name, Type, Size, Download) |
| `widgets/attachments/attachments-table/index.ts` | New file |
| `pages/tasks/details/tabs/TaskAttachmentsPage.vue` | Replaced placeholder with `AttachmentsTable` integration |

## Decisions

- `AttachmentsTable` stays pure-presentational — no query logic inside it.
- Attachment content served via redirect to S3 pre-signed URL (not proxied). Avoids `Content-Length` metadata fetch that S3 adapter can't reliably perform.
- Download link uses signed route with 1h TTL; `signed` middleware on the content route validates access without requiring a Bearer token (so the link works in a new browser tab).
- Filter payload for `TaskAttachmentsPage` constructed inline — no separate filters config introduced per task spec.

## Validation

- `./vendor/bin/pint` — passed
- `./vendor/bin/phpstan analyse` — passed (0 errors)
- `npm run format` — passed
- `npm run lint` (targeted) — passed
- `npm run types:check` — passed
