<script setup lang="ts">
import type { ITaskList } from '@/entities/task-list/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { DisplayDate } from '@/shared/components/display'

defineProps<{
    taskLists: ITaskList[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    expandable?: boolean
}>()

defineEmits<{
    pageChange: [page: number]
}>()

const expandedRows = defineModel<ITaskList[]>('expandedRows', { default: () => [] })

const columns: EntityTableColumnDef[] = [
    { field: 'name', header: 'Name' },
    { field: 'tasks_count', header: 'Tasks', style: 'width: 8rem' },
    { field: 'created_at', header: 'Created', style: 'width: 12rem' },
]
</script>

<template>
    <EntityTableView
        v-model:expanded-rows="expandedRows"
        :rows="taskLists"
        :columns="columns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        :expandable="expandable"
        @page-change="$emit('pageChange', $event)"
    >
        <template v-if="$slots.expansion" #expansion="{ row }">
            <slot name="expansion" :row="row" />
        </template>

        <template #column:tasks_count="{ row }">
            <span :class="(row.tasks_count ?? 0) === 0 ? 'text-surface-400' : ''">
                {{ row.tasks_count ?? 0 }}
            </span>
        </template>

        <template #column:created_at="{ row }">
            <DisplayDate :date="row.created_at" />
        </template>

        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>
    </EntityTableView>
</template>
