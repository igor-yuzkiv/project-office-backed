import { expect, test, type Page } from '@playwright/test'

const email = process.env.E2E_USER_EMAIL ?? 'e2e@example.com'
const password = process.env.E2E_USER_PASSWORD ?? 'password'

// Sanctum only treats a request as session-authenticated when it carries an Origin from a
// stateful domain, which page.request does not send — so the calls go through the page itself.
async function json(page: Page, method: string, url: string, body?: unknown) {
    const { status, text } = await page.evaluate(
        async ([method, url, body]) => {
            const xsrf = document.cookie
                .split('; ')
                .find((c) => c.startsWith('XSRF-TOKEN='))
                ?.split('=')[1]

            const response = await fetch(url as string, {
                method: method as string,
                credentials: 'include',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    ...(xsrf ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrf) } : {}),
                },
                ...(body === null ? {} : { body: JSON.stringify(body) }),
            })

            return { status: response.status, text: await response.text() }
        },
        [method, url, body ?? null] as const
    )

    expect(status, `${method} ${url} -> ${text.slice(0, 200)}`).toBeLessThan(300)

    return text ? JSON.parse(text) : null
}

test('opens the primary version, switches to another, and falls back to an empty state', async ({ page }) => {
    await page.goto('/#/login')
    await page.locator('#email').fill(email)
    await page.locator('#password input').fill(password)
    await page.getByRole('button', { name: 'Sign In' }).click()
    await expect(page).toHaveURL(/#\/$/)

    const document = await json(page, 'GET', '/api/project-documents/DOC-E2E-1')
    const documentId = document.data.id
    const projectId = document.data.project_id

    const second = await json(page, 'POST', `/api/project-documents/${documentId}/versions`, { label: 'Rewrite' })
    await json(page, 'PUT', `/api/project-documents/${documentId}/versions`, {
        versions: [
            {
                id: second.data.id,
                content: '# Rewritten\n\nThe second version of this document.',
                label: 'Rewrite',
            },
        ],
    })
    await json(page, 'POST', `/api/project-documents/${documentId}/versions`, { label: null })

    await page.goto(`/#/projects/${projectId}/documentation/${documentId}`)

    const all = await json(page, 'GET', `/api/project-documents/${documentId}/versions`)
    const latest = `v${all.data.at(-1).version_number}`
    const rewritten = `v${all.data.find((v: { label: string | null }) => v.label === 'Rewrite').version_number}`

    await expect(page.getByRole('tab', { name: 'Content' })).toBeVisible()
    await expect(page.getByRole('button', { name: 'Versions' })).toBeVisible()
    // The popover keeps a hidden copy of every version's label, so the toolbar is addressed
    // through the canvas rather than by text alone.
    const toolbar = page.locator('.document-canvas').first()
    await expect(toolbar.getByText(latest, { exact: true })).toBeVisible()
    await expect(toolbar.getByLabel('Primary version')).toBeVisible()

    await page.getByRole('button', { name: 'Versions' }).click()
    await expect(page.getByRole('button', { name: /Rewrite/ })).toBeVisible()

    await page.getByRole('button', { name: /Rewrite/ }).click()
    await expect(page.getByText('Rewritten')).toBeVisible()
    await expect(toolbar.getByText(rewritten, { exact: true })).toBeVisible()
    await expect(toolbar.getByLabel('Primary version')).toHaveCount(0)

    const versions = await json(page, 'GET', `/api/project-documents/${documentId}/versions`)
    for (const version of versions.data) {
        await json(page, 'DELETE', `/api/project-document-versions/${version.id}`)
    }

    await page.reload()
    await expect(page.getByText('This document has no versions yet')).toBeVisible()
    await expect(page.getByRole('button', { name: 'Versions' })).toHaveCount(0)
})
