import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from 'vue/server-renderer'
import PropertiesGrid from './PropertiesGrid.vue'

function render(props: Record<string, unknown>, slots: Record<string, () => unknown>) {
    return renderToString(createSSRApp({ render: () => h(PropertiesGrid, props, slots) }))
}

const slots = {
    default: () => [h('span', 'Status'), h('div', 'Open')],
    more: () => [h('span', 'Tags'), h('div', 'backend')],
}

describe('PropertiesGrid', () => {
    it('renders the main properties and hides the folded block while collapsed', async () => {
        const html = await render({ expanded: false, moreLabel: 'Dates, tags, attachments' }, slots)

        expect(html).toContain('Status')
        expect(html).toContain('Dates, tags, attachments')
        expect(html).toContain('aria-expanded="false"')
        expect(html).not.toContain('Tags')
    })

    it('renders the folded block with the collapse label while expanded', async () => {
        const html = await render({ expanded: true, lessLabel: 'Less' }, slots)

        expect(html).toContain('Tags')
        expect(html).toContain('Less')
        expect(html).toContain('aria-expanded="true"')
    })

    it('renders no toggle without a folded block', async () => {
        const html = await render({}, { default: slots.default })

        expect(html).not.toContain('aria-expanded')
    })
})
