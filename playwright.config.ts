import { defineConfig, devices } from '@playwright/test'

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
import dotenv from 'dotenv'
import { fileURLToPath } from 'node:url'
import path from 'node:path'

const rootDir = path.dirname(fileURLToPath(import.meta.url))
dotenv.config({ path: path.resolve(rootDir, '.env.e2e') })

const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8100'
const port = process.env.E2E_PORT ?? '8100'

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
    testDir: './e2e',
    /* Run tests in files in parallel */
    fullyParallel: true,
    /* Fail the build on CI if you accidentally left test.only in the source code. */
    forbidOnly: !!process.env.CI,
    /* Retry on CI only */
    retries: process.env.CI ? 2 : 0,
    /* Opt out of parallel tests on CI. */
    workers: process.env.CI ? 1 : undefined,
    /* Reporter to use. See https://playwright.dev/docs/test-reporters */
    reporter: [['html', { outputFolder: '_tmp/playwright/reports' }]],
    /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
    use: {
        /* Base URL to use in actions like `await page.goto('/login')`. */
        baseURL,

        /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
        trace: 'on-first-retry',
        video: 'on',
    },

    /*
     * Chromium-only for the initial smoke iteration. Firefox/WebKit are kept
     * ready to re-enable once the suite grows beyond login.
     */
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },

        // {
        //     name: 'firefox',
        //     use: { ...devices['Desktop Firefox'] },
        // },
        // {
        //     name: 'webkit',
        //     use: { ...devices['Desktop Safari'] },
        // },
    ],

    /*
     * Build the SPA, recreate + seed the dedicated e2e database, then serve the
     * app under APP_ENV=e2e. `serve` forwards APP_ENV to the child PHP server,
     * so the served app loads .env.e2e (which sets APP_ENV=e2e).
     */
    webServer: {
        command: [
            'npm run build -- --mode e2e',
            'php artisan migrate:fresh --seed --seeder=E2eSeeder --env=e2e',
            `php artisan serve --env=e2e --port=${port}`,
        ].join(' && '),
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        timeout: 180_000,
    },
})
