import { expect, test, type Page } from '@playwright/test'

const email = process.env.E2E_USER_EMAIL ?? 'e2e@example.com'
const password = process.env.E2E_USER_PASSWORD ?? 'password'

// Seeded by database/seeders/E2eSeeder.php.
const documentKey = process.env.E2E_DOCUMENT_KEY ?? 'DOC-E2E-1'
const annotationText = 'This paragraph needs an example.'
const paragraphText = 'The first paragraph of the annotated document.'

// The SPA uses hash-based routing (createWebHashHistory), so routes live under `/#/...`.
async function signIn(page: Page) {
    await page.goto('/#/login')
    await page.locator('#email').fill(email)
    await page.locator('#password input').fill(password)
    await page.getByRole('button', { name: 'Sign In' }).click()
    await expect(page).toHaveURL(/#\/$/)
}

// The document page folds its header actions into a SplitButton, so the entry point is a menu item.
async function openAnnotationMode(page: Page) {
    await page.goto(`/#/project-documents/${documentKey}/content`)
    await page.locator('[aria-haspopup="true"]').last().click()
    await page.getByRole('menuitem', { name: 'Annotation mode' }).click()
    await expect(page).toHaveURL(/\/annotations$/)
}

test.describe('document annotations', () => {
    test('creates an annotation, keeps it across a reload, and deletes it', async ({ page }) => {
        await signIn(page)
        await openAnnotationMode(page)

        const paragraph = page.locator('.md-editor-preview p', { hasText: paragraphText })
        await paragraph.click()
        await expect(paragraph).toHaveClass(/annotation-selected/)

        await page.getByPlaceholder('Write a comment').fill(annotationText)
        await page.getByRole('button', { name: 'Save', exact: true }).click()

        const card = page.locator('aside article', { hasText: annotationText })
        await expect(card).toBeVisible()

        await page.reload()
        await expect(card).toBeVisible()
        await expect(paragraph).toHaveClass(/annotation-anchored/)

        await card.click()
        await expect(paragraph).toBeInViewport()

        await card.getByRole('button', { name: 'Delete' }).click()
        await page.getByRole('alertdialog').getByRole('button', { name: 'Delete' }).click()

        await expect(card).toHaveCount(0)
    })
})
