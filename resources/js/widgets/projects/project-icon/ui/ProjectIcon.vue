<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import type { ComponentSize } from '@/shared/types'
import { PROJECT_ICON_SIZE_MAP, projectTintClass } from '../project-icon.config'

const props = withDefaults(
    defineProps<{
        prefix: string
        icon?: string | null
        size?: ComponentSize
    }>(),
    { size: 'medium' }
)

const sizeClasses = computed(() => PROJECT_ICON_SIZE_MAP[props.size])

// The prefix carries the tint whether or not an icon was chosen, so a project keeps the same
// colour on the day someone gives it one.
const tintClass = computed(() => projectTintClass(props.prefix))
</script>

<template>
    <span class="rounded-lg flex items-center justify-center" :class="[sizeClasses.root, tintClass]">
        <Icon v-if="icon" :icon="icon" :class="sizeClasses.glyph" />
        <span v-else class="font-semibold" :class="sizeClasses.label">{{ prefix }}</span>
    </span>
</template>
