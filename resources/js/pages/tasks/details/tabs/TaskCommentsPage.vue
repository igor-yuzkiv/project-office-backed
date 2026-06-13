<script setup lang="ts">
import { ref } from 'vue'
import Divider from 'primevue/divider'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { MarkdownEditor, MarkdownPreview } from '@/shared/components/md-editor'

interface CommentItem {
    id: number
    author: string
    role: string
    date: string
    body: string
}

const newComment = ref('')

const comments: CommentItem[] = [
    {
        id: 1,
        author: 'Sarah Mitchell',
        role: 'Product Manager',
        date: 'Nov 28, 2025 · 10:14 AM',
        body: `I reviewed the current onboarding flow with the design team. A few key observations:
![](https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Test-Logo.svg/960px-Test-Logo.svg.png?_=20150906031702)
- The step 3 CTA is not prominent enough on smaller viewports
- Copy on step 5 uses technical jargon — needs plain English rewrite
- Progress indicator styling is inconsistent with the rest of the design system

Tagging @Wei Zhang on the API dependency for step 4. Please confirm the endpoint is stable before we ship.`,
    },
    {
        id: 2,
        author: 'Wei Zhang',
        role: 'Backend Engineer',
        date: 'Nov 28, 2025 · 11:42 AM',
        body: `Endpoint confirmed stable. Here's a quick summary.

Returns a session token. Response time is under 120ms in staging. No breaking changes planned until Q2 2026.`,
    },
    {
        id: 3,
        author: 'Amara Osei',
        role: 'UX Designer',
        date: 'Nov 29, 2025 · 09:05 AM',
        body: `> Progress indicator styling is inconsistent with the rest of the design system

Fixed in the Figma file — updated to use DS tokens. Handoff ready. Linked updated frames in the Attachments tab.`,
    },
    {
        id: 4,
        author: 'Daniel Park',
        role: 'Tech Lead',
        date: 'Nov 29, 2025 · 11:00 AM',
        body: `## Release Checklist

Before we ship this, make sure all of the following are done:

- [x] API endpoint reviewed and confirmed stable
- [x] Figma handoff updated with DS tokens
- [ ] Accessibility audit passed (WCAG 2.1 AA)
- [ ] QA regression suite passed in all 4 locales
- [ ] Content team sign-off on EN copy`,
    },
    {
        id: 5,
        author: 'Wei Zhang',
        role: 'Backend Engineer',
        date: 'Nov 29, 2025 · 01:30 PM',
        body: `### Environment Comparison

Here's a breakdown of response times across environments:

| Environment | p50   | p95    | p99    |
|-------------|-------|--------|--------|
| Local       | 12ms  | 28ms   | 45ms   |
| Staging     | 38ms  | 91ms   | 118ms  |
| Production  | 41ms  | 98ms   | 124ms  |

All within the agreed SLA of **120ms at p95**. No action needed.`,
    },
    {
        id: 6,
        author: 'Sarah Mitchell',
        role: 'Product Manager',
        date: 'Nov 30, 2025 · 09:15 AM',
        body: `### Step 4 — Role Assignment UI

Outlined the expected behavior for the admin-only role assignment screen:

1. Admin opens the **Team Members** panel
2. Selects one or more users from the list
3. Picks a role from the dropdown: \`viewer\`, \`editor\`, \`admin\`
4. Confirms with **Save**

> Only users with the \`admin\` role should see this panel. Hide it entirely for \`editor\` and \`viewer\`.

Non-admin users hitting the endpoint directly should get a \`403 Forbidden\`.`,
    },
    {
        id: 7,
        author: 'Amara Osei',
        role: 'UX Designer',
        date: 'Nov 30, 2025 · 02:45 PM',
        body: `Ran a quick accessibility pass on the current flow. Summary:

**Passed ✓**
- Colour contrast on all text elements (4.5:1 minimum met)
- Focus indicators visible on all interactive elements
- ARIA labels present on icon-only buttons

**Failed ✗**
- Step indicator lacks \`aria-current="step"\` on the active step
- Modal close button missing \`aria-label\`
- Progress bar has no \`role="progressbar"\` or \`aria-valuenow\`

Raised these as blocking issues — need fixes before QA sign-off.`,
    },
]
</script>

<template>
    <div class="gap-4 p-4 app-content-background flex flex-col">
        <div class="gap-3 flex items-start">
            <UserAvatar user-name="Sarah Mitchell" size="medium" class="mt-1 shrink-0" />
            <div class="min-w-0 flex-1">
                <MarkdownEditor v-model="newComment" />
            </div>
        </div>

        <Divider />

        <div class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
            <div v-for="comment in comments" :key="comment.id" class="gap-3 py-4 first:pt-0 flex">
                <UserAvatar :user-name="comment.author" size="medium" class="mt-0.5 shrink-0" />

                <div class="gap-2 min-w-0 flex flex-1 flex-col">
                    <div class="gap-4 flex items-center justify-between">
                        <div class="gap-2 flex items-baseline">
                            <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">
                                {{ comment.author }}
                            </span>
                            <span class="text-xs text-surface-500 dark:text-surface-400">
                                {{ comment.role }}
                            </span>
                        </div>
                        <span class="text-xs text-surface-400 dark:text-surface-500 shrink-0">
                            {{ comment.date }}
                        </span>
                    </div>

                    <MarkdownPreview :model-value="comment.body" />
                </div>
            </div>
        </div>
    </div>
</template>
