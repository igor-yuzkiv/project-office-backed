import type { RouteLocationNormalizedLoaded, RouteLocationRaw } from 'vue-router'

export type AppLayoutName = 'default' | 'auth'

export interface SidebarNavItem {
    key: string
    label: string
    icon: string
    routeName: string
    activeWhen?: string | ((item: SidebarNavItem, route: RouteLocationNormalizedLoaded) => boolean)
}

export interface HeaderAction {
    key: string
    title: string
    action?: () => void
    to?: RouteLocationRaw
    is_primary?: boolean
}

export interface BreadcrumbItem {
    label: string
    to?: RouteLocationRaw
}
