<script setup lang="ts">
import { computed, ref } from 'vue'
import Avatar from 'primevue/avatar'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { IconButton } from '@/shared/components/button'
import { getInitials } from '@/shared/utils/string.util'
import { UserProfilePopover } from '@/widgets/user/profile'
import HeaderActionButton from './HeaderActionButton.vue'
import type { HeaderAction } from '../../types'

defineProps<{
    title: string
    actions?: HeaderAction[]
}>()

const authStore = useAuthStore()
const router = useRouter()
const profilePopover = ref<InstanceType<typeof UserProfilePopover>>()

const userInitials = computed(() => getInitials(authStore.user?.name ?? ''))

async function handleLogout() {
    await authStore.logout()
    await router.push({ name: 'login' })
}
</script>

<template>
    <header
        class="h-14 border-surface-200 px-4 dark:border-surface-700 flex shrink-0 items-center justify-between border-b"
    >
        <span class="text-base font-medium text-surface-900 dark:text-surface-0">{{ title }}</span>

        <div class="gap-2 flex items-center">
            <HeaderActionButton v-if="actions?.length" :actions="actions" />

            <IconButton icon="heroicons:bell" size="medium" severity="secondary" />
            <IconButton icon="heroicons:cog-6-tooth" size="medium" severity="secondary" />

            <Avatar
                :label="userInitials"
                shape="circle"
                size="normal"
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
