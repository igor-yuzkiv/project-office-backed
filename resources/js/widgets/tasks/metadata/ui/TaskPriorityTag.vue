<script setup lang="ts">
import { computed } from 'vue'
import type { TaskPriorityDto } from '@/entities/task/types/task-priority.types'
import { TaskPriorityMap } from '@/entities/task/config'
import Tag from 'primevue/tag'
import { Icon } from '@iconify/vue'

const props = withDefaults(
    defineProps<{
        priority: TaskPriorityDto | null | undefined
        variant?: 'light' | 'dark'
        showIcon?: boolean
    }>(),
    { variant: 'light', showIcon: false }
)

const meta = computed(() => {
    if (!props.priority) return null
    return TaskPriorityMap[props.priority.name] ?? null
})

const styles = computed(() => {
    if (!meta.value) {
        return props.variant === 'dark'
            ? { backgroundColor: '#6b7280', color: '#ffffff' }
            : { backgroundColor: '#6b728020', color: '#6b7280' }
    }
    const color = meta.value.color
    if (props.variant === 'dark') {
        return { backgroundColor: color, color: '#ffffff' }
    }
    return { backgroundColor: `${color}20`, color }
})
</script>

<template>
    <Tag :style="styles" title="Priority">
        <Icon v-if="meta && showIcon" :icon="meta.icon" />
        {{ meta?.label ?? 'None' }}
    </Tag>
</template>
