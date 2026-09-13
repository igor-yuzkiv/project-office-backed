import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from 'vue/server-renderer'
import PrimeVue from 'primevue/config'
import StatusPill from './StatusPill.vue'

type Props = InstanceType<typeof StatusPill>['$props']

async function render(props: Props): Promise<string> {
    const app = createSSRApp({ render: () => h(StatusPill, props) })
    app.use(PrimeVue, { unstyled: true })
    return renderToString(app)
}

const colors = { fg: '#15803d', bg: '#e6f4ea' } as const

describe('StatusPill', () => {
    it('renders the label with the given colours', async () => {
        const html = await render({ label: 'Completed', colors })

        expect(html).toContain('Completed')
        expect(html).toContain('background-color:#e6f4ea')
        expect(html).toContain('color:#15803d')
    })

    it('shows the dot by default and hides it on dot=false', async () => {
        const html = await render({ label: 'Open', colors })
        expect(html).toContain('data-role="dot"')
        expect(html).not.toContain('<svg')
        expect(await render({ label: 'Open', colors, dot: false })).not.toContain('data-role="dot"')
    })

    it('shows the icon instead of the dot when one is given', async () => {
        const html = await render({ label: 'High', colors, icon: 'hugeicons:arrow-up-01' })

        expect(html).not.toContain('data-role="dot"')
        expect(html).toContain('<svg')
    })
})
