<script setup lang="ts">
import { computed } from 'vue'
import Avatar from 'primevue/avatar'
import type { AvatarProps } from 'primevue/avatar'
import type { ComponentSize } from '@/shared/types'
import type { ProjectStatusValue } from '@/entities/project/types'
import { ProjectStatusMap } from '@/entities/project/config'
import { PROJECT_ICON_SIZE_MAP } from '../project-icon.config'

const props = withDefaults(
    defineProps<{
        prefix: string
        size?: ComponentSize
        shape?: AvatarProps['shape']
        status?: ProjectStatusValue
    }>(),
    { size: 'medium', shape: 'square' }
)

const sizeClasses = computed(() => PROJECT_ICON_SIZE_MAP[props.size])

const statusStyle = computed(() => {
    if (!props.status) return undefined
    const color = ProjectStatusMap[props.status]?.color
    return color ? { backgroundColor: color } : undefined
})

const rootClass = computed(() => [
    '!text-white !font-semibold',
    sizeClasses.value.root,
    !props.status && '!bg-blue-600',
])
</script>

<template>
    <Avatar
        :label="prefix"
        :shape="shape"
        :pt="{
            root: { class: rootClass, style: statusStyle },
            label: { class: sizeClasses.label },
        }"
    />
</template>
