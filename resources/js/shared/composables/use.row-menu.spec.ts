import { describe, expect, it } from 'vitest'
import { createSSRApp, defineComponent, h } from 'vue'
import { renderToString } from '@vue/server-renderer'
import { type RowMenu, useRowMenu } from './use.row-menu'

type Row = { id: number; name: string }

async function setup(): Promise<RowMenu<Row>> {
    let rowMenu!: RowMenu<Row>
    const Host = defineComponent({
        setup() {
            rowMenu = useRowMenu<Row>((row) => [{ label: `Edit ${row.name}` }])

            return () => h('div')
        },
    })
    await renderToString(createSSRApp(Host))

    return rowMenu
}

describe('useRowMenu', () => {
    it('has no items until a row is chosen', async () => {
        const { items } = await setup()

        expect(items.value).toEqual([])
    })

    it('remembers the row it opened for and builds the items from it', async () => {
        const { selected, items, open } = await setup()

        open(new Event('click'), { id: 7, name: 'Ledger' })

        expect(selected.value).toEqual({ id: 7, name: 'Ledger' })
        expect(items.value).toEqual([{ label: 'Edit Ledger' }])
    })
})
