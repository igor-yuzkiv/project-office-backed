import { expect, type Page } from '@playwright/test'

// Seeded by database/seeders/E2eSeeder.php: one document per spec file that writes.
export const DOCUMENTS = {
    annotated: 'DOC-E2E-1',
    autosave: 'DOC-E2E-2',
    modes: 'DOC-E2E-3',
} as const

export async function openDocument(page: Page, key: string) {
    await page.goto(`/#/project-documents/${key}`)
    await expect(page.locator('.document-canvas')).toBeVisible()
}
