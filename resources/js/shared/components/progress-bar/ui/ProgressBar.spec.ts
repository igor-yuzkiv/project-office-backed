import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from 'vue/server-renderer'
import ProgressBar from './ProgressBar.vue'

type Props = InstanceType<typeof ProgressBar>['$props']

function render(props: Props, slots: Record<string, () => unknown> = {}) {
    return renderToString(createSSRApp({ render: () => h(ProgressBar, props, slots) }))
}

const done = { fg: '#15803d', bg: '#e6f4ea' } as const
const progress = { fg: '#2f6fe0', bg: '#e6efff' } as const

describe('ProgressBar', () => {
    it('sizes each segment as its share of the total', async () => {
        const html = await render({
            segments: [
                { value: 3, colors: done },
                { value: 1, colors: progress },
            ],
            total: 4,
        })

        expect(html).toContain('width:75%;background-color:#15803d')
        expect(html).toContain('width:25%;background-color:#2f6fe0')
    })

    it('draws no segment for empty values or an empty total', async () => {
        expect(await render({ segments: [{ value: 0, colors: done }], total: 4 })).not.toContain('width:')
        expect(await render({ segments: [{ value: 2, colors: done }], total: 0 })).not.toContain('width:')
    })

    it('renders the label slot under the bar', async () => {
        const html = await render({ segments: [], total: 0 }, { default: () => '0 of 4 done' })

        expect(html).toContain('0 of 4 done')
    })
})
