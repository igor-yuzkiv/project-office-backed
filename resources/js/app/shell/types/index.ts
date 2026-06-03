export type AppLayoutName = 'default' | 'auth'

export interface SidebarNavItem {
    key: string
    label: string
    icon: string
    routeName: string
    activeWhen?: string | ((item: SidebarNavItem, route: import('vue-router').RouteLocationNormalizedLoaded) => boolean)
}

export interface HeaderAction {
    key: string
    title: string
    action?: () => void
    to?: import('vue-router').RouteLocationRaw
    is_primary?: boolean
}
