# Document annotations

Block-level comments on a project document. Annotations live in their own view,
`/#/project-documents/{id}/annotations`, reachable from the documentation workspace through
the `Annotation mode` header action. The document is rendered as a sheet on a canvas: clicking a
block selects it, and a chat-style composer docks below the document to write a comment
against that selection. The sidebar on the right lists every annotation in document order;
`Edit` scrolls to the annotated block, selects it, and loads the comment back into the
composer.

An annotation belongs to a block of the *rendered* document, not to a character range of
the markdown source. Editing the document therefore does not move annotations by itself:
each one is re-resolved against the freshly rendered page every time the view opens.

## Entities

- `App\Domains\Annotation\Models\AnnotationModel` (table `annotations`) — polymorphic
  through `annotatable_type`/`annotatable_id`, the same shape as `CommentModel`.
- `App\Infrastructure\Models\Contracts\Annotatable` — one method, `annotations()`.
  `ProjectDocumentModel` is the only implementer today.
- Actions in `App\Domains\Annotation\Actions\` — `CreateAnnotation`, `UpdateAnnotation`,
  `DeleteAnnotation`, each a Command plus a Handler.

Annotations write **no audit trail**. Unlike a comment, an annotation is review noise
rather than an event in the life of the project.

There is **no authorization** on annotations: any authenticated user may edit or delete
any of them. The SPA hides `Edit`, `Delete` and `Re-anchor` on other people's cards by
comparing `author.id`, but that is presentation only. The API enforces nothing.

## WebApi

All four routes sit behind `auth:sanctum`. The CLI API does not expose annotations.

```
GET    /api/project-documents/{project_document}/annotations
POST   /api/project-documents/{project_document}/annotations
PATCH  /api/annotations/{annotation}
DELETE /api/annotations/{annotation}
```

`GET` returns `{ "data": [...] }`, unpaginated, oldest first — the sidebar reads the whole
list and orders it itself. `POST` and `PATCH` take the same body, and `PATCH` writes the
whole object: `content` is required even when only the anchor changes.

## The anchor

The rendered preview is the only thing an annotation can point at, and it gives us one
usable coordinate: `data-line`, which `md-editor-v3` puts on rendered blocks. That alone is
not enough: nested blocks share one value, and fenced code blocks have none. An anchor
therefore combines six fields:

| Field | Meaning |
|---|---|
| `version` | Always `1`. Other values are rejected. |
| `line` | The block's `data-line`, or the nearest ancestor's; `null` when neither has one. |
| `tag` | Lowercase tag name: `p`, `h1`…`h6`, `li`, `blockquote`, `pre`, `table`. |
| `ordinal` | Position among blocks sharing the same `(line, tag)` pair, from zero. |
| `index` | Position among all candidate blocks in the document, from zero. |
| `text_hash` | FNV-1a 32-bit of the normalized block text, lowercase hex, always 8 characters. |

Normalization is `NFC`, then every run of whitespace collapsed to one space, then trimmed.
The hash is computed over **UTF-16 code units**, which matters if anything server-side ever
recomputes it: iterating UTF-8 bytes would give a different value for non-ASCII text.

Alongside the anchor an annotation stores `text_snapshot` — the same normalized text,
truncated to 300 characters.

The backend never interprets any of this. It accepts exactly the six keys above, rejects
any other, stores the result and hands it back.

**Code blocks have `line: null`** because `md-editor-v3` renders fenced code through a
highlighter that returns finished `<pre>` markup, and markdown-it drops the token's
attributes on the way. Such an annotation can still be found by hash, but never by
position.

**The annotated block is the deepest candidate.** In a tight list that is the `<li>`; in a
loose one the paragraph inside it. Converting a list from tight to loose therefore changes
the target's tag and orphans that list's annotations. This is accepted: re-anchoring cures
it.

## Resolving an anchor

Three attempts, in order; the first that succeeds wins, and its kind is reported to the UI.

1. **`exact`** — same `tag`, `line`, `ordinal` and `text_hash`. Nothing changed.
2. **`hash`** — same `tag` and `text_hash`, choosing the candidate whose `index` is closest
   to the stored one (the smaller index on a tie). This is the case where text moved.
   Skipped when the snapshot is empty, or every empty block would match every other one.
3. **`position`** — same `tag`, `line` and `ordinal`, **and** the block's current text is
   similar enough to the stored snapshot: a Dice coefficient over bigrams of at least
   `0.5`. Both sides are truncated the same way before comparing, so the threshold means
   the same thing at any block length. This is the case where the text was edited in place,
   and the UI marks such a card `Block content changed`.

Without the similarity check on the third attempt, deleting a paragraph would silently hand
its annotation to whatever block moved into its place — a confidently wrong answer instead
of an honest failure.

When all three fail the annotation is **orphaned**: it stays in the sidebar labelled
`Block not found`, clicking it does not scroll anywhere, and it offers `Re-anchor`.

## Re-anchoring

Re-anchoring is an ordinary update. The user picks a block, the SPA computes a fresh anchor
and snapshot from it, and `PATCH` writes them with the unchanged `content`. It is available
for any annotation the user owns, not only orphaned ones — a card marked
`Block content changed` is often worth re-pointing too.

## Where the code lives

| Piece | Path |
|---|---|
| Domain | `app/Domains/Annotation/` |
| WebApi | `app/Http/WebApi/Controllers/Annotation/`, `.../ProjectDocuments/ProjectDocumentAnnotationsController.php` |
| Anchor functions | `resources/js/shared/utils/markdown-anchor.util.ts` (+ `.dom.util.ts`) |
| Data layer | `resources/js/entities/annotation/`, `resources/js/entities/project-document/` |
| UI | `resources/js/widgets/project-documents/annotation-mode/` |
| Unit tests | `resources/js/shared/utils/markdown-anchor.util.spec.ts` (`npx vitest run`) |
| E2E | `e2e/project-documents/annotations.smoke.spec.ts` |

## Limits of this version

No resolved/approved states, no Open/Resolved tabs, no search or filtering in the sidebar,
no annotations on a selected range of text, no CLI API, and no carrying annotations across
document revisions. Concurrent editing is last-write-wins: if someone else changes the
document while annotation mode is open, anchors resolve against the stale content until the
page is reloaded.
