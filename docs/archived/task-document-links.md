---
id: doc-0007
title: Task–Document Links (archived)
type: archive
created_date: '2026-09-12 17:10'
updated_date: '2026-09-12 17:10'
---
# Task–Document Links (archived)

**Removed from `master`; the full working state is kept on the branch `archive/task-document-links`**
(branched from `ae95ada`). It was removed because nobody used it and nothing was planned for it.

---

## What it did

A task could be linked to any number of project documents, and a document to any number of
tasks. It is one relation seen from two sides.

- Both sides had to belong to the same project. This was checked when a link was saved, not by
  the database.
- Saving from either side replaced the whole list: the request carried every id the entity
  should be linked to, and the server synced the pivot rows to match. An empty list removed all
  links.
- Deleting a task or a document deleted its links with it.

Links were not used anywhere else: not in filtering, sorting, audit trail, the dashboard, or the
CLI API.

### In the SPA

- Task details page, **Related Docs** tab (route `task-details.related-docs`): a flat table of
  linked documents (key, title, status) and an **Associate project documents** dialog to search
  documents of the task's project, tick or untick them, and save.
- Document page, **Related tasks** tab (route `project-documentation.document.tasks`), with the
  number of linked tasks as a badge on the tab: a table of linked tasks and an **Associate
  tasks** dialog of the same shape.

### Web API

All four endpoints sat behind `auth:sanctum` in the `/api` (SPA) surface:

| Method | Path | Body | Response |
|---|---|---|---|
| `GET` | `/api/tasks/{task}/project-documents` | — | paginated document overviews |
| `PUT` | `/api/tasks/{task}/project-documents` | `{ "document_ids": [ … ] }` | the linked document overviews |
| `GET` | `/api/project-documents/{project_document}/tasks` | — | paginated task overviews |
| `PUT` | `/api/project-documents/{project_document}/tasks` | `{ "task_ids": [ … ] }` | the linked task overviews |

Validation on `PUT`: the id array is required (may be empty); every id must be distinct and
must exist in the same project as the task or document in the URL.

The primary resources also carried the link:

- `GET /api/tasks/{task}?include=projectDocuments` — `project_documents`, an array of document
  overviews.
- `GET /api/project-documents/{project_document}` — `tasks_count`, always present;
  `?include=tasks` added `tasks`, an array of task overviews.

## Database

Pivot table `project_document_task`, created by
`database/migrations/2026_07_09_100001_create_project_document_task_table.php`:

| Column | Type | Notes |
|---|---|---|
| `project_document_id` | ulid | FK → `project_documents.id`, cascade on delete |
| `task_id` | ulid | FK → `tasks.id`, cascade on delete, indexed |
| `created_at`, `updated_at` | timestamps | |

Unique index on `(project_document_id, task_id)`. No `project_id` column: the same-project rule
lived in request validation only.

The table is dropped by a later migration whose `down()` recreates it with the same shape.

## Where the code was

Every path below exists on `archive/task-document-links`.

**Backend**

| What | Path |
|---|---|
| `projectDocuments()` relation on the task model | `app/Domains/Task/Models/TaskModel.php` |
| `tasks()` relation on the document model | `app/Domains/ProjectDocument/Models/ProjectDocumentModel.php` |
| sync action, task side | `app/Domains/Task/Actions/SyncTaskProjectDocuments/` |
| sync action, document side | `app/Domains/ProjectDocument/Actions/Document/SyncProjectDocumentTasks/` |
| controllers | `app/Http/WebApi/Controllers/Tasks/TaskProjectDocumentsController.php`, `app/Http/WebApi/Controllers/ProjectDocuments/ProjectDocumentTasksController.php` |
| request validation | `app/Http/WebApi/Requests/Tasks/SyncTaskProjectDocumentsRequest.php`, `app/Http/WebApi/Requests/ProjectDocuments/SyncProjectDocumentTasksRequest.php` |
| `projectDocuments` include on tasks | `app/Http/WebApi/Controllers/Tasks/TasksController.php` |
| `tasks` include and `tasks` count on documents | `app/Http/WebApi/Controllers/ProjectDocuments/ProjectDocumentsController.php` |
| resource fields `project_documents`, `tasks_count`, `tasks` | `app/Http/Shared/Resources/Tasks/TaskResource.php`, `app/Http/Shared/Resources/ProjectDocuments/ProjectDocumentResource.php`, `app/Http/Shared/Resources/ProjectDocuments/ProjectDocumentOverviewResource.php` |
| routes | `routes/api.php`, groups `tasks/{task}/project-documents` and `project-documents/{project_document}/tasks` |
| migration | `database/migrations/2026_07_09_100001_create_project_document_task_table.php` |
| tests | `tests/Feature/Http/Tasks/TaskProjectDocumentsTest.php`, `tests/Feature/Http/ProjectDocuments/ProjectDocumentTasksTest.php`, plus cases in `TaskShowTest.php`, `ProjectDocumentsCrudTest.php`, `ProjectDocumentHierarchyTest.php` |

**Frontend**

| What | Path |
|---|---|
| task tab page | `resources/js/pages/tasks/details/tabs/TaskRelatedDocsPage.vue` |
| document tab page | `resources/js/pages/documentation/workspace/tabs/DocumentTasksPage.vue` |
| associate dialogs | `resources/js/widgets/tasks/associate-project-documents-dialog/`, `resources/js/widgets/project-documents/associate-tasks-dialog/` |
| flat document table used by the task tab | `resources/js/widgets/project-documents/views/flat-table/` |
| task entity: API client, query, mutation, query key, `project_documents` type field | `resources/js/entities/task/` |
| document entity: API client, query, mutation, query key, `tasks` and `tasks_count` type fields | `resources/js/entities/project-document/` |
| routes | `resources/js/app/router/index.ts` |
| tab entries | `resources/js/pages/tasks/details/TaskDetailsPage.vue`, `resources/js/pages/documentation/workspace/DocumentationWorkspacePage.vue` |

## Bringing it back

1. See what changed: `git diff master archive/task-document-links -- <paths above>`.
2. Restore the feature-only files from the branch with `git checkout archive/task-document-links -- <paths>`,
   then re-apply the edits in the shared files (models, controllers, resources, routes, router,
   tab pages, entity barrels and types) by hand — those files have moved on since the branch was cut.
3. Recreate the table: roll back the drop migration, or write a new create migration with the
   schema above.
4. Restore the two test files and the removed cases in the three shared test files.
