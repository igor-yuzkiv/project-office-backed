<script setup lang="ts">
import { computed } from 'vue'
import type { TaskStatusValue } from '@/entities/task/types/task-status.types'
import { TaskStatusMap } from '@/entities/task/config'
import { STATUS_COLORS_FALLBACK, StatusPill, useStatusColors } from '@/shared/components/status-pill'

const props = defineProps<{
    status: TaskStatusValue | null | undefined
}>()

const meta = computed(() => (props.status ? (TaskStatusMap[props.status] ?? null) : null))
const colors = useStatusColors(() => meta.value?.colors ?? STATUS_COLORS_FALLBACK)
</script>

<template>
    <StatusPill :label="meta?.label ?? 'None'" :colors="colors" title="Status" />
</template>
