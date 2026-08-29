import { expect, test } from '@playwright/test'
import { DOCUMENTS, isVersionContentWrite, openDocument, preview, signIn, switchMode, typeAtEnd } from '../support'

// The tests write to the same document, so they must not race each other.
test.describe.configure({ mode: 'serial' })

test.describe('document autosave', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page)
        await openDocument(page, DOCUMENTS.autosave)
        await switchMode(page, 'Edit')
    })

    test('writes what was typed after a pause and keeps it across a reload', async ({ page }) => {
        const marker = `Autosaved ${Date.now()}`

        const write = page.waitForResponse((res) => isVersionContentWrite(res.url(), res.request().method()))
        await typeAtEnd(page, `\n\n${marker}`)
        expect((await write).ok()).toBe(true)
        await expect(page.getByText(/Saved \d\d:\d\d/)).toBeVisible()

        await page.reload()
        await expect(preview(page)).toContainText(marker)
    })

    test('writes at once on Ctrl+S', async ({ page }) => {
        const marker = `Shortcut ${Date.now()}`
        await typeAtEnd(page, `\n\n${marker}`)

        const write = page.waitForResponse((res) => isVersionContentWrite(res.url(), res.request().method()), {
            timeout: 1000,
        })
        await page.keyboard.press('Control+s')
        expect((await write).ok()).toBe(true)
    })

    test('writes a draft to the document it was typed in after the reader moved on', async ({ page }) => {
        const marker = `Left behind ${Date.now()}`
        await typeAtEnd(page, `\n\n${marker}`)

        // The route stays the same and only the document changes, so the page is reused and the
        // write goes out from a page that already shows the other document.
        const write = page.waitForResponse((res) => isVersionContentWrite(res.url(), res.request().method()))
        await openDocument(page, DOCUMENTS.modes)
        expect((await write).ok()).toBe(true)

        await openDocument(page, DOCUMENTS.autosave)
        await page.reload()
        await expect(preview(page)).toContainText(marker)
    })

    test('reports a failed write and lets Save retry it', async ({ page, context }) => {
        const marker = `Offline ${Date.now()}`

        await context.route('**/api/project-documents/*/versions/*', (route) => route.abort())
        await typeAtEnd(page, `\n\n${marker}`)
        await expect(page.getByText('Not saved')).toBeVisible()

        await context.unroute('**/api/project-documents/*/versions/*')
        const write = page.waitForResponse((res) => isVersionContentWrite(res.url(), res.request().method()))
        await page.getByRole('button', { name: 'Save', exact: true }).click()
        expect((await write).ok()).toBe(true)
        await expect(page.getByText(/Saved \d\d:\d\d/)).toBeVisible()
    })
})
