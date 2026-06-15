<script setup lang="ts">
import { computed, ref } from 'vue'
import { refDebounced } from '@vueuse/core'
import { LookupField } from '@/shared/components/input'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { useProjectsSearchQuery } from '@/entities/project/queries'

withDefaults(
    defineProps<{
        object?: boolean
        disabled?: boolean
    }>(),
    { object: false, disabled: false }
)

const modelValue = defineModel<ProjectOverviewDto | string | null>({ required: true })

const searchTerm = ref('')
const debouncedSearchTerm = refDebounced(searchTerm, 300)

const { projects, isPending } = useProjectsSearchQuery(
    computed(() => ({ query: debouncedSearchTerm.value, per_page: 20, page: 1 }))
)

function optionLabel(project: ProjectOverviewDto): string {
    return `${project.prefix} - ${project.name}`
}

function onUpdate(value: unknown) {
    modelValue.value = value as ProjectOverviewDto | string | null
}
</script>

<template>
    <LookupField
        :model-value="modelValue"
        :options="projects"
        :option-label="optionLabel"
        :object="object"
        :loading="isPending"
        :disabled="disabled"
        placeholder="Search projects..."
        @update:model-value="onUpdate"
        @search="searchTerm = $event"
    />
</template>
