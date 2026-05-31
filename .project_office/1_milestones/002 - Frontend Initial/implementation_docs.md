# Milestone 002 — Frontend Initial: Implementation

## Overview

This milestone establishes the foundational frontend architecture: app shell components,
layout system, navigation, auth store reorganization, app-level layout store, and base pages.

---

## Directory Structure

```
resources/js/
├── app/
│   ├── shell/
│   │   ├── index.ts                          # barrel: re-exports ui components + types
│   │   ├── types/
│   │   │   └── index.ts                      # AppLayoutName, SidebarNavItem, HeaderAction
│   │   └── ui/
│   │       ├── header/
│   │       │   └── AppHeader.vue
│   │       ├── navigation/
│   │       │   └── AppLeftNavigationSidebar.vue
│   │       └── layouts/
│   │           ├── DefaultLayout.vue
│   │           └── AuthLayout.vue
│   ├── stores/
│   │   ├── use.auth.store.ts                 # moved from stores/auth.store.ts
│   │   └── use.app-layout.store.ts
│   ├── router/
│   │   └── index.ts                          # updated imports + new routes + meta.title
│   └── App.vue                               # inline AppLayoutComponentMap
├── pages/
│   ├── login/LoginPage.vue
│   ├── home/HomePage.vue
│   ├── projects/ProjectsPage.vue
│   ├── tasks/TasksPage.vue
│   └── documents/DocumentsPage.vue
├── widgets/
│   └── user/
│       ├── index.ts
│       └── ui/UserProfilePopover.vue
└── shared/
    ├── components/
    │   └── button/
    │       ├── index.ts
    │       ├── button.types.ts
    │       ├── button.config.ts
    │       └── ui/IconButton.vue
    └── utils/
        └── string.util.ts
```

---

## Key Design Decisions

### Shell as a self-contained module

`app/shell/` groups all layout-level UI (header, sidebar, layouts) and their shared types
under one barrel export. Components outside `shell/` import from `@/app/shell`.

`DefaultLayout.vue` uses relative imports for `AppHeader` and `AppLeftNavigationSidebar`
to avoid a circular dependency (the barrel exports `DefaultLayout` itself).

### Layout system

`App.vue` holds an inline `AppLayoutComponentMap` keyed by `AppLayoutName` (`'default' | 'auth'`).
Route `meta.layout` selects which layout renders. This avoids a separate registry file for
what is currently only two layouts.

`AuthLayout.vue` is a minimal centered flex container for unauthenticated pages.
`DefaultLayout.vue` composes sidebar + header + main content slot.

### AppLeftNavigationSidebar — default slot

The sidebar provides a single unnamed default slot. The consumer (`DefaultLayout`) is
responsible for rendering additional sections (e.g., recent projects list with its heading).
This keeps the sidebar generic and avoids slot-name coupling.

Bottom buttons (Settings, Profile) are plain `<button>` elements intentionally —
they will be replaced by `RouterLink` in a future milestone.

### AppHeader — SplitButton + actions

`AppHeader` accepts an optional `actions: HeaderAction[]` prop. If actions are present,
it renders a PrimeVue `SplitButton`:
- the item with `is_primary: true` (or the first item) becomes the button label + click handler
- remaining items become the dropdown model

Action buttons appear before the bell, cog, and avatar icons.

### AppLayoutStore — page title + header actions

`use.app-layout.store.ts` is a Pinia setup store that:
- Reads `route.meta.title` as the default page title (no override needed for most pages)
- Allows pages to call `setPageTitle()` for dynamic titles
- Clears the title override on route change
- Syncs `document.title` via a watcher: `"<title> | <VITE_APP_NAME>"`
- Exposes `setHeaderActions` / `clearHeaderActions` for pages that need header action buttons

### Auth store relocation

`stores/auth.store.ts` was moved to `app/stores/use.auth.store.ts` to co-locate
app-level stores. Store ID `'auth'` is unchanged.

### UserProfilePopover — widget, not shell

`UserProfilePopover` lives in `widgets/user/` because it is a reusable widget with
its own data (user name/email) rather than a shell-level concern. It is a presentational
component: accepts `name: string`, `email: string` props, emits `logout`. The store
interaction happens in `AppHeader`.

The component exposes `toggle(event)` via `defineExpose` so `AppHeader` can open it
from an Avatar click.

### IconButton — shared component

`shared/components/button/` groups:
- `button.types.ts` — `ButtonSize` type
- `button.config.ts` — `ICON_SIZE_MAP`, `BUTTON_SIZE_MAP` (tailwind class maps per size)
- `ui/IconButton.vue` — wraps PrimeVue `Button` with `text` + `rounded` + iconify `Icon`

`ButtonProps` conflict with the custom `size` prop is handled via
`interface IconButtonProps extends /* @vue-ignore */ Omit<ButtonProps, 'size'>`.

### Pages convention

Each page lives in a subdirectory matching its route path:
`pages/<route-segment>/<PageName>Page.vue`. Placeholder pages render a minimal
`<div>` with the page name. `TasksPage.vue` demonstrates header action injection
via `useAppLayoutStore`.

### TypeScript — verbatimModuleSyntax

`tsconfig.app.json` explicitly sets `"verbatimModuleSyntax": true` (already inherited
from `@vue/tsconfig` but made visible for clarity). All type-only imports use `import type`.

---

## Route Meta

```ts
interface RouteMeta {
    requiresAuth?: boolean
    layout?: AppLayoutName  // 'default' | 'auth'
    title?: string
}
```

Routes defined:

| Name        | Path         | Layout    | Title      |
|-------------|--------------|-----------|------------|
| login       | /login       | auth      | Sign In    |
| home        | /            | default   | Home       |
| projects    | /projects    | default   | Projects   |
| tasks       | /tasks       | default   | Tasks      |
| documents   | /documents   | default   | Documents  |

---

## PrimeVue Notes

- `Password` component: use `fluid` prop (not `class="w-full"`) so the toggle icon
  stays inside the input border. Applying width via class targets the inner `<input>`,
  leaving the wrapper narrower than the icon.
- `Avatar`: size `"large"` used in `UserProfilePopover` for the profile display.
- `SplitButton`: `model` prop accepts `MenuItem[]`; the main button uses `label` + `@click`.
