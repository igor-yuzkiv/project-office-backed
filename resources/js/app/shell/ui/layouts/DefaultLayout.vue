<script setup lang="ts">
import { computed } from 'vue'
import { useIsFetching } from '@tanstack/vue-query'
import ProgressBar from 'primevue/progressbar'
import AppHeader from '../header/AppHeader.vue'
import AppLeftNavigationSidebar from '../navigation/AppLeftNavigationSidebar.vue'
import SidebarPinnedProjects from '../navigation/SidebarPinnedProjects.vue'
import LoadingOverlay from '@/shared/components/loading/LoadingOverlay.vue'
import type { SidebarNavItem } from '../../types'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useLoadingStateStore } from '@/app/stores/use.loading-state.store'

const store = useAppLayoutStore()
const loadingStore = useLoadingStateStore()

const isFetching = useIsFetching()
const showProgressBar = computed(() => isFetching.value > 0 || loadingStore.progressLoading)

const navItems: SidebarNavItem[] = [
    { key: 'home', label: 'Home', icon: 'heroicons:home', routeName: 'home', activeWhen: '/' },
    { key: 'activity', label: 'Activity', icon: 'heroicons:bolt', routeName: 'activity', activeWhen: '/activity' },
    {
        key: 'projects',
        label: 'Projects',
        icon: 'heroicons:rectangle-stack',
        routeName: 'projects',
        activeWhen: '/projects',
    },
    { key: 'tasks', label: 'Tasks', icon: 'heroicons:check-circle', routeName: 'tasks', activeWhen: '/tasks' },
    {
        key: 'task-lists',
        label: 'Task lists',
        icon: 'heroicons:queue-list',
        routeName: 'task-lists',
        activeWhen: '/task-lists',
    },
]
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden">
        <AppLeftNavigationSidebar :items="navItems">
            <template #pinned>
                <SidebarPinnedProjects :collapsed="store.sidebarCollapsed" />
            </template>
        </AppLeftNavigationSidebar>

        <div class="bg-page relative flex flex-1 flex-col overflow-hidden">
            <AppHeader :breadcrumbs="store.activeBreadcrumbs" />
            <ProgressBar
                v-show="showProgressBar"
                mode="indeterminate"
                class="left-0 right-0 top-11 !h-0.5 !absolute z-10 !rounded-none !border-none"
            />
            <slot />
            <LoadingOverlay
                v-if="loadingStore.isLoading"
                :title="loadingStore.currentLoader?.title"
                :subtitle="loadingStore.currentLoader?.subtitle"
            />
        </div>
    </div>
</template>
