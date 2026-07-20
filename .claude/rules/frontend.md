---
paths:
  - "resources/js/**"
---

# Frontend architecture

Vue 3, TypeScript, Vite, PrimeVue, Tailwind CSS, Pinia, and TanStack Vue Query. Frontend source
lives in `resources/js/` and follows a Feature-Sliced Design-inspired structure.

## Layer ownership

```text
resources/js/
|- app/       bootstrap, plugins, router, shell, global stores, and application styles
|- pages/     route-level composition
|- widgets/   substantial feature UI assembled for a specific use case
|- entities/  domain API, types, queries, mutations, composables, and configuration
`- shared/    entity-agnostic UI and utilities
```

- Pages compose widgets and entities and should remain thin.
- Widgets own feature-specific UI, supporting components, and local composables.
- Entities own server-facing API functions, TypeScript types, query keys, queries, mutations, and
  entity-level composables.
- Shared code must be genuinely entity-agnostic.
- Expose module APIs through `index.ts`; prefer public imports over reaching into another module's
  internals.

Dependencies should generally flow from app and pages toward widgets, entities, and shared code.
Do not move feature knowledge downward into Shared merely to avoid a local import.

## Server state and contracts

- Use TanStack Vue Query for server state, caching, loading state, invalidation, and mutations.
- Keep query keys centralized in the owning entity's config.
- Keep API requests and response types in the owning entity.
- Keep backend Resources and frontend TypeScript types aligned. Do not silently compensate for a
  backend contract mismatch in a component.
- Handle meaningful pending, error, empty, and disabled states where the interaction requires them.

Universal entities such as comment, tag, and attachment do not depend on their consumers.
Consumer-scoped queries and mutations belong to the consuming entity, even when this creates small
and explicit duplication.

## Component and composable conventions

Use `<script setup lang="ts">`. Keep this internal order unless a local dependency is clearer when
kept together:

1. imports;
2. types and interfaces;
3. component inputs or composable parameters;
4. composables, stores, router, and injected services;
5. reactive state;
6. computed values;
7. methods and event handlers;
8. watchers;
9. lifecycle hooks;
10. public API through `defineExpose` or `return`.

Use call signatures for `defineEmits`:

```ts
const emit = defineEmits<{
    (e: 'update', value: Item): void
    (e: 'delete', id: string): void
}>()
```

Do not introduce the shorthand tuple form in new or modified components.

## Libraries and reuse

- Check installed packages before building a custom primitive.
- Prefer PrimeVue components, VueUse composables, and established project wrappers where they fit.
- Adapt library components through props, slots, pass-through configuration, and focused styles.
- A wrapper or composition component is appropriate when it establishes a project contract or
  combines existing pieces; do not recreate library behavior without a concrete need.
- Never install a new dependency without user approval.

## Frontend verification boundary

Run formatting, linting, type checks, and builds proportionally as defined in `testing.md`.
Playwright and browser-based visual verification are not part of the automatic pipeline yet. The
user visually verifies UI changes; describe the affected interaction and any unverified states in
the handoff.
