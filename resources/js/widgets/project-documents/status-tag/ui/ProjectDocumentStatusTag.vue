<script setup lang="ts">
import { computed } from 'vue'
import type { ProjectDocumentStatusValue } from '@/entities/project-document/types'
import { ProjectDocumentStatusMap } from '@/entities/project-document/config'
import Tag from 'primevue/tag'

const props = withDefaults(
    defineProps<{
        status: ProjectDocumentStatusValue | null | undefined
        variant?: 'light' | 'dark'
    }>(),
    { variant: 'dark' }
)

const meta = computed(() => {
    if (!props.status) return null
    return ProjectDocumentStatusMap[props.status] ?? null
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
        {{ meta?.label ?? 'None' }}
    </Tag>
</template>
