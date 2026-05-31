<script setup lang="ts">
import { computed, ref } from 'vue'
import SplitButton from 'primevue/splitbutton'
import Avatar from 'primevue/avatar'
import type { MenuItem } from 'primevue/menuitem'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { IconButton } from '@/shared/components/button'
import { getInitials } from '@/shared/utils/string.util'
import { UserProfilePopover } from '@/widgets/user'
import type { HeaderAction } from '../../types'

const props = defineProps<{
    title: string
    actions?: HeaderAction[]
}>()

const authStore = useAuthStore()
const router = useRouter()
const profilePopover = ref<InstanceType<typeof UserProfilePopover>>()

const userInitials = computed(() => getInitials(authStore.user?.name ?? ''))

const primaryAction = computed<HeaderAction | undefined>(() => {
    if (!props.actions?.length) return undefined
    return props.actions.find((a) => a.is_primary) ?? props.actions[0]
})

const dropdownItems = computed<MenuItem[]>(() => {
    if (!props.actions?.length || !primaryAction.value) return []
    return props.actions
        .filter((a) => a.key !== primaryAction.value!.key)
        .map((a) => ({ label: a.title, command: a.action }))
})

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
            <SplitButton
                v-if="primaryAction"
                :label="primaryAction.title"
                :model="dropdownItems"
                size="small"
                @click="primaryAction.action"
            />

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
