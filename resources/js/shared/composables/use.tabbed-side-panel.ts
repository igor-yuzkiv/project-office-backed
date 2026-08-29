import { ref, type Ref } from 'vue'
import { useLocalStorage } from '@vueuse/core'

export interface TabbedSidePanel {
    /** Hiding the column is a preference and outlives the session. */
    isCollapsed: Ref<boolean>
    /** Which tab the reader last chose; null until they choose one. Outlives the session too. */
    activeTab: Ref<string | null>
    /** Looking at a hidden column is a moment, and starts closed every time. */
    isDrawerOpen: Ref<boolean>
    collapse: () => void
    expand: () => void
    /** Shows a tab: in the column when it is there, in the drawer when it is not. */
    openTab: (value: string) => void
    closeDrawer: () => void
}

/**
 * The state behind a column of tabs that can be hidden down to a strip of their icons. Render it
 * with `SideTabs`.
 *
 * @param storagePrefix localStorage prefix; the collapsed flag and the active tab live under it.
 */
export function useTabbedSidePanel(storagePrefix: string): TabbedSidePanel {
    const isCollapsed = useLocalStorage(`${storagePrefix}:collapsed`, false)
    const activeTab = useLocalStorage<string | null>(`${storagePrefix}:tab`, null)
    const isDrawerOpen = ref(false)

    function collapse() {
        isCollapsed.value = true
    }

    function expand() {
        isCollapsed.value = false
        isDrawerOpen.value = false
    }

    // Clicking the icon of the tab already in the drawer is the only way to dismiss it without
    // a pointer leaving anything, so that click closes it.
    function openTab(value: string) {
        if (isCollapsed.value && isDrawerOpen.value && activeTab.value === value) {
            isDrawerOpen.value = false
            return
        }

        activeTab.value = value
        isDrawerOpen.value = isCollapsed.value
    }

    function closeDrawer() {
        isDrawerOpen.value = false
    }

    return { isCollapsed, activeTab, isDrawerOpen, collapse, expand, openTab, closeDrawer }
}
