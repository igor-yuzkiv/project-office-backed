<script setup lang="ts">
import { computed } from 'vue'
import Avatar from 'primevue/avatar'
import type { ComponentSize } from '@/shared/types'
import type { UserOverviewDto } from '@/entities/user/types'
import { getInitials } from '@/shared/utils/string.util'
import { USER_AVATAR_SIZE_MAP } from '../user-avatar.config'

const props = withDefaults(
    defineProps<{
        user: UserOverviewDto
        size?: ComponentSize
    }>(),
    { size: 'medium' }
)

const initials = computed(() => getInitials(props.user.name))
const sizeClasses = computed(() => USER_AVATAR_SIZE_MAP[props.size])
</script>

<template>
    <Avatar
        :label="initials"
        shape="circle"
        :pt="{
            root: { class: ['!bg-indigo-500 !text-white !font-semibold', sizeClasses.root] },
            label: { class: sizeClasses.label },
        }"
    />
</template>
