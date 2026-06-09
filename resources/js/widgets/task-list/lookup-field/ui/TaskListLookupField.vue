<script setup lang="ts">
import { computed, ref } from 'vue'
import { refDebounced } from '@vueuse/core'
import { LookupField } from '@/shared/components/input'
import type { ITaskList } from '@/entities/task-list/types'
import { useTaskListsSearchQuery } from '@/entities/task-list/queries'

const props = withDefaults(
    defineProps<{
        projectId?: string
        object?: boolean
    }>(),
    { object: false }
)

const modelValue = defineModel<ITaskList | string | null>({ required: true })

const searchTerm = ref('')
const debouncedSearchTerm = refDebounced(searchTerm, 300)

const { taskLists, isPending } = useTaskListsSearchQuery(
    computed(() => ({
        query: debouncedSearchTerm.value,
        project_id: props.projectId,
        per_page: 20,
        page: 1,
    }))
)

function onUpdate(value: unknown) {
    modelValue.value = value as ITaskList | string | null
}
</script>

<template>
    <LookupField
        :model-value="modelValue"
        :options="taskLists"
        option-label="name"
        :object="object"
        :loading="isPending"
        placeholder="Search task lists..."
        @update:model-value="onUpdate"
        @search="searchTerm = $event"
    />
</template>
