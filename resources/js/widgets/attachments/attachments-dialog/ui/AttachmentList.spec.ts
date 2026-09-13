import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from '@vue/server-renderer'
import type { IAttachment } from '@/entities/attachment/types'
import AttachmentList from './AttachmentList.vue'

function render(props: { attachments: IAttachment[]; isPending: boolean }) {
    return renderToString(createSSRApp({ render: () => h(AttachmentList, props) }))
}

function attachment(overrides: Partial<IAttachment>): IAttachment {
    return {
        id: '1',
        url: '',
        original_name: 'statement.csv',
        extension: 'csv',
        mime_type: 'text/csv',
        size_bytes: 8192,
        storage_provider: 'local',
        storage_key: 'k',
        role: null,
        created_at: '2026-09-09T11:30:00Z',
        updated_at: '2026-09-09T11:30:00Z',
        ...overrides,
    }
}

describe('AttachmentList', () => {
    it('renders each file with its type, name and size', async () => {
        const html = await render({
            attachments: [
                attachment({ id: '1' }),
                attachment({ id: '2', original_name: 'notes.pdf', extension: 'pdf' }),
            ],
            isPending: false,
        })

        expect(html).toContain('statement.csv')
        expect(html).toContain('notes.pdf')
        expect(html).toContain('pdf')
        expect(html).toContain('8.0 KB')
        expect(html).not.toContain('No files yet.')
    })

    it('renders the empty state', async () => {
        const html = await render({ attachments: [], isPending: false })

        expect(html).toContain('No files yet.')
    })

    it('renders the pending state instead of the empty state', async () => {
        const html = await render({ attachments: [], isPending: true })

        expect(html).toContain('Loading files...')
        expect(html).not.toContain('No files yet.')
    })
})
