<script setup lang="ts">
import type { ITask } from '@/entities/task/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import { computed } from 'vue'

const props = defineProps<{
    tasks: ITask[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    columns?: EntityTableColumnDef[]
}>()

defineEmits<{
    rowClick: [task: ITask]
    pageChange: [page: number]
}>()

const defaultColumns = computed<EntityTableColumnDef[]>(() => {
    if (props.columns) {
        return props.columns
    }

    return [
        { field: 'key', header: 'Key', style: 'width: 10rem' },
        { field: 'name', header: 'Task Name' },
        { field: 'project', header: 'Project', style: 'width: 12rem; min-width: 0' },
        { field: 'status', header: 'Status', style: 'width: 9rem' },
        { field: 'priority', header: 'Priority', style: 'width: 7rem' },
        { field: 'created_at', header: 'Created', style: 'width: 12rem' },
    ]
})
</script>

<template>
    <EntityTableView
        :rows="tasks"
        :columns="props.columns ?? defaultColumns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        row-clickable
        @row-click="$emit('rowClick', $event)"
        @page-change="$emit('pageChange', $event)"
    >
        <template #column:key="{ row }">
            <CopyToClipboard :text="row.key" class="text-surface-500" />
        </template>

        <template #column:project="{ row }">
            <RouterLink
                v-if="row.project"
                :to="{ name: 'project-details', params: { id: row.project_id } }"
                class="app-link block truncate"
                :title="`${row.project.prefix} - ${row.project.name}`"
            >
                {{ row.project.prefix }} - {{ row.project.name }}
            </RouterLink>
        </template>

        <template #column:status="{ row }">
            <TaskStatusTag :status="row.status" class="w-full" />
        </template>

        <template #column:priority="{ row }">
            <TaskPriorityTag :priority="row.priority" class="w-full" />
        </template>

        <template #column:created_at="{ row }">
            <DisplayDate :date="row.created_at" />
        </template>

        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>
    </EntityTableView>
</template>
