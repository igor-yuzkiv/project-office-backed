<script setup lang="ts">
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import type { SidebarNavItem } from '../../types'

const props = defineProps<{
    item: SidebarNavItem
    collapsed: boolean
}>()

const route = useRoute()
const router = useRouter()

function isActive(): boolean {
    if (typeof props.item.activeWhen === 'function') return props.item.activeWhen(props.item, route)

    const prefix = props.item.activeWhen ?? router.resolve({ name: props.item.routeName }).path
    if (prefix === '/') return route.path === '/'
    return route.path === prefix || route.path.startsWith(prefix + '/')
}
</script>

<template>
    <RouterLink
        :to="{ name: item.routeName }"
        class="rounded-md text-sm text-surface-300 hover:bg-surface-800 hover:text-surface-0 flex items-center transition-colors"
        :class="[
            isActive() ? 'bg-surface-800 text-surface-0' : '',
            collapsed ? 'justify-center p-2' : 'gap-3 px-3 py-2',
        ]"
        :title="collapsed ? item.label : undefined"
    >
        <Icon :icon="item.icon" class="h-5 w-5 shrink-0" />
        <span v-if="!collapsed">{{ item.label }}</span>
    </RouterLink>
</template>
