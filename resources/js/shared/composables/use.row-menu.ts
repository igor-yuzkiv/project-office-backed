import { computed, type Ref, shallowRef, useTemplateRef } from 'vue'
import type Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'

export interface RowMenu<T> {
    /** The row the menu was last opened for. */
    selected: Ref<T | undefined>
    /** Items for the selected row; empty until a row was chosen. */
    items: Ref<MenuItem[]>
    open: (event: Event, row: T) => void
}

/**
 * One popup menu for every row of a list: opening it for a row remembers that row, and the
 * items are built for it. The template keeps the `<Menu ref="rowMenu" popup>` it drives.
 */
export function useRowMenu<T>(itemsFor: (row: T) => MenuItem[] = () => []): RowMenu<T> {
    const menu = useTemplateRef<InstanceType<typeof Menu>>('rowMenu')
    const selected = shallowRef<T>()
    const items = computed(() => (selected.value === undefined ? [] : itemsFor(selected.value)))

    function open(event: Event, row: T) {
        selected.value = row
        menu.value?.toggle(event)
    }

    return { selected, items, open }
}
