import { expect, test } from '@playwright/test'
import { DOCUMENTS, openDocument, preview, signIn, switchMode } from '../support'

// Unique per run, so a card left behind by an interrupted run can never be mistaken for this one.
const annotationText = `This paragraph needs an example (${Date.now()}).`
const paragraphText = 'The first paragraph of the annotated document.'

test.describe('document annotations', () => {
    test('creates an annotation, keeps it across a reload, and deletes it', async ({ page }) => {
        await signIn(page)
        await openDocument(page, DOCUMENTS.annotated)
        await switchMode(page, 'Annotate')

        const paragraph = preview(page).locator('p', { hasText: paragraphText })
        await paragraph.click()
        await expect(paragraph).toHaveClass(/document-block-selected/)

        await page.getByPlaceholder('Write a comment').fill(annotationText)
        await page.getByRole('button', { name: 'Save', exact: true }).click()

        // The sidebar opens on its first tab; the list of annotations is the second.
        await page.getByRole('button', { name: 'Annotations', exact: true }).click()
        const card = page.locator('aside article', { hasText: annotationText })
        await expect(card).toBeVisible()

        // A reload lands in View: the annotation is listed and anchored, but not editable.
        await page.reload()
        await expect(card).toBeVisible()
        await expect(paragraph).toHaveClass(/annotation-anchored/)
        await expect(card.getByRole('button', { name: 'Delete' })).toHaveCount(0)

        await switchMode(page, 'Annotate')
        await card.click()
        await expect(paragraph).toBeInViewport()

        await card.getByRole('button', { name: 'Delete' }).click()
        await page.getByRole('alertdialog').getByRole('button', { name: 'Delete' }).click()

        await expect(card).toHaveCount(0)
    })
})
