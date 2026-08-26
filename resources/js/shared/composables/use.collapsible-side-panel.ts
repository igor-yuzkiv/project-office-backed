import { ref, type Ref } from 'vue'
import { useLocalStorage } from '@vueuse/core'

export interface CollapsibleSidePanel {
    /** Hiding the column is a preference and outlives the session. */
    isCollapsed: Ref<boolean>
    /** Peeking at a hidden column is a moment, and starts closed every time. */
    isDrawerOpen: Ref<boolean>
    collapse: () => void
    openDrawer: () => void
    /** Brings the column back and dismisses the peek that asked for it. */
    dock: () => void
    closeDrawer: () => void
}

/**
 * The state behind a column that can be hidden down to a hover strip: whether it is hidden,
 * and whether the drawer peeking at it is open. Render it with `SidePanel`.
 *
 * @param storageKey localStorage key the collapsed flag is kept under, one per panel.
 * @param collapsedByDefault where a panel starts before the reader has expressed a preference.
 */
export function useCollapsibleSidePanel(storageKey: string, collapsedByDefault = false): CollapsibleSidePanel {
    const isCollapsed = useLocalStorage(storageKey, collapsedByDefault)
    const isDrawerOpen = ref(false)

    function collapse() {
        isCollapsed.value = true
    }

    function dock() {
        isCollapsed.value = false
        isDrawerOpen.value = false
    }

    function openDrawer() {
        isDrawerOpen.value = true
    }

    function closeDrawer() {
        isDrawerOpen.value = false
    }

    return { isCollapsed, isDrawerOpen, collapse, openDrawer, dock, closeDrawer }
}
