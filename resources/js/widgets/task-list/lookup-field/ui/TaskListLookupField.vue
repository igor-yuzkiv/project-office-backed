<script setup lang="ts">
import { computed, ref } from 'vue'
import { refDebounced } from '@vueuse/core'
import { LookupField } from '@/shared/components/input'
import type { ITaskList } from '@/entities/task-list/types'
import { useTaskListsSearchQuery } from '@/entities/task-list/queries'
import type { FilterPayloadItem } from '@/shared/filters'

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
    computed(() => {
        const filters: FilterPayloadItem[] = []
        if (props.projectId) {
            filters.push({
                filter_key: 'text',
                field_name: 'project_id',
                value: props.projectId,
                matchMode: 'equals',
                params: {},
            })
        }
        return { query: debouncedSearchTerm.value, filters, per_page: 20, page: 1 }
    })
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
