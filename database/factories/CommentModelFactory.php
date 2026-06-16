<?php

namespace Database\Factories;

use App\Domains\Comment\Models\CommentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentModel>
 */
class CommentModelFactory extends Factory
{
    protected $model = CommentModel::class;

    private static array $bodies = [
        <<<'MD'
I reviewed the current onboarding flow with the design team. A few key observations:

- The step 3 CTA is not prominent enough on smaller viewports
- Copy on step 5 uses technical jargon — needs plain English rewrite
- Progress indicator styling is inconsistent with the rest of the design system

Tagging @Wei Zhang on the API dependency for step 4. Please confirm the endpoint is stable before we ship.
MD,
        <<<'MD'
Endpoint confirmed stable. Here's a quick summary.

Returns a session token. Response time is under 120ms in staging. No breaking changes planned until Q2 2026.
MD,
        <<<'MD'
> Progress indicator styling is inconsistent with the rest of the design system

Fixed in the Figma file — updated to use DS tokens. Handoff ready. Linked updated frames in the Attachments tab.
MD,
        <<<'MD'
## Release Checklist

Before we ship this, make sure all of the following are done:

- [x] API endpoint reviewed and confirmed stable
- [x] Figma handoff updated with DS tokens
- [ ] Accessibility audit passed (WCAG 2.1 AA)
- [ ] QA regression suite passed in all 4 locales
- [ ] Content team sign-off on EN copy
MD,
        <<<'MD'
### Environment Comparison

Here's a breakdown of response times across environments:

| Environment | p50   | p95    | p99    |
|-------------|-------|--------|--------|
| Local       | 12ms  | 28ms   | 45ms   |
| Staging     | 38ms  | 91ms   | 118ms  |
| Production  | 41ms  | 98ms   | 124ms  |

All within the agreed SLA of **120ms at p95**. No action needed.
MD,
        <<<'MD'
### Step 4 — Role Assignment UI

Outlined the expected behavior for the admin-only role assignment screen:

1. Admin opens the **Team Members** panel
2. Selects one or more users from the list
3. Picks a role from the dropdown: `viewer`, `editor`, `admin`
4. Confirms with **Save**

> Only users with the `admin` role should see this panel. Hide it entirely for `editor` and `viewer`.

Non-admin users hitting the endpoint directly should get a `403 Forbidden`.
MD,
        <<<'MD'
Ran a quick accessibility pass on the current flow. Summary:

**Passed ✓**
- Colour contrast on all text elements (4.5:1 minimum met)
- Focus indicators visible on all interactive elements
- ARIA labels present on icon-only buttons

**Failed ✗**
- Step indicator lacks `aria-current="step"` on the active step
- Modal close button missing `aria-label`
- Progress bar has no `role="progressbar"` or `aria-valuenow`

Raised these as blocking issues — need fixes before QA sign-off.
MD,
        <<<'MD'
Left a few inline comments on the PR. Main concern is the retry logic in the sync handler — if the external call fails after the local write, we end up in a half-committed state with no rollback.

Suggest wrapping the whole thing in a DB transaction and moving the external call to a queued job dispatched on `afterCommit`.
MD,
        <<<'MD'
Confirmed with the content team — they're fine with the current EN copy for the tooltip. No changes needed there.

Still waiting on the FR and DE translations. Expected back by EOD Thursday.
MD,
        <<<'MD'
Reproduced the reported issue on staging. Steps:

1. Log in as a non-admin user
2. Navigate to **Settings → Integrations**
3. Click **Connect** on any provider

Expected: redirect to OAuth flow.
Actual: blank screen with a console error — `TypeError: Cannot read properties of undefined (reading 'url')`.

Root cause looks like a missing null-check before accessing `integration.oauth.url`. Fix should be straightforward.
MD,
        <<<'MD'
Performance numbers after the latest cache layer change:

- Cold cache: **340ms** → **290ms** (p95)
- Warm cache: **41ms** → **18ms** (p95)
- Error rate: unchanged at 0.02%

Looks good. Ready to promote to production after QA sign-off.
MD,
        <<<'MD'
One thing to flag before we close this out — the migration adds a `NOT NULL` column to `user_preferences` without a default. This will fail on tables with existing rows.

Two options:
1. Add a default value in the migration
2. Run a backfill before applying the constraint

I'd go with option 1 for simplicity. Happy to update if there's a reason to prefer option 2.
MD,
    ];

    public function definition(): array
    {
        return [
            'content' => fake()->randomElement(self::$bodies),
        ];
    }
}
