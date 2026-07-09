# Project documents (documentation hub)

Backend foundation for storing project-scoped documentation in a hierarchical,
filesystem-like structure. Each document can hold content and/or act as a
container for child documents — there is no separate "folder" type.

This covers the data model only. No API, UI, editor, or move/reorder commands
exist yet (out of scope for this iteration).

## Entities

- `App\Domains\ProjectDocument\Models\ProjectDocumentModel` (table `project_documents`)
- `App\Domains\ProjectDocument\Enums\ProjectDocumentStatus`
- Pivot table `project_document_task` — many-to-many between documents and tasks
  (both must belong to the same project; not enforced at the DB level, see
  Constraints below).
- Tags — reuses the existing polymorphic `taggables` mechanism, same as
  `ProjectModel`/`TaskModel` (`tags(): MorphToMany`).

## Hierarchy: parent_id + ltree path

Two parallel representations of the tree, per the task's design:

- **`parent_id`** — direct link to the parent document (simple relational
  integrity, `ON DELETE SET NULL`).
- **`path`** — a PostgreSQL `ltree` materialized path storing the chain of
  ancestor **ids** (not titles), e.g. `01kx0w32eeq1e764ks0zfhk0gv.01kx0w6dh9...`.
  ULIDs are valid `ltree` labels (alphanumeric, Crockford base32), so no
  separate encoding step is needed.
- **`depth`** — nesting level, cached to avoid recomputing it from `path` on
  every read.

`path`/`depth` are computed automatically by
`ProjectDocumentModel::applyHierarchy()`, wired into the `creating` hook and
into `updating` whenever `parent_id` or `project_id` changes. A root document
(`parent_id === null`) gets `path = id`, `depth = 0`; a child document gets
`path = "{parent.path}.{id}"`, `depth = parent.depth + 1`.

The `path` column has a `GiST` index (`project_documents_path_gist_idx`),
required for efficient ancestor/descendant tree lookups
(`path <@ '...'`, `path @> '...'`).

## Constraints enforced

- A document must belong to a project (`project_id` is required, FK cascade
  delete).
- A child document must belong to the same project as its parent — checked in
  `applyHierarchy()`, throws `DomainException` otherwise.
- A document cannot be its own parent, and cannot be moved under its own
  descendant (cycle prevention) — same method, same exception type.
- Sibling titles must be unique per `(project_id, parent_id)` — a plain
  `unique(project_id, parent_id, title)` DB constraint covers non-root levels;
  a **partial unique index** (`project_documents_root_title_unique`, `WHERE
  parent_id IS NULL`) covers root-level titles separately, because Postgres
  unique constraints treat `NULL` as distinct and would not otherwise dedupe
  root titles.

Validation lives directly in the model's boot hooks rather than in an
Actions/Handler (there is no Handler/API layer for this entity yet — adding
one was out of scope for this iteration).

## Known limitations (explicitly out of scope for this iteration)

The task excluded "move within the tree" business logic except where minimally
necessary for a correct model. As a result:

- Moving a document that has children does **not** cascade `path`/`depth`
  updates to its descendants — only the moved document itself is recomputed.
- Deleting a parent document sets children's `parent_id` to `NULL` at the
  database level (`nullOnDelete`), bypassing Eloquent hooks — orphaned
  children keep a stale `path`/`depth` pointing at the deleted ancestor.
- No pessimistic locking guards the cycle check, so two concurrent opposite
  moves (A under B, B under A) could theoretically both pass validation.

Any future work implementing document moves/reordering needs to address these.

## Local setup note: the `ltree` extension

The migration runs `CREATE EXTENSION IF NOT EXISTS ltree`, which requires
`CREATE` privilege on the database. The default application DB user in this
project does not have it — the extension must be created once by a superuser
(e.g. `root` in the `task_manager_postgres` container):

```bash
docker exec task_manager_postgres psql -U root -d task_manager -c "CREATE EXTENSION IF NOT EXISTS ltree;"
```

Do this for every environment/database the migration runs against (dev, test,
staging, prod) before running `php artisan migrate`.
