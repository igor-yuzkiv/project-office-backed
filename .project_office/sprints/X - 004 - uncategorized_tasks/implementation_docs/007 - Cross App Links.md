# 007 - Cross App Links: Project From Task Details

## What was implemented

Made the Project field a clickable `RouterLink` in two places on the Task Details page. Also extracted a shared `.app-link` CSS class to avoid duplicating the link style across components.

## Changed files

| File | Change |
|------|--------|
| `resources/js/pages/tasks/details/TaskDetailsPage.vue` | `<span>` for project name in header replaced with `<RouterLink to="project-details">` |
| `resources/js/pages/tasks/details/tabs/TaskOverviewPage.vue` | Project field changed from `:value` prop to default slot with `<RouterLink>` |
| `resources/js/app/style/base.css` | Added `.app-link { @apply text-primary-500 hover:underline; }` |
| `resources/js/widgets/tasks/tasks-table/ui/TasksTable.vue` | `text-primary-500 hover:underline` → `app-link` (+ kept `block truncate`) |
| `resources/js/widgets/attachments/attachments-table/ui/AttachmentsTable.vue` | `text-primary-500 hover:underline` → `app-link` |

## Decisions

- `app-link` class introduced to remove duplication of `text-primary-500 hover:underline` across 4 files — no separate `EntityLink` component introduced per spec.
- `text-sm` in the header link kept as a local class alongside `app-link` (context-specific size).
- `block truncate` in `TasksTable` kept alongside `app-link` (column width constraint).
- `ProjectIcon` left as a non-clickable static element per spec.
- Task List fields unchanged in both locations — no destination route exists.
- `widgets/tasks/tasks-table/`, `widgets/task-list/lists-table/`, `widgets/attachments/attachments-table/` moved to `views/table/` under each widget as part of the same session.

## Validation

- `npm run format` — passed
- `npm run lint` — passed
- `npm run types:check` — passed
