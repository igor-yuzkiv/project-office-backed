---
paths:
  - "resources/js/**"
---

# Rule: Frontend architecture and conventions

Vue 3 + TypeScript + Vite. UI: PrimeVue + Tailwind CSS. Frontend source lives in
`resources/js/`.

## Architecture

Feature-Sliced Design inspired layered structure.

```
resources/js/
├── app/              # app-level setup
├── pages/            # route-level page components
├── widgets/          # complex self-contained UI blocks
├── entities/         # domain entity modules
└── shared/           # cross-cutting code
```

### Layer reference

**`app/`** — application bootstrap and shell.

```
app/
├── config/           # app-level config constants
├── plugins/          # Vue plugins (PrimeVue, Vue Query, Laravel Echo)
├── router/           # Vue Router setup
├── shell/            # layouts, header, navigation sidebar
└── stores/           # app-level Pinia stores (auth, layout)
```

**`pages/`** — one file per route. Thin: compose widgets and entities, no business logic.

**`widgets/`** — complex UI blocks with own composables and sub-components. Not reused
across entities.

```
widgets/{feature}/
├── ui/               # Vue components
├── composables/      # widget-local composables
└── index.ts          # public API
```

**`entities/`** — domain entity modules. Each entity owns its API layer, types, queries,
and mutations.

```
entities/{entity}/
├── api/              # API call functions
├── types/            # TypeScript types for the entity
├── queries/          # TanStack Vue Query query composables
├── mutations/        # TanStack Vue Query mutation composables
├── config/           # entity-level constants (query keys, route names, etc.)
└── index.ts          # public API (re-exports)
```

**`shared/`** — truly reusable, entity-agnostic code.

```
shared/
├── api/              # HTTP client, error types
├── components/       # generic UI components (buttons, etc.)
├── composables/      # generic composables (toast, confirm dialog, etc.)
├── types/            # shared TypeScript types (pagination, result, etc.)
└── utils/            # pure utility functions
```

### Cross-cutting entities

Universal entities (`comment`, `tag`, `attachment`) never reference their consumers — a
universal entity module operates on the entity by its own ID only
(`deleteCommentRequest(commentId)`, `updateCommentRequest(commentId, data)`).

- Operations scoped to a consuming entity live in that entity's module:
  `fetchTaskCommentsRequest` / `useTaskCommentsQuery` / `useCreateTaskCommentMutation` in
  `entities/task/`, not `entities/comment/`.
- When another entity needs comments, it gets its own set (`useProjectCommentsQuery`).
  Duplication across consumers is preferred over a shared abstraction.

## Vue Emits

Always use the call-signature form for `defineEmits`:

```ts
const emit = defineEmits<{
    (e: 'update', value: { commentId: string; content: string }): void
    (e: 'delete', commentId: string): void
}>()
```

Do not use the shorthand object/tuple syntax (`{ update: [value: ...] }`).

## `<script setup>` and composable structure

Use a consistent internal order in both Vue `<script setup>` blocks and composables:

1. Imports
2. Types and interfaces
3. Inputs:
    - components — `defineProps`, `defineEmits`, `defineModel`
    - composables — function parameters
4. Composables, stores, router, injected services
5. Reactive state: `ref`, `reactive`, constants related to state
6. Computed values
7. Methods and event handlers
8. Watchers
9. Lifecycle hooks
10. Public API (placed last):
    - components — `defineExpose`
    - composables — `return`

Keep lifecycle hooks near the end unless there is a strong reason to place them near
related logic.

## Component and library usage

Prefer components and utilities from already-installed packages over writing custom
implementations.

- Always check installed packages first (PrimeVue, VueUse, etc.) before implementing
  anything from scratch.
- Never install new packages independently — propose them if needed and wait for
  confirmation.
- Customize existing components to match the design (via props, slots, CSS overrides,
  PrimeVue `pt` API) rather than recreating them.
- Create a custom component only as a last resort, when no installed package covers the
  need.

This restriction does not apply to:

- Wrapper components that adapt a library component to project conventions.
- Composition components that combine multiple existing components into a reusable block.

## Key libraries

| Library | Purpose |
| --- | --- |
| PrimeVue | UI component library |
| Tailwind CSS | Utility-first styling |
| TanStack Vue Query | Server state management (queries, mutations, caching) |
| Pinia | Client state management |
| Zod | Schema validation and type inference |
| Vue Router | Client-side routing |
| VueUse | Composable utilities |
| Axios | HTTP client |

## Tooling

| Task | Command |
| --- | --- |
| Format code | `npm run format` |
| Check formatting | `npm run format:check` |
| Lint and auto-fix | `npm run lint` |
| Check linting | `npm run lint:check` |
| Type check | `npm run types:check` |

Run `npm run format` and `npm run lint` before considering frontend work complete.
Run `npm run types:check` after changes that touch types or interfaces.
Run `npm run build` only when changes affect bundling, routing, or Vite config.

Do not run browser-based verification — the user verifies the UI manually.
