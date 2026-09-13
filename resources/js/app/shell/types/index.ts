import type { RouteLocationRaw } from 'vue-router'

export type AppLayoutName = 'default' | 'auth'

export interface SidebarNavItem {
    key: string
    label: string
    icon: string
    routeName: string
    activeWhen: string
}

export interface BreadcrumbItem {
    label: string
    to?: RouteLocationRaw
}
