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

Frontend unit tests run on vitest (`npm run test:unit`); specs sit next to the code as `*.spec.ts`
under `resources/js/`. Static checks and the exact commands are in the project profile.

Do not use `npm run format` or `npm run lint` as a broad automatic fix over unrelated files. Apply
focused corrections and preserve user changes.

## Playwright and visual verification

Playwright specs are part of the repository (`e2e/`, see `docs/testing.md`), but a run reseeds the
e2e database.

- Do not run Playwright or browser automation unless the user explicitly requests it.
- Do not claim visual verification from static checks.
- The user performs visual verification for UI changes.
- In the handoff, identify the affected screen or flow, expected interaction, and states that still
  need manual verification.

## Shared test infrastructure

Treat existing `phpunit.xml`, base `*TestCase.php` files, and migrations as protected shared
infrastructure. Do not rewrite or weaken them to force a passing result. A genuinely required
shared change is review-worthy by default; these files remain mechanically blocked until protection
is deliberately changed or the user makes the edit. New leaf tests and
new migration files remain allowed.

## Backend verification

The exact commands are in the project profile; run backend tooling through `php8.5`, because
`vendor/` is built for PHP >= 8.4 and the default `php` may resolve lower.

The test database contract is documented in `docs/testing.md`. Recreating it through
`./scripts/init_testing_pg_databases.sh` is a destructive testing-infrastructure action and requires
permission. Running migrations remains blocked, including for the testing environment.

Run Pint on the files you touched, not as a full-project formatting pass.

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
