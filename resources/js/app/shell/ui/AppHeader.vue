<script setup lang="ts">
import { computed, ref } from 'vue'
import SplitButton from 'primevue/splitbutton'
import Avatar from 'primevue/avatar'
import type { MenuItem } from 'primevue/menuitem'
import { useAuthStore } from '@/app/stores/use.auth.store'
import { IconButton } from '@/shared/components/button'
import { getInitials } from '@/shared/utils/string.util'
import UserProfilePopover from './UserProfilePopover.vue'
import type { HeaderAction } from '../types'

const props = defineProps<{
    title: string
    actions?: HeaderAction[]
}>()

const authStore = useAuthStore()
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
</script>

<template>
    <header class="flex h-14 shrink-0 items-center justify-between border-b border-surface-200 px-4 dark:border-surface-700">
        <span class="text-base font-medium text-surface-900 dark:text-surface-0">{{ title }}</span>

        <div class="flex items-center gap-2">
            <SplitButton
                v-if="primaryAction"
                :label="primaryAction.title"
                :model="dropdownItems"
                size="small"
                @click="primaryAction.action"
            />

            <IconButton icon="heroicons:bell" size="small" severity="secondary" />
            <IconButton icon="heroicons:cog-6-tooth" size="small" severity="secondary" />

            <Avatar
                :label="userInitials"
                shape="circle"
                size="normal"
                class="cursor-pointer"
                @click="profilePopover?.toggle($event)"
            />
            <UserProfilePopover ref="profilePopover" />
        </div>
    </header>
</template>
