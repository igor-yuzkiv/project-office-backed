<script setup lang="ts">
import { ref } from 'vue'
import Breadcrumb from 'primevue/breadcrumb'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { IconButton } from '@/shared/components/button'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { UserProfilePopover } from '@/widgets/user/profile'
import HeaderActionButton from './HeaderActionButton.vue'
import type { BreadcrumbItem, HeaderAction } from '../../types'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store.ts'

defineProps<{
    title: string
    actions?: HeaderAction[]
    breadcrumbs?: BreadcrumbItem[]
}>()

const authStore = useAuthStore()
const themeStore = useAppThemeStore()
const layoutStore = useAppLayoutStore()
const router = useRouter()
const profilePopover = ref<InstanceType<typeof UserProfilePopover>>()

async function handleLogout() {
    await authStore.logout()
    await router.push({ name: 'login' })
}
</script>

<template>
    <header
        class="h-14 border-surface-200 px-4 dark:border-surface-700 bg-white dark:bg-surface-900 flex shrink-0 items-center justify-between border-b"
    >
        <div class="gap-1 flex items-center truncate">
            <IconButton
                icon="heroicons:bars-3"
                size="medium"
                severity="secondary"
                @click="layoutStore.toggleSidebar()"
            />
            <Breadcrumb
                v-if="breadcrumbs?.length"
                :model="breadcrumbs"
                class="!p-0 !border-none !bg-transparent"
                :pt="{ list: { class: 'flex items-center flex-nowrap truncate' } }"
            >
                <template #item="{ item }">
                    <RouterLink
                        v-if="item.to"
                        :to="item.to"
                        class="text-sm text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-0 block"
                    >
                        {{ item.label }}
                    </RouterLink>
                    <span v-else class="text-sm font-medium text-surface-900 dark:text-surface-0 block truncate">
                        {{ item.label }}
                    </span>
                </template>
            </Breadcrumb>

            <span v-else class="text-base font-medium text-surface-900 dark:text-surface-0">{{ title }}</span>
        </div>

        <div class="gap-2 flex shrink-0 items-center">
            <HeaderActionButton v-if="actions?.length" :actions="actions" />

            <IconButton
                @click="themeStore.toggle"
                size="medium"
                severity="secondary"
                :icon="themeStore.isDark ? 'ix:light-dark' : 'circum:dark'"
            />
            <IconButton icon="heroicons:bell" size="medium" severity="secondary" />
            <IconButton icon="heroicons:cog-6-tooth" size="medium" severity="secondary" />

            <UserAvatar
                :initials="authStore.user?.initials ?? ''"
                :avatar-url="authStore.user?.avatar_url"
                size="medium"
                class="cursor-pointer"
                @click="profilePopover?.toggle($event)"
            />

            <UserProfilePopover
                ref="profilePopover"
                :name="authStore.user?.name ?? ''"
                :email="authStore.user?.email ?? ''"
                @logout="handleLogout"
            />
        </div>
    </header>
</template>
