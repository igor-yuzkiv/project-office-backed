<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import type { SidebarNavItem } from '../../types'
import { APP_NAME } from '@/app/config'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { UserProfilePopover } from '@/widgets/user/profile'
import SidebarNavLink from './SidebarNavLink.vue'

defineProps<{
    items: SidebarNavItem[]
}>()

const layoutStore = useAppLayoutStore()
const themeStore = useAppThemeStore()
const authStore = useAuthStore()
const router = useRouter()

const profilePopover = ref<InstanceType<typeof UserProfilePopover>>()

async function handleLogout() {
    await authStore.logout()
    await router.push({ name: 'login' })
}
</script>

<template>
    <aside
        class="bg-canvas border-line rounded-md flex h-full shrink-0 flex-col overflow-hidden border text-[13.5px] transition-[width] duration-150"
        :class="layoutStore.sidebarCollapsed ? 'w-[52px]' : 'w-[224px]'"
    >
        <div
            class="flex shrink-0 items-center"
            :class="layoutStore.sidebarCollapsed ? 'gap-1.5 px-0 pt-2.5 pb-1.5 flex-col' : 'gap-1 px-2.5 pt-2.5 pb-1.5'"
        >
            <div class="gap-2 px-1.5 py-1 min-w-0 flex items-center">
                <img src="/logo.png" alt="Logo" class="h-5 w-auto shrink-0" />
                <span v-if="!layoutStore.sidebarCollapsed" class="text-ink font-semibold truncate">{{ APP_NAME }}</span>
            </div>
            <button
                v-tooltip.right="{
                    value: layoutStore.sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar',
                }"
                type="button"
                class="rounded-md text-ink-3 hover:bg-hover hover:text-ink grid h-[26px] w-[26px] shrink-0 place-items-center transition-colors"
                :class="{ 'ml-auto': !layoutStore.sidebarCollapsed }"
                :aria-label="layoutStore.sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                @click="layoutStore.toggleSidebar"
            >
                <Icon
                    :icon="
                        layoutStore.sidebarCollapsed
                            ? 'heroicons:chevron-double-right'
                            : 'heroicons:chevron-double-left'
                    "
                    class="h-[15px] w-[15px]"
                />
            </button>
        </div>

        <nav class="gap-0.5 px-2 flex flex-col">
            <SidebarNavLink
                v-for="item in items"
                :key="item.key"
                :item="item"
                :collapsed="layoutStore.sidebarCollapsed"
            />
        </nav>

        <div class="px-2 pt-1 pb-3 flex-1 overflow-auto">
            <slot name="pinned" />
        </div>

        <div
            class="border-line text-ink-2 flex shrink-0 items-center border-t"
            :class="layoutStore.sidebarCollapsed ? 'gap-1.5 px-0 py-2 flex-col' : 'gap-2.5 px-3 py-2'"
        >
            <button
                v-tooltip.right="{ value: authStore.user?.name ?? '', disabled: !layoutStore.sidebarCollapsed }"
                type="button"
                class="gap-2 rounded-md min-w-0 flex items-center"
                :class="layoutStore.sidebarCollapsed ? 'p-0.5' : 'hover:text-ink flex-1'"
                aria-label="Account menu"
                @click="profilePopover?.toggle($event)"
            >
                <UserAvatar
                    :initials="authStore.user?.initials ?? ''"
                    :avatar-url="authStore.user?.avatar_url"
                    size="small"
                />
                <span v-if="!layoutStore.sidebarCollapsed" class="truncate">{{ authStore.user?.name }}</span>
            </button>

            <button
                v-tooltip.right="{ value: 'Switch theme', disabled: !layoutStore.sidebarCollapsed }"
                type="button"
                class="rounded-md text-ink-3 hover:bg-hover hover:text-ink grid h-[26px] w-[26px] shrink-0 place-items-center transition-colors"
                :class="{ 'ml-auto': !layoutStore.sidebarCollapsed }"
                aria-label="Switch theme"
                @click="themeStore.toggle"
            >
                <Icon :icon="themeStore.isDark ? 'heroicons:sun' : 'heroicons:moon'" class="h-[15px] w-[15px]" />
            </button>

            <UserProfilePopover
                ref="profilePopover"
                :name="authStore.user?.name ?? ''"
                :email="authStore.user?.email ?? ''"
                @logout="handleLogout"
            />
        </div>
    </aside>
</template>
