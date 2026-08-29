import { expect, test } from '@playwright/test'
import { DOCUMENTS, openDocument, signIn } from '../support'

test.describe('document sidebar', () => {
    test.beforeEach(async ({ page }) => {
        await signIn(page)
        await openDocument(page, DOCUMENTS.modes)
    })

    test('switches between the Versions and Annotations tabs', async ({ page }) => {
        const panel = page.locator('aside').filter({ hasText: /Versions|Annotations/ })

        await page.getByRole('button', { name: 'Versions', exact: true }).click()
        await expect(panel).toContainText('Versions')
        await expect(panel.locator('li')).toHaveCount(1)

        await page.getByRole('button', { name: 'Annotations', exact: true }).click()
        await expect(panel).toContainText('Annotations')
    })

    test('collapses to a strip, opens a tab in a drawer on click, and remembers the state', async ({ page }) => {
        const column = page.locator('aside').filter({ hasText: /Versions|Annotations/ })
        const drawer = page.getByTestId('side-tabs-drawer')

        await page.getByRole('button', { name: 'Hide sidebar' }).click()
        await expect(column).toHaveCount(0)

        // No hover behaviour: only a click opens the drawer.
        await page.getByRole('button', { name: 'Versions', exact: true }).hover()
        await expect(drawer).toHaveCount(0)

        await page.getByRole('button', { name: 'Versions', exact: true }).click()
        await expect(drawer).toContainText('Versions')

        // A click outside dismisses it; the same icon would too.
        await page.locator('.document-canvas').click({ position: { x: 20, y: 400 } })
        await expect(drawer).toHaveCount(0)

        await page.reload()
        await expect(page.getByRole('button', { name: 'Show sidebar' })).toBeVisible()
        await expect(column).toHaveCount(0)

        await page.getByRole('button', { name: 'Show sidebar' }).click()
        await expect(column).toContainText('Versions')
    })
})
