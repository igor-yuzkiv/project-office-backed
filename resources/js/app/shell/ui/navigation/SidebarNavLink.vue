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
        v-tooltip.right="{ value: item.label, disabled: !collapsed }"
        :to="{ name: item.routeName }"
        class="hover:bg-hover hover:text-ink flex min-h-[26px] items-center rounded-[5px] text-[13px] transition-colors"
        :class="[
            isActive() ? 'bg-hover text-ink font-medium' : 'text-ink-2',
            collapsed ? 'p-1.5 justify-center' : 'gap-2 px-2 py-1',
        ]"
        :aria-label="collapsed ? item.label : undefined"
    >
        <Icon :icon="item.icon" class="h-4 w-4 shrink-0" />
        <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
    </RouterLink>
</template>
