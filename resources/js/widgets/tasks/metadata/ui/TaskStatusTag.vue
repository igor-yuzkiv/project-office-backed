<script setup lang="ts">
import { computed } from 'vue'
import type { TaskStatusValue } from '@/entities/task/types/task-status.types'
import { TaskStatusMap } from '@/entities/task/config'
import Tag from 'primevue/tag'
import { Icon } from '@iconify/vue'

const props = withDefaults(
    defineProps<{
        status: TaskStatusValue | null | undefined
        variant?: 'light' | 'dark'
        showIcon?: boolean
    }>(),
    { variant: 'dark', showIcon: false }
)

const meta = computed(() => {
    if (!props.status) return null
    return TaskStatusMap[props.status] ?? null
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
    <Tag :style="styles" title="Status">
        <Icon v-if="showIcon" icon="hugeicons:status" />
        {{ meta?.label ?? 'None' }}
    </Tag>
</template>
