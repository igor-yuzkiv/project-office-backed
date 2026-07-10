<script setup lang="ts">
import { computed } from 'vue'
import Avatar from 'primevue/avatar'
import type { ComponentSize } from '@/shared/types'
import { USER_AVATAR_SIZE_MAP } from '../user-avatar.config'

const props = withDefaults(
    defineProps<{
        initials: string
        avatarUrl?: string | null
        size?: ComponentSize
    }>(),
    { size: 'medium' }
)

const sizeClasses = computed(() => USER_AVATAR_SIZE_MAP[props.size])
</script>

<template>
    <Avatar
        :image="avatarUrl ?? undefined"
        :label="avatarUrl ? undefined : initials"
        shape="circle"
        :pt="{
            root: { class: ['!bg-indigo-500 !text-white !font-semibold', sizeClasses.root] },
            label: { class: sizeClasses.label },
        }"
    />
</template>
