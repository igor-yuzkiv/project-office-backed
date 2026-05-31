<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import { Icon } from '@iconify/vue'
import type { SidebarNavItem } from '../types'

defineProps<{
    items: SidebarNavItem[]
}>()

const route = useRoute()

const appName = (import.meta.env.VITE_APP_NAME as string | undefined) ?? 'Task Manager'
</script>

<template>
    <aside class="w-60 bg-surface-900 text-surface-0 flex h-full shrink-0 flex-col">
        <div class="h-14 px-4 gap-2.5 flex items-center">
            <img src="/logo.png" alt="Logo" class="h-7 w-auto shrink-0" />
            <span class="text-sm font-semibold truncate">{{ appName }}</span>
        </div>

        <nav class="gap-0.5 px-2 flex flex-col">
            <RouterLink
                v-for="item in items"
                :key="item.key"
                :to="{ name: item.routeName }"
                class="gap-3 rounded-md px-3 py-2 text-sm text-surface-300 hover:bg-surface-800 hover:text-surface-0 flex items-center transition-colors"
                :class="{ 'bg-surface-800 text-surface-0': route.name === item.routeName }"
            >
                <Icon :icon="item.icon" class="h-4 w-4 shrink-0" />
                <span>{{ item.label }}</span>
            </RouterLink>
        </nav>

        <div class="mt-4 px-2">
            <slot />
        </div>

        <div class="gap-0.5 border-surface-700 px-2 py-2 mt-auto flex flex-col border-t">
            <button
                class="gap-3 rounded-md px-3 py-2 text-sm text-surface-300 hover:bg-surface-800 hover:text-surface-0 flex items-center transition-colors"
            >
                <Icon icon="heroicons:cog-6-tooth" class="h-4 w-4 shrink-0" />
                <span>Settings</span>
            </button>
            <button
                class="gap-3 rounded-md px-3 py-2 text-sm text-surface-300 hover:bg-surface-800 hover:text-surface-0 flex items-center transition-colors"
            >
                <Icon icon="heroicons:user-circle" class="h-4 w-4 shrink-0" />
                <span>Profile</span>
            </button>
        </div>
    </aside>
</template>
