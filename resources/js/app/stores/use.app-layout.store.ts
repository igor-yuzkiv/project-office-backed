import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import type { HeaderAction } from '@/app/shell'

export const useAppLayoutStore = defineStore('app-layout', () => {
    const route = useRoute()
    const titleOverride = ref<string | null>(null)
    const headerActions = ref<HeaderAction[]>([])

    const pageTitle = computed(() => titleOverride.value ?? route.meta.title ?? '')

    watch(
        () => route.name,
        () => {
            titleOverride.value = null
        }
    )

    watch(pageTitle, (title) => {
        const appName = (import.meta.env.VITE_APP_NAME as string | undefined) ?? 'Task Manager'
        document.title = title ? `${title} | ${appName}` : appName
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

    return { pageTitle, headerActions, setPageTitle, setHeaderActions, clearHeaderActions }
})
