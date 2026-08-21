<script setup lang="ts">
import { computed } from 'vue'
import Avatar from 'primevue/avatar'
import type { AvatarProps } from 'primevue/avatar'
import type { ComponentSize } from '@/shared/types'
import type { ProjectStatusValue } from '@/entities/project/types'
import { ProjectStatusMap } from '@/entities/project/config'
import { PROJECT_ICON_EMOJI_LABEL_SIZE_MAP, PROJECT_ICON_SIZE_MAP } from '../project-icon.config'

const props = withDefaults(
    defineProps<{
        prefix: string
        iconEmoji?: string | null
        size?: ComponentSize
        shape?: AvatarProps['shape']
        status?: ProjectStatusValue
    }>(),
    { size: 'medium', shape: 'square' }
)

const sizeClasses = computed(() => PROJECT_ICON_SIZE_MAP[props.size])

// The emoji stands in for the prefix, never for the status colour behind it.
const label = computed(() => props.iconEmoji || props.prefix)

const hasEmoji = computed(() => Boolean(props.iconEmoji))

// The status colour is what tells a prefix plate apart from any other. An emoji says
// what the project is by itself, and a status-tinted square behind it only fights it.
const statusStyle = computed(() => {
    if (hasEmoji.value || !props.status) return undefined

    const color = ProjectStatusMap[props.status]?.color

    return color ? { backgroundColor: color } : undefined
})

const rootClass = computed(() => [
    sizeClasses.value.root,
    hasEmoji.value
        ? '!bg-surface-100 dark:!bg-surface-800'
        : ['!text-white !font-semibold', !props.status && '!bg-blue-600'],
])

const labelClass = computed(() =>
    hasEmoji.value ? PROJECT_ICON_EMOJI_LABEL_SIZE_MAP[props.size] : sizeClasses.value.label
)
</script>

<template>
    <Avatar
        :label="label"
        :shape="shape"
        :pt="{
            root: { class: rootClass, style: statusStyle },
            label: { class: labelClass },
        }"
    />
</template>
