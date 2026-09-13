<script setup lang="ts">
import { computed } from 'vue'
import type { TaskListStatusValue } from '@/entities/task-list/types'
import { TaskListStatusMap } from '@/entities/task-list/config'
import { STATUS_COLORS_FALLBACK, StatusPill, useStatusColors } from '@/shared/components/status-pill'

const props = defineProps<{
    status: TaskListStatusValue | null | undefined
}>()

const meta = computed(() => (props.status ? (TaskListStatusMap[props.status] ?? null) : null))
const colors = useStatusColors(() => meta.value?.colors ?? STATUS_COLORS_FALLBACK)
</script>

<template>
    <StatusPill :label="meta?.label ?? 'None'" :colors="colors" title="Status" />
</template>
