import { describe, expect, it } from 'vitest'
import { createSSRApp, h } from 'vue'
import { renderToString } from 'vue/server-renderer'
import { createPinia } from 'pinia'
import type { TaskPriorityName } from '@/entities/task/types/task-priority.types'
import { TaskPriorityMap } from '@/entities/task/config'
import { STATUS_COLORS } from '@/shared/components/status-pill'
import TaskPriorityBars from './TaskPriorityBars.vue'

type Props = InstanceType<typeof TaskPriorityBars>['$props']

async function render(props: Props): Promise<string> {
    const app = createSSRApp({ render: () => h(TaskPriorityBars, props) })
    app.use(createPinia())
    return renderToString(app)
}

function filledBars(html: string): number {
    return html.match(/data-filled="true"/g)?.length ?? 0
}

describe('TaskPriorityBars', () => {
    it.each<[TaskPriorityName, number]>([
        ['None', 0],
        ['Low', 1],
        ['Medium', 2],
        ['High', 3],
        ['Urgent', 3],
    ])('fills the bars for %s', async (name, expected) => {
        const html = await render({ priority: TaskPriorityMap[name] })

        expect(filledBars(html)).toBe(expected)
        expect(html).toContain(TaskPriorityMap[name].label)
    })

    it('renders empty bars and None without a priority', async () => {
        const html = await render({ priority: null })

        expect(filledBars(html)).toBe(0)
        expect(html).toContain('None')
    })

    it('paints the urgent bars in the declined colour', async () => {
        const html = await render({ priority: TaskPriorityMap.Urgent })

        expect(html).toContain(`background-color:${STATUS_COLORS.declined.dark.fg}`)
        expect(await render({ priority: TaskPriorityMap.High })).not.toContain('background-color:')
    })
})
