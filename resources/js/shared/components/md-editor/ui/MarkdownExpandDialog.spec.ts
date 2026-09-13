import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from '@vue/server-renderer'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import MarkdownExpandDialog from './MarkdownExpandDialog.vue'
import MarkdownPreview from './MarkdownPreview.vue'

function render(component: unknown, props: Record<string, unknown>) {
    const app = createSSRApp({ render: () => h(component as never, props) })
    app.use(createPinia()).use(PrimeVue)
    return renderToString(app)
}

describe('MarkdownPreview', () => {
    it('offers Expand next to Copy when there is markdown', async () => {
        const html = await render(MarkdownPreview, { modelValue: '# Plan' })

        expect(html).toContain('aria-label="Expand"')
        expect(html).toContain('aria-label="Copy markdown"')
    })

    it('offers neither for empty markdown', async () => {
        const html = await render(MarkdownPreview, { modelValue: '  ' })

        expect(html).not.toContain('aria-label="Expand"')
        expect(html).not.toContain('aria-label="Copy markdown"')
    })
})

describe('MarkdownExpandDialog', () => {
    it('renders the same markdown with Copy in the header while open', async () => {
        // PrimeVue's portal renders nothing on the server unless the dialog stays in place.
        const html = await render(MarkdownExpandDialog, {
            modelValue: '# Plan\n\nRead me in full',
            visible: true,
            appendTo: 'self',
        })

        expect(html).toContain('<h1')
        expect(html).toContain('Read me in full')
        expect(html).toContain('aria-label="Copy markdown"')
    })
})
