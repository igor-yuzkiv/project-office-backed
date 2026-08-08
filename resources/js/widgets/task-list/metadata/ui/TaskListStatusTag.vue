<script setup lang="ts">
import { computed } from 'vue'
import type { TaskListStatusValue } from '@/entities/task-list/types'
import { TaskListStatusMap } from '@/entities/task-list/config'
import Tag from 'primevue/tag'

const props = withDefaults(
    defineProps<{
        status: TaskListStatusValue | null | undefined
        variant?: 'light' | 'dark'
    }>(),
    { variant: 'dark' }
)

const meta = computed(() => (props.status ? (TaskListStatusMap[props.status] ?? null) : null))

const styles = computed(() => {
    const color = meta.value?.color ?? '#6b7280'

    return props.variant === 'dark'
        ? { backgroundColor: color, color: '#ffffff' }
        : { backgroundColor: `${color}20`, color }
})
</script>

<template>
    <Tag :style="styles" title="Status">
        {{ meta?.label ?? 'None' }}
    </Tag>
</template>
