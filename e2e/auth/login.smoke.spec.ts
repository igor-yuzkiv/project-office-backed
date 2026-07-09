import { expect, test } from '@playwright/test'

const email = process.env.E2E_USER_EMAIL ?? 'e2e@example.com'
const password = process.env.E2E_USER_PASSWORD ?? 'password'

// The SPA uses hash-based routing (createWebHashHistory), so routes live under
// `/#/...`; the backend only serves `/` (app.blade.php), not `/login`.
test.describe('login', () => {
    test('signs in with valid credentials and lands on home', async ({ page }) => {
        await page.goto('/#/login')

        await page.locator('#email').fill(email)
        await page.locator('#password input').fill(password)
        await page.getByRole('button', { name: 'Sign In' }).click()

        await expect(page).toHaveURL(/#\/$/)
        await expect(page.getByText('not implemented yet')).toBeVisible()
    })

    test('shows an error for invalid credentials', async ({ page }) => {
        await page.goto('/#/login')

        await page.locator('#email').fill(email)
        await page.locator('#password input').fill('wrong-password')
        await page.getByRole('button', { name: 'Sign In' }).click()

        await expect(page.getByText('Invalid email or password.')).toBeVisible()
        await expect(page).toHaveURL(/#\/login$/)
    })
})
