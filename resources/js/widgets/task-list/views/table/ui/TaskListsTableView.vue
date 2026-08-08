<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import type { ITaskList } from '@/entities/task-list/types'
import { taskListTableColumnDefs } from '@/entities/task-list/config'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard } from '@/shared/components/display'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { TagList } from '@/widgets/tags/metadata'

const props = defineProps<{
    taskLists: ITaskList[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    /** Defaults to the full column set; drop the ones a context does not need with taskListTableColumnsExcluding(). */
    columns?: EntityTableColumnDef[]
    to?: (taskList: ITaskList) => RouteLocationRaw
}>()

defineEmits<{
    rowClick: [taskList: ITaskList]
    pageChange: [page: number]
}>()
</script>

<template>
    <EntityTableView
        :rows="taskLists"
        :columns="props.columns ?? taskListTableColumnDefs"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        row-clickable
        :to="to"
        @row-click="$emit('rowClick', $event)"
        @page-change="$emit('pageChange', $event)"
    >
        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>

        <template #column:key="{ row }">
            <CopyToClipboard :text="row.key" class="text-surface-500" />
        </template>

        <template #column:status="{ row }">
            <TaskListStatusTag :status="row.status" class="w-fit" />
        </template>

        <template #column:project="{ row }">
            <RouterLink
                v-if="row.project"
                :to="{ name: 'project-details', params: { id: row.project_id } }"
                class="app-link block truncate"
                :title="`${row.project.prefix} - ${row.project.name}`"
                @click.stop
            >
                {{ row.project.prefix }} - {{ row.project.name }}
            </RouterLink>
        </template>

        <template #column:tags="{ row }">
            <TagList v-if="row.tags" :tags="row.tags" inline />
        </template>

        <template #column:tasks_count="{ row }">
            <span :class="(row.tasks_count ?? 0) === 0 ? 'text-surface-400' : ''">
                {{ row.tasks_count ?? 0 }}
            </span>
        </template>

        <template #empty>
            <slot name="empty">
                <div class="py-6 text-sm text-surface-400 text-center">No task lists found.</div>
            </slot>
        </template>
    </EntityTableView>
</template>
