---
paths:
  - "app/**"
  - "tests/**"
  - "routes/**"
  - "database/**"
  - "resources/js/**"
  - "e2e/**"
  - "phpunit.xml"
  - "phpstan.neon"
  - "pint.json"
  - "package.json"
  - "playwright.config.ts"
---

# Testing and verification

Testing is part of implementation, not a separate approval phase.

## Default policy

- Add or update tests for bug fixes and business-behavior changes by default.
- A bug fix should include a regression test that fails for the original defect when practical.
- Omit a test only when it would add no meaningful confidence or its cost is disproportionate to
  the risk. Explain the reason in the handoff.
- Assert intended observable behavior, not incidental implementation details.

## Backend tests

The backend suite uses Pest with Laravel's testing helpers and a dedicated PostgreSQL database.

- Inspect the nearest test and `tests/Pest.php` before adding coverage.
- Prefer focused Feature tests for API contracts, authentication, validation, persistence, and
  Handler wiring.
- Use Unit tests for isolated services, value objects, filters, and pure behavior that does not
  require Laravel integration.
- Exercise business rules at the layer that owns them; avoid duplicating the same matrix through
  Handler, controller, and end-to-end API tests.
- Use descriptive English `it(...)` names and named datasets for real behavior matrices.
- Keep tests independent and order-agnostic. Use `RefreshDatabase` where persistence is involved.
- Fake queues, events, storage, network calls, time, and other external boundaries as appropriate.
- Do not test standard Laravel or Eloquent behavior without project-specific logic.

## Frontend verification

There is no integrated frontend unit-test suite yet. Use proportional static verification:

1. `npm run format:check` for formatting-sensitive changes.
2. `npm run lint:check` for changed TypeScript, Vue, or E2E source.
3. `npm run types:check` for TypeScript, Vue contracts, props, emits, queries, and mutations.
4. `npm run build` for routing, Vite, application composition, or bundling-sensitive changes, or
   when narrower checks do not provide enough confidence.

Do not use `npm run format` or `npm run lint` as a broad automatic fix over unrelated files. Apply
focused corrections and preserve user changes.

## Playwright and visual verification

Playwright is installed but is not yet integrated into the normal development pipeline.

- Do not run Playwright or browser automation unless the user explicitly requests it.
- Do not claim visual verification from static checks.
- The user performs visual verification for UI changes.
- In the handoff, identify the affected screen or flow, expected interaction, and states that still
  need manual verification.

## Shared test infrastructure

Treat existing `phpunit.xml`, base `*TestCase.php` files, and migrations as protected shared
infrastructure. Do not rewrite or weaken them to force a passing result. A genuinely required
shared change must be proposed through the Controlled pipeline; these files remain mechanically
blocked until protection is deliberately changed or the user makes the edit. New leaf tests and
new migration files remain allowed.

## Backend verification ladder

Use the narrowest sequence that provides adequate confidence:

1. `php -l <changed-file>` for a fast PHP syntax check when useful.
2. `php artisan test <test-file>` or `php artisan test --filter=<TestName>` for targeted behavior.
3. `./vendor/bin/phpstan analyse` for affected backend work.
4. A broader related suite when shared behavior or risk makes targeted checks insufficient.
5. `php artisan test` when the change is broad or foundational, targeted verification is
   insufficient, or the user explicitly requests the full suite.

The test database contract is documented in `docs/testing.md`. Recreating it through
`./scripts/init_testing_pg_databases.sh` is a destructive testing-infrastructure action and requires
permission. Running migrations remains blocked, including for the testing environment.

Pint runs automatically on edited PHP files under `app/` through the PostToolUse hook; do not run a
redundant full-project formatting pass.

## Handling failures

- Determine whether a failure comes from the change, pre-existing repository state, or the
  environment.
- Fix in-scope regressions autonomously and rerun affected checks.
- Do not suppress, skip, loosen, or delete a valid failing test.
- Report unrelated or externally blocked failures with the command and relevant evidence.
- Escalate when resolving a failure would expand scope or require a product, contract, or UI
  decision.

## Handoff evidence

List tests added or updated, exact commands run, their results, relevant checks not run and why,
known coverage limitations, and any required manual visual verification.
