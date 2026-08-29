import { expect, test } from '@playwright/test'
import {
    DOCUMENTS,
    editor,
    isVersionContentWrite,
    modeSwitch,
    openDocument,
    preview,
    signIn,
    switchMode,
    typeAtEnd,
} from '../support'

// The tests write to the same document, so they must not race each other.
test.describe.configure({ mode: 'serial' })

test.describe('document modes', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page)
        await openDocument(page, DOCUMENTS.modes)
    })

    test('opens in View with the rendered text and no editor', async ({ page }) => {
        await expect(modeSwitch(page).locator('[aria-pressed="true"]')).toHaveText('View')
        await expect(preview(page)).toBeVisible()
        await expect(editor(page)).toHaveCount(0)
    })

    test('Edit shows the editor and leaving it writes what was typed', async ({ page }) => {
        await switchMode(page, 'Edit')
        await expect(preview(page)).toHaveCount(0)

        const marker = `Switched ${Date.now()}`
        await typeAtEnd(page, `\n\n${marker}`)

        const write = page.waitForResponse((res) => isVersionContentWrite(res.url(), res.request().method()))
        await switchMode(page, 'View')
        expect((await write).ok()).toBe(true)
        await expect(preview(page)).toContainText(marker)
    })

    test('only Annotate lets a block be picked', async ({ page }) => {
        const paragraph = preview(page).locator('p').first()

        await paragraph.click()
        await expect(paragraph).not.toHaveClass(/document-block-selected/)
        await expect(page.getByPlaceholder('Write a comment')).toHaveCount(0)

        await switchMode(page, 'Annotate')
        await paragraph.click()
        await expect(paragraph).toHaveClass(/document-block-selected/)
        await expect(page.getByPlaceholder('Write a comment')).toBeVisible()
    })
})
