import { expect, type Page } from '@playwright/test'

export type Mode = 'View' | 'Edit' | 'Annotate'

export function modeSwitch(page: Page) {
    return page.locator('.p-selectbutton')
}

export async function switchMode(page: Page, mode: Mode) {
    await modeSwitch(page).getByText(mode, { exact: true }).click()

    if (mode === 'Edit') await expect(editor(page)).toBeVisible()
    else await expect(preview(page)).toBeVisible()
}

export function editor(page: Page) {
    return page.locator('.md-editor .cm-content').first()
}

export function preview(page: Page) {
    return page.locator('.document-canvas .md-editor-preview')
}

/** Types at the end of the editor; the marker is unique so a later assertion can find it. */
export async function typeAtEnd(page: Page, text: string) {
    await editor(page).click()
    await page.keyboard.press('Control+End')
    await page.keyboard.type(text)
}

export function isVersionContentWrite(url: string, method: string) {
    return method === 'PUT' && /\/api\/project-documents\/[^/]+\/versions\/[^/]+$/.test(url)
}
