---
id: doc-0006
title: Task Owners (archived)
type: archive
created_date: '2026-09-12 16:22'
updated_date: '2026-09-12 16:22'
---
# Task Owners (archived)

**Removed from `master`; the full working state is kept on the branch `archive/task-owners`**
(branched from `ec5f4e7`). It was removed because nobody used it and nothing was planned for it.

---

## What it did

A task could carry a list of **owners**: users attached to the task, each with an optional role
and an "is primary" flag.

- Roles: `Project Manager`, `Executor`, `QA`, `Supervisor`. A role could be left empty.
- At most one owner per task was primary. A request with two primary owners was rejected with
  HTTP 422.
- The same user could not be added to one task twice.
- Saving replaced the whole list: the request carried every owner the task should have, and
  the server deleted the previous rows and wrote the new ones in one transaction. Sending an
  empty list cleared the owners.
- Deleting a task or a user deleted its owner rows with it.

Owners were not used anywhere else: not in filtering, sorting, audit trail, notifications, or
the CLI API.

### In the SPA

The task details page had an **Owners** tab (route `task-details.owners`, path
`/tasks/{key}/owners`) with a table — user name, role, primary marker — and an **Add Owners**
button. The button opened the **Manage Task Owners** dialog: search users, add or remove them,
pick a role per owner, star one as primary, then **Save**. The tab showed
"No owners assigned" when the list was empty.

### Web API

Both endpoints sat behind `auth:sanctum` in the `/api` (SPA) surface:

| Method | Path | Body | Response |
|---|---|---|---|
| `GET` | `/api/tasks/{task}/owners` | — | list of owners |
| `PUT` | `/api/tasks/{task}/owners` | `{ "owners": [ { "user_id", "role", "is_primary" } ] }` | the new list of owners |

Validation on `PUT`: `owners` nullable array; `user_id` required, must exist, distinct within
the request; `role` nullable, one of the four roles; `is_primary` required boolean.

An owner in a response:

```json
{
  "id": "01J…",
  "user": { "id": "01J…", "name": "…", "initials": "…", "avatar_url": null },
  "role": "Executor",
  "is_primary": true
}
```

## Database

Table `task_owners`, created by `database/migrations/2026_06_23_100000_create_task_owners_table.php`:

| Column | Type | Notes |
|---|---|---|
| `id` | ulid | primary key |
| `task_id` | ulid | FK → `tasks.id`, cascade on delete |
| `user_id` | ulid | FK → `users.id`, cascade on delete |
| `role` | string, nullable | one of the four role names |
| `is_primary` | boolean, default `false` | |
| `created_at`, `updated_at` | timestamps | |

Unique index on `(task_id, user_id)`.

The table is dropped by a later migration whose `down()` recreates it with the same shape.

## Where the code was

Every path below exists on `archive/task-owners`.

**Backend**

| What | Path |
|---|---|
| model | `app/Domains/Task/Models/TaskOwnerModel.php` |
| role enum | `app/Domains/Task/Enums/TaskOwnerRole.php` |
| exception (two primary owners) | `app/Domains/Task/Exceptions/InvalidTaskOwnerAssignmentException.php` |
| sync action: command, handler, item DTO | `app/Domains/Task/Actions/SyncTaskOwners/` |
| `taskOwners()` relation on the task model | `app/Domains/Task/Models/TaskModel.php` |
| exception rendered as 422 | `bootstrap/app.php` |
| controller (`index`, `sync`) | `app/Http/WebApi/Controllers/Tasks/TaskOwnersController.php` |
| request validation | `app/Http/WebApi/Requests/Tasks/SyncTaskOwnersRequest.php` |
| JSON resource | `app/Http/Shared/Resources/Tasks/TaskOwnerResource.php` |
| routes | `routes/api.php`, group `tasks/{task}/owners` |
| migration | `database/migrations/2026_06_23_100000_create_task_owners_table.php` |

**Frontend**

| What | Path |
|---|---|
| entity: API client, query, mutation, types, query keys | `resources/js/entities/task-owner/` |
| owners table, manage dialog | `resources/js/widgets/task-owners/` |
| tab page | `resources/js/pages/tasks/details/tabs/TaskOwnersPage.vue` |
| child route `owners` | `resources/js/app/router/index.ts` |
| `<Tab value="owners">` | `resources/js/pages/tasks/details/TaskDetailsPage.vue` |

There were no tests, factories, or seeders for the feature.

## Bringing it back

1. See what changed: `git diff master archive/task-owners -- <paths above>`.
2. Restore the files from the branch: `git checkout archive/task-owners -- <paths above>`, then
   re-apply the small edits in the shared files (`TaskModel.php`, `bootstrap/app.php`,
   `routes/api.php`, `router/index.ts`, `TaskDetailsPage.vue`) by hand — those files have moved
   on since the branch was cut.
3. Recreate the table: roll back the drop migration, or write a new create migration with the
   schema above.
4. Add tests.
