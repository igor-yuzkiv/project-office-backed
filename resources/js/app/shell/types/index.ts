export type AppLayoutName = 'default' | 'auth'

export interface SidebarNavItem {
    key: string
    label: string
    icon: string
    routeName: string
    activeFor?: string[]
}

export interface HeaderAction {
    key: string
    title: string
    action: () => void
    is_primary?: boolean
}
