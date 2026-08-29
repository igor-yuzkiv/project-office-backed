# Document versions and inline editing

How a document's content is written and managed from the workspace's `Content` tab. The data
model — documents, versions, the effective version, the tree — is in `project-documents.md`;
block-level comments are in `document-annotations.md`. This document covers what sits on top:
the version endpoints, the editor with its autosave, the page's three modes, and the sidebar.

Editing happens on the viewing page. There is no separate editing route: the old
`/projects/{project}/documentation/{document}/edit` page was removed and answers with the
router's own not-found page.

## Versions in the WebApi

All routes sit behind `auth:sanctum`. Content and a version's own fields are written by
**two endpoints**, on purpose: the content write is what autosave hammers, and the
fields endpoint is free to grow without touching it.

```
GET    /api/project-documents/{project_document}/versions                    list, numbering order
POST   /api/project-documents/{project_document}/versions                    create
PUT    /api/project-documents/{project_document}/versions/{version}          write content
PUT    /api/project-document-versions/{project_document_version}             write the version's fields
DELETE /api/project-document-versions/{project_document_version}             delete
PUT    /api/project-documents/{project_document}/primary-version             pin / unpin the primary
```

- `POST` takes `label` (nullable) and `copy_content_from_version_id` (nullable, must belong to
  the document). The new version gets the next number; the response carries it.
- The content `PUT` takes `{ "content": string | null }`; the key is required (`present`), the
  value may be null. The version is resolved **through the document** (scoped route binding), so
  a version of another document is `404`, never written.
- The fields `PUT` takes `{ "label": string | null }` — every field the version owns, written
  whole, as the WebApi does everywhere. Today that is one field.
- Both writes answer with the version (`ProjectDocumentVersionResource`: `id`,
  `version_number`, `label`, `content`, `is_primary`, `author`, timestamps).
- The primary-version `PUT` takes `{ "version_id": string | null }`; null returns the document
  to following its newest version.

The bulk write that saved every version in one request is gone with the editing page.

### Audit trail and the document's timestamps

Every version write goes through the activity feed as `project_document_version.created`,
`.updated`, `.deleted` or `.primary_changed`, with the **document** as the subject. An `.updated`
record is written only when something changed — a write that sends the same content or
the same label back produces nothing, and does not touch the document either.

When content or a field did change, the document is `touch()`ed, so its `updated_at` and
`updated_by` follow the version rows where the text lives. Both version handlers follow this
rule; the CLI's `WriteProjectDocumentContentHandler` predates it and still touches
unconditionally.

## The editor and autosave

`resources/js/widgets/project-documents/content-editor/` holds the two pieces:

- `DocumentContentEditor.vue` — the same white card the reader sees, filled with
  `MarkdownEditor` instead of the rendered text. The toolbar is the default one minus `preview`,
  `previewOnly`, `catalog` and `fullscreen`; images pasted or picked go to the document's
  attachments (`role = project_document.content`) and come back as URLs.
- `useDocumentContentAutosave({ documentId, versionId, serverContent })` — the buffer between
  what is typed and what the server has. It returns `value`, `status`, `lastSavedAt`, `isDirty`,
  `flush()` and `cancel()`.

The contract of the buffer:

- `value` reads the draft when there is one and the server's content otherwise, so a background
  refetch never overwrites what is being typed. Writing `value` puts the text in the draft and
  restarts a **1500 ms** timer; typing the server's exact text back clears the draft.
- Drafts are kept **per version**, and each remembers the **document** it was typed into. The
  page is reused when the reader moves to another document that is already cached, so by the time
  a draft is written its document may no longer be the open one.
- A successful write **patches** that version in the `documentVersions(documentId)` query cache
  instead of invalidating it: the list carries every version's full content, and refetching all of
  it on every pause is the cost invalidation would have. The list and the document detail are
  invalidated once, when the reader leaves Edit.
- One write per version at a time; a write is repeated while the text keeps changing under it.
  `flush()` writes **every** pending draft, on any version of any document, and resolves `true`
  only when all of them landed. `isDirty` is true while any draft exists.
- `cancel()` drops the open version's draft without writing — for a version that no longer
  exists to be written to.
- `status` and `lastSavedAt` describe the **open** version only and start over when it changes;
  writes of other versions stay quiet. The global progress bar (`loadingStore.progressLoading`)
  stays on until the last write lands.

### When a write is forced

Besides the timer, the page (`DocumentContentPage.vue`) flushes on every event that could lose
the text:

| Trigger | On failure |
|---|---|
| leaving Edit for View or Annotate | the switch happens; `Not saved`, text stays in the buffer |
| switching the open version | the switch happens; the draft survives and is written again when the version is opened or on the next flush |
| switching the document | same |
| any route change, the page's own tabs included (`onBeforeRouteLeave`) | **navigation is cancelled**; the reader stays with `Not saved` |
| closing or reloading the tab (`beforeunload`) | the browser's own prompt, when anything is dirty |
| unmount | best effort — a consequence of the rows above |

Switching the sidebar tab forces nothing. `Ctrl/Cmd+S` inside the editor and the `Save` button
next to the status write at once; the shortcut is md-editor-v3's own, so the browser's dialog
never opens. There are no toasts on success.

## Modes

The toolbar above the sheet holds a three-way switch, `View · Edit · Annotate`. The mode is page
state: it starts at `View`, resets to `View` when the document changes, and is never in the URL.

| Mode | Sheet | Picking a block | Sidebar annotations |
|---|---|---|---|
| View | rendered markdown, table of contents card | no | listed, anchored, read-only |
| Edit | the editor, no table of contents | no | a plain list straight from the query: no anchors, no actions |
| Annotate | rendered markdown, table of contents card | yes | editable |

Any open version can be edited, not only the effective one; the indicator to the left of the
switch says which one is on screen.

Leaving Edit remounts the sheet, so the canvas scroll offset is stored on the way out and put
back once the rendered blocks arrive. Known limit: the toolbar sits inside the scrolling canvas,
so a reader who scrolled down has to scroll up to reach the switch — the restored offset is the
one at the moment of the click.

## Sidebar

`shared/components/side-tabs/` (`SideTabs` + `SideTab`) draws the right-hand column: a permanent
strip (`w-11`) with a collapse button and one icon per tab, the column with the active tab's
content, and — while the column is hidden — a drawer inside the workspace row for the tab that
was clicked. No hover behaviour; a click outside the drawer closes it, except clicks inside the
PrimeVue overlays it opened (menus, dialogs). A tab's content is mounted only while it is active
and visible, so an idle tab runs no queries.

Collapsed state and the chosen tab persist in `localStorage` under `docs:sidebar:collapsed` and
`docs:sidebar:tab` (`useTabbedSidePanel('docs:sidebar')`). The existing `SidePanel` (hover peek,
one panel) is untouched and still serves the document tree and the other pages.

The document page registers two tabs:

- **Versions** — `DocumentVersionPanel`: open a version, create one (the dialog flushes the
  editor first, because copying reads the source on the server), rename it through the row menu,
  delete it (deleting the open version drops its draft after the delete succeeds; deleting the
  last one returns the page to the empty state), pin or unpin the primary.
  `useDocumentVersionActions` is the thin composable behind it — no buffer of its own.
- **Annotations** — `AnnotationPanel`, see `document-annotations.md`.

## The document's own fields

Title, status and tags are edited in `ProjectDocumentUpsertDialog`
(`widgets/project-documents/upsert-dialog/`), the same dialog the tree creates documents with;
the header's `Edit` opens it on the current document, with the parent shown read-only. Status is
chosen at creation and defaults to what the backend would assign (`draft`); tags travel in the
same request. There is no autosave for these fields.

## Where the code lives

| Piece | Path |
|---|---|
| Version actions | `app/Domains/ProjectDocument/Actions/Version/` — `CreateProjectDocumentVersion`, `UpdateProjectDocumentVersionContent`, `UpdateProjectDocumentVersion`, `DeleteProjectDocumentVersion`, `SetProjectDocumentPrimaryVersion` |
| WebApi | `app/Http/WebApi/Controllers/ProjectDocuments/ProjectDocumentVersionsController.php`, requests under `app/Http/WebApi/Requests/ProjectDocuments/` |
| Data layer | `resources/js/entities/project-document/` (api, queries, mutations, types) |
| Canvas and card | `resources/js/shared/components/document-canvas/`, `content-card/`, `document-sheet/` (the reader) |
| Editor and autosave | `resources/js/widgets/project-documents/content-editor/` |
| Versions | `resources/js/widgets/project-documents/versions/` |
| Sidebar | `resources/js/shared/components/side-tabs/`, `resources/js/shared/composables/use.tabbed-side-panel.ts` |
| Page | `resources/js/pages/documentation/workspace/tabs/DocumentContentPage.vue` |
| Feature tests | `tests/Feature/Http/ProjectDocuments/ProjectDocumentVersionsTest.php`, `ProjectDocumentsCrudTest.php` |
| E2E | `e2e/project-documents/autosave.spec.ts`, `modes.spec.ts`, `sidebar.spec.ts` — see `testing.md` |

## Deliberately not there

- No concurrent-editing protection: two people writing the same version is last-write-wins.
- No carrying of unsaved drafts across a reload; the browser prompt is the only guard.
- No sticky toolbar, no narrow-screen layout (the sidebar collapses to its strip).
- The CLI API still writes content through its own `WriteProjectDocumentContentHandler` (the
  effective version, or version 1 when there is none); it does not expose versions.
