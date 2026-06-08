<script setup lang="ts">
import { computed, ref } from 'vue'
import { refDebounced } from '@vueuse/core'
import { LookupField } from '@/shared/components/input'
import type { IProject } from '@/entities/project/types'
import { useProjectsSearchQuery } from '@/entities/project/queries'

withDefaults(
    defineProps<{
        object?: boolean
    }>(),
    { object: false }
)

const modelValue = defineModel<IProject | string | null>({ required: true })

const searchTerm = ref('')
const debouncedSearchTerm = refDebounced(searchTerm, 300)

const { projects, isPending } = useProjectsSearchQuery(
    computed(() => ({ query: debouncedSearchTerm.value, per_page: 20, page: 1 }))
)

function optionLabel(project: IProject): string {
    return `${project.prefix} - ${project.name}`
}

function onUpdate(value: unknown) {
    modelValue.value = value as IProject | string | null
}
</script>

<template>
    <LookupField
        :model-value="modelValue"
        :options="projects"
        :option-label="optionLabel"
        :object="object"
        :loading="isPending"
        placeholder="Search projects..."
        @update:model-value="onUpdate"
        @search="searchTerm = $event"
    />
</template>
