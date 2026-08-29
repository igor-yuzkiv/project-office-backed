import type { ComputedRef, InjectionKey } from 'vue'

export interface SideTabDescriptor {
    value: string
    icon: string
    label: string
}

export interface SideTabsContext {
    register: (tab: SideTabDescriptor) => void
    unregister: (value: string) => void
    /** The tab whose content is shown; null while nothing is registered. */
    active: ComputedRef<string | null>
    /** False while the column is hidden and no drawer is looking at it — then no tab renders. */
    contentVisible: ComputedRef<boolean>
}

export const SIDE_TABS_CONTEXT: InjectionKey<SideTabsContext> = Symbol('SideTabs')
