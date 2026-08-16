---
id: doc-0005
title: Audit Trail
type: specification
created_date: '2026-08-16 11:45'
updated_date: '2026-08-16 11:45'
---
# Audit Trail

An activity feed for people: **what happened, who did it, when, and where to go**. The
backend records one line per user action; the SPA shows those lines on Home, newest first.

This document has two parts. The first describes what the feature does and what it
deliberately does not do. The second describes how it is built, on both sides.

---

# Part 1 — Functional

## What it is, and what it is not

It is **not** a change log. Before/after values are not stored, and an entity's state cannot
be reconstructed from the feed. Losing a single record is acceptable: nothing depends on the
feed being complete, and a failed audit write never fails the action that produced it.

Access is flat — every authenticated user sees every record. The project has no memberships,
so there is nothing to filter by.

## What gets recorded

Eighteen event types. `title` is the whole sentence shown in the list; `description` is what
the row adds when it is expanded.

| type | title | description | links to |
|---|---|---|---|
| `task.created` | Igor created MTM-44 | task name | task |
| `task.updated` | Igor updated MTM-41 | Changed name and due date | task |
| `task.status_changed` | Igor moved MTM-42 to Ready to test | In progress → Ready to test | task |
| `task.deleted` | Igor deleted MTM-39 | task description | — |
| `task.bulk_status_changed` | Igor moved 6 tasks to Ready to test | the task keys | — |
| `task.started` | Igor started MTM-42 | task name | task |
| `task.checkpoint` | Igor recorded a checkpoint on MTM-42 | subject — excerpt | task |
| `task.handoff` | Igor handed off MTM-42 for testing | resolution excerpt | task |
| `comment.created` | Igor commented on MTM-42 | comment excerpt | the commented entity |
| `task_list.created` | Igor created list «Audit Trail MVP» | list description | task list |
| `task_list.updated` | Igor updated list «Audit Trail MVP» | Changed name and status | task list |
| `task_list.tasks_added` | Igor added 6 tasks to «Audit Trail MVP» | the task keys | task list |
| `project.created` | Igor created project «MVP Task Manager» | prefix | project |
| `project.updated` | Igor updated project «MVP Task Manager» | Changed name | project |
| `project.deleted` | Igor deleted project «Sandbox» | — | — |
| `project_document.created` | Igor created «Architecture» | project name | document |
| `project_document.updated` | Igor updated «Architecture» | — | document |
| `attachment.uploaded` | Igor uploaded «schema.png» | the carrier's name | the carrier |

All product text is English. Human-written content — task names, comment excerpts, checkpoint
subjects — stays in whatever language it was written in.

## Rules worth knowing

**One user action, one line.** `task:handoff` internally creates a comment and changes a
status; the feed shows a single `task.handoff` row. Composite handlers silence the events of
the handlers they call.

**A status change and an edit are different facts.** One update can produce both
`task.status_changed` and `task.updated` — that is two lines about two different things, not a
duplicate.

**An update that changes nothing records nothing.** The feed reflects what actually moved,
not what was submitted.

**A bulk status change is one line, not N.** It carries no subject, so it does not appear in
any single task's history — a deliberate trade.

**Avatars are silent.** Uploading an avatar attaches a file to the user, and a feed full of
avatar changes is noise. Attachments to any other carrier are recorded.

**Imports are silent.** The markdown document import creates documents in bulk from the
console, where there is no actor and no user action to report.

**`task.started` records a real transition only.** `task:start` also resumes work and loads
context, so recording every call would repeat the same line for one task across sessions.

**A deleted author reads as "Someone".** `created_by` is nulled, not the record.

## What a row shows

A compact row: a coloured category badge, the author's avatar, one sentence, the time, and a
chevron. Clicking anywhere on the row expands it in place; several rows can be open at once.

Expanded, a row adds the full `description`, the exact time, the author, the machine `type`,
and a button that opens the subject.

Four kinds of rows have no button, each with its own explanation:

- an unknown type — the frontend has no entry for it yet;
- an event with no subject, such as a bulk status change;
- a deleted entity;
- a known type whose subject has no page in the SPA.

**An unknown type still renders.** A new backend event appears in the feed, readable, before
the frontend knows anything about it — a grey badge, the sentence, and no link. This is the
main reason the sentence is composed on the backend.

## States

| state | what is shown |
|---|---|
| loading the first page | three skeleton rows |
| empty | "No activity yet" |
| the first page failed | "Could not load activity." and a retry button |
| a further page failed | the rows already loaded, plus a retry line under them |
| end of the feed | the "Load more" button disappears |

Rows are grouped by day — **Today**, **Yesterday**, then the date — in the reader's own
timezone.

## Deliberately out of scope

Real-time updates and polling; filters by category; contextual feeds on task and project
pages; retention or clean-up of old records; a backfill — the feed starts at the moment of
release; translations.

---

# Part 2 — Technical

## Backend

### The library

```txt
app/Libs/AuditTrail/
├── AuditRecorder.php              # capture(): builds the row and writes it
├── Contracts/AuditRecord.php      # type(), title(), description(), subject()
├── Facades/AuditTrail.php         # AuditTrail::capture(...)
├── Models/AuditRecordModel.php    # ULID, created_at only, subject(): MorphTo
└── Concerns/
    ├── ResolvesActorName.php      # the actor's display name, or "Someone"
    ├── DescribesSubject.php       # model → the name used inside a sentence
    └── ListsChangedFields.php     # getChanges() → "Changed name and due date"
```

Auditing belongs to no domain, so it lives in `app/Libs/`. The facade is a deliberate
exception to the project's "avoid service-locator calls in new domain code" rule — it is the
first facade in the codebase, and it buys `AuditTrail::spy()` in tests.

### The table

`audit_records`: `id` (ULID, primary key), `type`, `title` (**text**), `description` (text,
null), `subject_type`, `subject_id`, `created_by` (FK to users, null on delete), `created_at`.
Index on `(subject_type, subject_id, id)`.

- `title` is `text`, not `string`: it embeds task, list and file names, all of which are
  `varchar(255)` themselves, so a long one would overflow the column — and, because auditing
  must never fail the action, that failure would be silent instead.
- `subject` is an ordinary polymorphic relation: `subject_type` stores the model class, like
  `comments.commentable_type`. No alias map, no enum.
- `subject_id` is a `string`: most keys are ULIDs, but `comments.id` is a bigint.
- There is no `updated_at`, no `updated_by`, no `meta`. A record is immutable, and everything
  a row needs fits in the four text fields plus the subject.
- The `id` is a ULID and doubles as the chronology: `id desc` is newest-first.

### Writing a record

```php
AuditTrail::capture(new TaskStatusChangedAuditRecord($task, $previousStatus, $task->status));
```

`AuditRecorder::capture()` reads `type()`, `title()`, `description()` and `subject()` off the
record, adds `created_by` from `auth()->id()`, generates the ULID and `created_at`, and writes
one row. Two properties matter:

- **it never throws.** Any failure is caught and logged through `Log::warning`. The record is
  read once, before the write, so even a throwing `type()` cannot escape;
- **it never poisons a caller's transaction.** The insert runs in a nested transaction, which
  Laravel compiles to a savepoint. On PostgreSQL a swallowed SQL error otherwise leaves the
  surrounding transaction aborted, and the business action would fail despite the `try`.

An unsaved subject, or a subject with an empty key, is stored as no subject at all rather than
as a half-filled reference.

Everything is synchronous. The text is rendered at the moment of the event, on purpose: a
queued job would re-read models and record the *new* name in an event about the old one, or
fail outright once the entity is gone.

### Emitting from a domain

Event classes live in `app/Domains/{Domain}/AuditRecords/{Event}AuditRecord.php`; the handler
that performs the action decides whether to record it. There is no automatic wrapper around
Actions.

Nested calls are silenced by a flag on the command, not by machinery:

```php
$this->createCommentHandler->handle(new CreateCommentCommand(
    commentable: $task,
    author: $command->author,
    content: "# Handoff\n\n{$command->resolution}",
    recordAudit: false,
));
```

`CreateCommentCommand` and `CreateProjectDocumentCommand` carry `recordAudit: bool = true`.
The three CLI workflow handlers pass `false` and record their own event; the markdown import
passes `false` and records nothing.

For `*.updated` events, the list of changed fields comes from `getChanges()` **after**
`update()` — WebApi handlers write every field, so only Eloquent knows what actually moved.
Each record class keeps a `FIELD_NAMES` whitelist of the columns its handler can write:

```php
protected const FIELD_NAMES = ['name' => 'name', 'status' => 'status', /* … */];
```

`ListsChangedFields::reportableColumns()` intersects the changes with that map, and the
handler skips the event when nothing is left. `updated_at`, `updated_by` and `status` cannot
leak into the sentence by construction — `status` has its own event.

Deletions build the record **before** `delete()` and capture **after** it: a deleted model
keeps its attributes in memory, and a delete that throws leaves no event behind.

### Reading

`GET /api/audit-records`, `auth:sanctum`, route name `audit-records.index`.

Paginated the way every other list in the project is (`{data, meta, links}`), with
`per_page` honoured. Sorting is always `id desc`; `sort_by` and `sort_order` are ignored,
because a feed is chronological by definition.

`AuditRecordResource` returns `id`, `type`, `title`, `description`, `created_at`, `subject`
and `actor`.

- `subject` is `{type, id}` or `null` as a whole. `subject.type` is the short key, derived
  mechanically from the stored class — `class_basename` minus the `Model` suffix, in
  snake_case — so no namespace reaches the frontend and no map has to be maintained;
- `actor` is always present, `null` when there is no author. It is not wrapped in
  `whenLoaded()`: a missing key and a null author are different contracts;
- the morph relation is never loaded. The resource reads `subject_type` and `subject_id`
  straight off the record, which makes an N+1 impossible and means a deleted entity cannot
  break the response. The only eager load is `createdBy`.

### Tests

`tests/Feature/Libs/AuditTrail/` covers the recorder; the emission is covered where the
action is, in `tests/Feature/Http/…` and `tests/Feature/CliApi/…`; the endpoint has
`tests/Feature/Http/AuditRecords/AuditRecordsIndexTest.php`, including a query-count test that
proves the eager load is doing its job.

## Frontend

### The entity

```txt
resources/js/entities/audit-record/
├── types/        AuditRecordDto, AuditRecordSubjectDto, AuditRecordFetchParams
├── api/          fetchAuditRecordsRequest
├── config/       AuditRecordQueryKey
├── queries/      useAuditRecordsQuery — one page
└── composables/  useAuditRecordFeed — the growing list
```

`AuditRecordDto.type` is a plain `string`, not a union of literals. A union would turn a new
backend event into a compile error, which is the opposite of "an unknown type still renders".
`subject.type` keeps a union of the four known keys but stays open-ended for the same reason.

`useAuditRecordFeed` merges pages into one `ref`, not into the query cache — the cache keeps
pages apart on purpose. Three details are load-bearing:

- **deduplication by `id`.** Offset pagination on a growing table repeats rows: a new event
  between two requests shifts everything down, and the last row of page 1 arrives again at the
  top of page 2;
- **sorting by `id` after the merge.** ULIDs are monotonic, so this is the same chronology the
  backend serves, and a page refetched later cannot append fresh events below older ones;
- **`loadMore()` advances only when `page === meta.current_page`.** Two clicks in the same
  tick both read the flags of the already-settled previous page — reactivity has not run yet —
  so guarding on `isPending` or `isFetching` lets the counter jump 1 → 3 and drop a page for
  good, since `loadMore` only ever counts upward.

`hasMore` comes from `meta`, never from the list length: after deduplication the length is
short by exactly the rows that were duplicated. It is also `false` while an error is showing,
so a failed page cannot look like the end of the feed.

### The widget

```txt
resources/js/widgets/activity-stream/
├── ui/ActivityStream.vue        states, day groups, "Load more"
├── ui/ActivityStreamItem.vue    one row and its expansion
├── config/activity-type.registry.ts
└── lib/group-by-day.ts
```

`HomePage.vue` only mounts the widget.

The registry maps every type to an icon, an accent and whether the event links anywhere.
Accents group by **action** — create, update, status, delete, talk, agent — not by domain: in
a mixed feed a creation and a deletion from different domains sit side by side, and the action
is what the eye picks up first. `resolveActivityType()` falls back to a neutral grey entry for
anything it has never seen.

A separate map turns `subject.type` into a route name (`task-details`,
`task-list-details`, `project-details`, `project-document-details`). A link is built only when
the type is linkable, the subject exists, and its type is in that map.

The row is a `<button>` with `aria-expanded` and `aria-controls`, so keyboard and screen
reader support come for free; the badge, the chevron and the avatar are `aria-hidden`, leaving
the sentence as the accessible name.

Day grouping compares each record only against the previous group, which is valid because
`capture()` stamps the ULID and `created_at` at the same instant. Dates go through
`shared/utils/date.util.ts`, so one unparseable timestamp degrades to "Unknown date" instead
of throwing mid-render.

### Adding a new event

1. Write `{Event}AuditRecord` in the domain that owns the action, using `ResolvesActorName`,
   `DescribesSubject` and — for an update — `ListsChangedFields`.
2. Call `AuditTrail::capture()` from the handler, after the write it describes.
3. If the handler is called by another handler that records its own event, add and pass
   `recordAudit: false`.
4. Add the type to `activity-type.registry.ts` with an icon, an accent and `linkable`.
   Skipping this step is not fatal — the row renders grey and without a link.
5. Cover it where the action is tested: one event per action, and none when nothing changed.
