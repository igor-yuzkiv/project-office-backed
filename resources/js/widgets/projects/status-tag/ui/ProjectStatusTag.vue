<script setup lang="ts">
import { computed } from 'vue'
import type { ProjectStatusValue } from '@/entities/project/types'
import { ProjectStatusMap } from '@/entities/project/config'
import { STATUS_COLORS_FALLBACK, StatusPill, useStatusColors } from '@/shared/components/status-pill'

const props = defineProps<{
    status: ProjectStatusValue | null | undefined
}>()

const meta = computed(() => (props.status ? (ProjectStatusMap[props.status] ?? null) : null))
const colors = useStatusColors(() => meta.value?.colors ?? STATUS_COLORS_FALLBACK)
</script>

<template>
    <StatusPill :label="meta?.label ?? 'None'" :colors="colors" title="Status" />
</template>
