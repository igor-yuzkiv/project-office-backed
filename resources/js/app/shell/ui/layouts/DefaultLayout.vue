<script setup lang="ts">
import { computed } from 'vue'
import { useIsFetching } from '@tanstack/vue-query'
import ProgressBar from 'primevue/progressbar'
import AppHeader from '../header/AppHeader.vue'
import AppLeftNavigationSidebar from '../navigation/AppLeftNavigationSidebar.vue'
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
    {
        key: 'projects',
        label: 'Projects',
        icon: 'heroicons:rectangle-stack',
        routeName: 'projects',
        activeWhen: '/projects',
    },
    { key: 'tasks', label: 'Tasks', icon: 'heroicons:check-circle', routeName: 'tasks', activeWhen: '/tasks' },
    {
        key: 'documents',
        label: 'Documents',
        icon: 'heroicons:document-text',
        routeName: 'documents',
        activeWhen: '/documents',
    },
]

const recentProjects = [
    { id: '1', name: 'Atlas Platform', color: '#6366f1' },
    { id: '2', name: 'Horizon CRM', color: '#10b981' },
    { id: '3', name: 'Nexus API', color: '#f59e0b' },
]
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden">
        <AppLeftNavigationSidebar :items="navItems">
            <template #default>
                <p class="mb-1 px-3 text-xs font-semibold tracking-wider text-surface-500 uppercase">Projects</p>
                <div
                    v-for="project in recentProjects"
                    :key="project.id"
                    class="gap-2 rounded-md px-3 py-1.5 text-sm text-surface-300 hover:bg-surface-800 flex items-center"
                >
                    <div class="h-2 w-2 shrink-0 rounded-full" :style="{ background: project.color }" />
                    <span class="truncate">{{ project.name }}</span>
                </div>
            </template>
        </AppLeftNavigationSidebar>

        <div class="bg-surface-50 relative flex flex-1 flex-col overflow-hidden">
            <AppHeader :title="store.pageTitle" :actions="store.headerActions" :breadcrumbs="store.activeBreadcrumbs" />
            <ProgressBar
                v-show="showProgressBar"
                mode="indeterminate"
                class="left-0 right-0 top-14 !h-0.5 !absolute z-10 !rounded-none !border-none"
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
