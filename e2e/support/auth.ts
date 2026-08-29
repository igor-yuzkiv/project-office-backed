import { expect, type Page } from '@playwright/test'

const email = process.env.E2E_USER_EMAIL ?? 'e2e@example.com'
const password = process.env.E2E_USER_PASSWORD ?? 'password'

// The SPA uses hash-based routing (createWebHashHistory), so routes live under `/#/...`.
export async function signIn(page: Page) {
    await page.goto('/#/login')
    await page.locator('#email').fill(email)
    await page.locator('#password input').fill(password)
    await page.getByRole('button', { name: 'Sign In' }).click()
    await expect(page).toHaveURL(/#\/$/)
}
