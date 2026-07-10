# CLI API — Project documents

Agent/CLI-facing endpoints for reading and writing a project's documentation.
Routes live in `routes/api-cli.php`; the controller is
`App\Http\CliApi\Controllers\ProjectDocuments\ProjectDocumentsController`.

Everything is **project-scoped**: a document is always addressed through its
project, and a document that belongs to another project is invisible here
(returns `404`). This matches the CLI tasks API.

## Auth

All routes require a Sanctum token: `Authorization: Bearer <token>`
(`middleware('auth:sanctum')`), same as the rest of the CLI API.

## Identifying a document — `{document}`

`{document}` accepts either the internal ULID `id` **or** the human-readable
`key` (e.g. `DOC-MTM-1`). The CLI should prefer the `key`. Both resolve to the
same document (`ProjectDocumentModel::resolveRouteBindingQuery`).

## Endpoints

### `GET /api/cli/projects/{project}/docs/{document}`

Returns one document.

Response (`200`):

```json
{
  "data": {
    "id": "01kx0w32eeq1e764ks0zfhk0gv",
    "key": "DOC-MTM-1",
    "title": "Architecture",
    "status": "draft",
    "content": "… markdown body …",
    "tags": [
      { "id": "01kx…", "name": "backend", "color": "#111111" }
    ],
    "path": [
      { "id": "01kx…root", "key": "DOC-MTM-1", "title": "Architecture" }
    ]
  }
}
```

- `status` — one of `draft`, `in_review`, `active`, `deprecated`, `archived`.
- `content` — may be `null`.
- `path` — the ancestor chain **root-first, the document itself last**
  (each node: `id`, `key`, `title`). For a root document it holds a single
  entry (the document). Always present.

`404` if the document does not exist or belongs to another project.

### `POST /api/cli/projects/{project}/docs`

Creates a **root-level** document in the project. Parent/path selection is not
supported — a new document is always created at the project's documentation
root.

Body:

| Field     | Rules                                   |
| --------- | --------------------------------------- |
| `title`   | required, string, ≤ 255, unique among the project's **root** documents |
| `content` | optional, string or `null`              |
| `tags`    | optional, comma-separated string (see Tags) |

Returns the created document (`201`) in the same shape as `GET`.

`422` on validation errors (e.g. missing `title`, duplicate root `title`,
over-long tag name).

### `PUT /api/cli/projects/{project}/docs/{document}`

Updates a document. Same writable fields as create; parent/path cannot be
changed (any `parent_id` in the body is ignored). Every field is optional —
only sent fields change.

| Field     | Rules                                   |
| --------- | --------------------------------------- |
| `title`   | sometimes, required-if-present, string, ≤ 255, unique among the document's siblings |
| `content` | sometimes, string or `null`             |
| `tags`    | sometimes, comma-separated string (see Tags) |

Returns the updated document (`200`) in the same shape as `GET`.
`404` for a document from another project.

## Tags

Identical mechanism to the CLI tasks API. `tags` is a single
**comma-separated string**, not an array:

- `"bug, Backend , urgent"` → three tags; names are trimmed and de-duplicated
  case-insensitively.
- An existing tag is reused by normalized (lower-cased) name; a missing one is
  created.
- Each tag name must be ≤ 64 characters, otherwise `422` on `tags`.
- On `PUT`: sending `tags` **replaces** the document's tag set; an empty string
  clears all tags; omitting `tags` leaves them unchanged.

## Out of scope (not implemented here)

Listing, searching, creating nested documents, moving a document (changing
parent/path), and hierarchy management are intentionally not part of this API.
