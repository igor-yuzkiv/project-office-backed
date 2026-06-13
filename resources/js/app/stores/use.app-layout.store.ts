import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { useLocalStorage } from '@vueuse/core'
import { useRoute } from 'vue-router'
import type { BreadcrumbItem, HeaderAction } from '@/app/shell'
import { APP_NAME } from '@/app/config'

export const useAppLayoutStore = defineStore('app-layout', () => {
    const route = useRoute()
    const titleOverride = ref<string | null>(null)
    const headerActions = ref<HeaderAction[]>([])
    const breadcrumbs = ref<BreadcrumbItem[]>([])
    const breadcrumbScope = ref<symbol | null>(null)
    const sidebarCollapsed = useLocalStorage('app:sidebar-collapsed', false)

    function toggleSidebar() {
        sidebarCollapsed.value = !sidebarCollapsed.value
    }

    const pageTitle = computed(() => titleOverride.value ?? route.meta.title ?? '')
    const activeBreadcrumbs = computed(() => breadcrumbs.value)

    watch(
        () => route.name,
        () => {
            titleOverride.value = null
        }
    )

    watch(pageTitle, (title) => {
        document.title = title ? `${title} | ${APP_NAME}` : APP_NAME
    })

    function setPageTitle(title: string) {
        titleOverride.value = title
    }

    function setHeaderActions(actions: HeaderAction[]) {
        headerActions.value = actions
    }

    function clearHeaderActions() {
        headerActions.value = []
    }

    function setBreadcrumbs(scopeId: symbol, items: BreadcrumbItem[]) {
        breadcrumbScope.value = scopeId
        breadcrumbs.value = items
    }

    function clearBreadcrumbs(scopeId: symbol) {
        if (breadcrumbScope.value === scopeId) {
            breadcrumbScope.value = null
            breadcrumbs.value = []
        }
    }

    return {
        pageTitle,
        headerActions,
        activeBreadcrumbs,
        sidebarCollapsed,
        setPageTitle,
        setHeaderActions,
        clearHeaderActions,
        setBreadcrumbs,
        clearBreadcrumbs,
        toggleSidebar,
    }
})
