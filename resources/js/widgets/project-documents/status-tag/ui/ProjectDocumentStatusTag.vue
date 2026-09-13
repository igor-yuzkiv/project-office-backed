<script setup lang="ts">
import { computed } from 'vue'
import type { ProjectDocumentStatusValue } from '@/entities/project-document/types'
import { ProjectDocumentStatusMap } from '@/entities/project-document/config'
import { STATUS_COLORS_FALLBACK, StatusPill, useStatusColors } from '@/shared/components/status-pill'

const props = defineProps<{
    status: ProjectDocumentStatusValue | null | undefined
}>()

const meta = computed(() => (props.status ? (ProjectDocumentStatusMap[props.status] ?? null) : null))
const colors = useStatusColors(() => meta.value?.colors ?? STATUS_COLORS_FALLBACK)
</script>

<template>
    <StatusPill :label="meta?.label ?? 'None'" :colors="colors" title="Status" />
</template>
