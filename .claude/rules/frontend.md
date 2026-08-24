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
- Read models live in Entities too — `dashboard` and `audit-trail` serve a screen rather than a
  domain object, but they hold what any entity slice holds: api, types, query keys, queries,
  composables.
- Slices of the same layer do not import one another, with one exception: a read model may import
  the types of the entities it reports on, because that is what it reports. The dependency runs one
  way — an entity never imports a read model.
- Shared code must be genuinely entity-agnostic, which is not the same as being a primitive.
  Opinionated components that establish a project contract live here too — `EntityTableView`
  renders its own empty state and paginator, `DataPanel` decides that an error means a `Try again`
  button. What keeps them in Shared is that they name no entity and reach no server.
- Expose module APIs through `index.ts`; prefer public imports over reaching into another module's
  internals.

Dependencies should generally flow from app and pages toward widgets, entities, and shared code. Do
not move feature knowledge downward into Shared merely to avoid a local import.

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

In template event handlers, bind a method reference instead of an inline call when the handler
takes no arguments: `@click="moveDialog.open"`, not `@click="moveDialog.open()"`. A bare reference
receives the event as an argument, so keep the explicit call when the method has optional
parameters the event object could fill (`open(payload?)`), and when real arguments are passed the
inline call is the only form.

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
