# 005 - Create Project Details Page

## What Was Implemented

- Route-level `ProjectDetailsPage.vue` that loads a project via the existing `ProjectsController::show` endpoint.
- Project header with a square blue avatar (shows prefix initials) and project name/prefix.
- Read-only details grid displaying: `name`, `prefix`, `created_by`, `updated_by`, `created_at`, `updated_at`.
- PrimeVue `Tabs` section with five tabs: `Task Lists`, `Tasks`, `Issues`, `Attachments`, `Documentation` — each with `Not implemented` placeholder content.
- Shared `DisplayField` component (`label` + `value`) at `resources/js/shared/components/display/ui/DisplayField.vue`.
- `DisplayDate` refactored to support optional `label` prop (backward-compatible — existing usages without label unchanged).
- `UserOverviewDto = Pick<IUser, 'id' | 'name'>` added to the user entity types, following the same pattern as `ProjectOverviewDto`.
- `IProject` extended with `created_by?: UserOverviewDto`, `updated_by?: UserOverviewDto`, `created_at: string`, `updated_at: string`.
- `useProjectQuery` composable added for fetching a single project by id.
- `ProjectQueryKey.detail` added to the project query key config.
- Navigation sidebar highlights the **Projects** item when on the `project-details` route — via optional `activeFor` array on `SidebarNavItem`.
- Projects table rows are clickable and navigate to `project-details`; the `⋯` menu button uses `@click.stop` to avoid conflicting with row navigation.

## Changed Files

| File | Change |
|---|---|
| `resources/js/pages/projects/ProjectDetailsPage.vue` | Full implementation |
| `resources/js/pages/projects/ProjectsPage.vue` | Row click navigation |
| `resources/js/entities/project/types/project.types.ts` | Extended `IProject`, imported `UserOverviewDto` |
| `resources/js/entities/project/queries/use.project.query.ts` | New single-project query composable |
| `resources/js/entities/project/queries/index.ts` | Exported `useProjectQuery` |
| `resources/js/entities/project/config/index.ts` | Added `detail` query key |
| `resources/js/entities/user/types/user.types.ts` | Added `UserOverviewDto` |
| `resources/js/shared/components/display/ui/DisplayField.vue` | New component |
| `resources/js/shared/components/display/ui/DisplayDate.vue` | Added optional `label` prop |
| `resources/js/shared/components/display/index.ts` | Exported `DisplayField` |
| `resources/js/app/shell/types/index.ts` | Added `activeFor` to `SidebarNavItem` |
| `resources/js/app/shell/ui/layouts/DefaultLayout.vue` | Added `activeFor: ['project-details']` to projects nav item |
| `resources/js/app/shell/ui/navigation/AppLeftNavigationSidebar.vue` | Updated active class condition |

## Key Decisions

- `UserOverviewDto` placed in the user entity (not inline in project types) — consistent with `ProjectOverviewDto` pattern.
- `DisplayDate` label rendering uses `<div>` wrapper only when label is present, preserving `<span>` output for existing usages.
- Navigation active state uses `activeFor?: string[]` on `SidebarNavItem` — extensible without changing the base check logic.
- Row click navigation stops propagation on the `⋯` button to prevent double-trigger.

## Checks Run

- `npm run format` — passed
- `npm run types:check` — passed

## Notes For Next Agent

- Tabs are placeholders; content will be implemented in future tasks.
- No backend changes were made — the existing `ProjectsController::show` with `createdBy`/`updatedBy` eager loading was sufficient.
