<script setup lang="ts">
import { computed } from 'vue'
import Tag from 'primevue/tag'
import { Icon } from '@iconify/vue'
import type { ProjectStatusValue } from '@/entities/project/types'
import { ProjectStatusMap } from '@/entities/project/config'

const props = withDefaults(
    defineProps<{
        status: ProjectStatusValue | null | undefined
        variant?: 'light' | 'dark'
        showIcon?: boolean
    }>(),
    { variant: 'dark', showIcon: false }
)

const meta = computed(() => (props.status ? (ProjectStatusMap[props.status] ?? null) : null))

const styles = computed(() => {
    if (!meta.value) {
        return props.variant === 'dark'
            ? { backgroundColor: '#6b7280', color: '#ffffff' }
            : { backgroundColor: '#6b728020', color: '#6b7280' }
    }
    const color = meta.value.color
    return props.variant === 'dark'
        ? { backgroundColor: color, color: '#ffffff' }
        : { backgroundColor: `${color}20`, color }
})
</script>

<template>
    <Tag :style="styles" title="Status">
        <Icon v-if="showIcon" icon="hugeicons:status" />
        {{ meta?.label ?? 'None' }}
    </Tag>
</template>
