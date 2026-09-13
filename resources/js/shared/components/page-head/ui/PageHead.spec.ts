import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from '@vue/server-renderer'
import PageHead from './PageHead.vue'

describe('PageHead', () => {
    it('renders the key, meta and actions on the first row and the title below', async () => {
        const html = await renderToString(
            createSSRApp({
                render: () =>
                    h(
                        PageHead,
                        { title: 'Reconcile ledger rows' },
                        {
                            key: () => h('span', 'HBR-214'),
                            meta: () => 'Edited 2 hours ago by Claude',
                            actions: () => h('button', 'Edit'),
                        }
                    ),
            })
        )

        expect(html).toContain('type-title')
        expect(html).toContain('Reconcile ledger rows')
        expect(html).toContain('<span>HBR-214</span>')
        expect(html).toContain('Edited 2 hours ago by Claude')
        expect(html).toContain('<button>Edit</button>')
    })
})
