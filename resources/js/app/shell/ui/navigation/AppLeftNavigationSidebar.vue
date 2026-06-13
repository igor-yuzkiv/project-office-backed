<script setup lang="ts">
import { Icon } from '@iconify/vue'
import type { SidebarNavItem } from '../../types'
import { APP_NAME } from '@/app/config'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import SidebarNavLink from './SidebarNavLink.vue'

defineProps<{
    items: SidebarNavItem[]
}>()

const layoutStore = useAppLayoutStore()
</script>

<template>
    <aside
        class="bg-surface-900 text-surface-0 flex h-full shrink-0 flex-col overflow-hidden transition-all duration-300"
        :class="layoutStore.sidebarCollapsed ? 'w-14' : 'w-60'"
    >
        <div
            class="h-14 flex shrink-0 items-center"
            :class="layoutStore.sidebarCollapsed ? 'px-0 justify-center' : 'gap-2.5 px-4'"
        >
            <img src="/logo.png" alt="Logo" class="h-7 w-auto shrink-0" />
            <span v-if="!layoutStore.sidebarCollapsed" class="text-sm font-semibold truncate">{{ APP_NAME }}</span>
        </div>

        <nav class="gap-1 px-2 flex flex-col">
            <SidebarNavLink
                v-for="item in items"
                :key="item.key"
                :item="item"
                :collapsed="layoutStore.sidebarCollapsed"
            />
        </nav>

        <div v-if="!layoutStore.sidebarCollapsed" class="mt-4 px-2">
            <slot />
        </div>

        <div class="gap-0.5 border-surface-700 px-2 py-2 mt-auto flex flex-col border-t">
            <button
                class="rounded-md text-sm text-surface-300 hover:bg-surface-800 hover:text-surface-0 flex items-center transition-colors"
                :class="layoutStore.sidebarCollapsed ? 'p-2 justify-center' : 'gap-3 px-3 py-2'"
                :title="layoutStore.sidebarCollapsed ? 'Settings' : undefined"
            >
                <Icon icon="heroicons:cog-6-tooth" class="h-4 w-4 shrink-0" />
                <span v-if="!layoutStore.sidebarCollapsed">Settings</span>
            </button>
            <button
                class="rounded-md text-sm text-surface-300 hover:bg-surface-800 hover:text-surface-0 flex items-center transition-colors"
                :class="layoutStore.sidebarCollapsed ? 'p-2 justify-center' : 'gap-3 px-3 py-2'"
                :title="layoutStore.sidebarCollapsed ? 'Profile' : undefined"
            >
                <Icon icon="heroicons:user-circle" class="h-4 w-4 shrink-0" />
                <span v-if="!layoutStore.sidebarCollapsed">Profile</span>
            </button>
        </div>
    </aside>
</template>
