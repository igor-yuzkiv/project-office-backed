<script setup lang="ts">
import { computed } from 'vue'
import type { TaskPriorityDto } from '@/entities/task/types/task-priority.types'
import { TaskPriorityMap } from '@/entities/task/config'
import { STATUS_COLORS_FALLBACK, StatusPill, useStatusColors } from '@/shared/components/status-pill'

const props = withDefaults(
    defineProps<{
        priority: TaskPriorityDto | null | undefined
        showIcon?: boolean
    }>(),
    { showIcon: true }
)

const meta = computed(() => (props.priority ? (TaskPriorityMap[props.priority.name] ?? null) : null))
const colors = useStatusColors(() => meta.value?.colors ?? STATUS_COLORS_FALLBACK)
</script>

<template>
    <StatusPill
        :label="meta?.label ?? 'None'"
        :colors="colors"
        :icon="meta && showIcon ? meta.icon : undefined"
        title="Priority"
    />
</template>
