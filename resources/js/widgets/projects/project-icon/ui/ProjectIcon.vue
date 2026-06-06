<script setup lang="ts">
import { computed } from 'vue'
import Avatar from 'primevue/avatar'
import type { AvatarProps } from 'primevue/avatar'
import type { ComponentSize } from '@/shared/types'
import { PROJECT_ICON_SIZE_MAP } from '../project-icon.config'

const props = withDefaults(
    defineProps<{
        prefix: string
        size?: ComponentSize
        shape?: AvatarProps['shape']
    }>(),
    { size: 'medium', shape: 'square' }
)

const sizeClasses = computed(() => PROJECT_ICON_SIZE_MAP[props.size])
</script>

<template>
    <Avatar
        :label="prefix"
        :shape="shape"
        :pt="{
            root: { class: ['!bg-blue-600 !text-white !font-semibold', sizeClasses.root] },
            label: { class: sizeClasses.label },
        }"
    />
</template>
