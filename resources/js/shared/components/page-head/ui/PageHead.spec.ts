import { describe, expect, it } from 'vitest'
import { createSSRApp, h, type VNode } from 'vue'
import { renderToString } from '@vue/server-renderer'
import PageHead, { type PageHeadMode } from './PageHead.vue'

function render(props: { title: string; mode?: PageHeadMode }, slots: Record<string, () => VNode | string>) {
    return renderToString(createSSRApp({ render: () => h(PageHead, props, slots) }))
}

describe('PageHead', () => {
    it('renders the list mode with the title, lede and actions', async () => {
        const html = await render(
            { title: 'Tasks' },
            { lede: () => 'Everything open', actions: () => h('button', 'New task') }
        )

        expect(html).toContain('type-page')
        expect(html).toContain('Tasks')
        expect(html).toContain('Everything open')
        expect(html).toContain('<button>New task</button>')
        expect(html).not.toContain('type-title')
    })

    it('renders the document mode with the key, meta, actions and title', async () => {
        const html = await render(
            { title: 'Reconcile ledger rows', mode: 'document' },
            {
                key: () => h('span', 'HBR-214'),
                meta: () => 'Edited 2 hours ago by Claude',
                actions: () => h('button', 'Edit'),
            }
        )

        expect(html).toContain('type-title')
        expect(html).toContain('Reconcile ledger rows')
        expect(html).toContain('<span>HBR-214</span>')
        expect(html).toContain('Edited 2 hours ago by Claude')
        expect(html).toContain('<button>Edit</button>')
        expect(html).not.toContain('type-page')
    })
})
