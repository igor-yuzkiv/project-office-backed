<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import type { ITaskList } from '@/entities/task-list/types'
import { taskListTableColumnDefs } from '@/entities/task-list/config'
import { taskListProgress } from '@/entities/task-list/lib'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyableKey } from '@/shared/components/display'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'
import TaskListProgressCell from './TaskListProgressCell.vue'

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
    (e: 'rowClick', taskList: ITaskList): void
    (e: 'pageChange', page: number): void
}>()

function countLabel(total: number | undefined) {
    return total === undefined ? undefined : `${total} ${total === 1 ? 'list' : 'lists'}`
}

function tasksLabel(total: number) {
    return `${total} ${total === 1 ? 'task' : 'tasks'}`
}
</script>

<template>
    <EntityTableView
        :rows="taskLists"
        :columns="props.columns ?? taskListTableColumnDefs"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        :count-label="countLabel(paginationMeta?.total)"
        actions-placement="end"
        row-clickable
        :to="to"
        @row-click="$emit('rowClick', $event)"
        @page-change="$emit('pageChange', $event)"
    >
        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>

        <template #column:key="{ row }">
            <CopyableKey :value="row.key" />
        </template>

        <template #column:name="{ row }">
            <div class="min-w-0">
                <div class="font-medium truncate" :title="row.name">{{ row.name }}</div>
                <div class="type-meta-3">{{ tasksLabel(taskListProgress(row.task_status_counts).total) }}</div>
            </div>
        </template>

        <template #column:project="{ row }">
            <RouterLink
                v-if="row.project"
                :to="{ name: 'project-details', params: { id: row.project_id } }"
                class="text-ink-2 hover:text-ink block truncate"
                :title="`${row.project.prefix} - ${row.project.name}`"
                @click.stop
            >
                {{ row.project.name }}
            </RouterLink>
        </template>

        <template #column:status="{ row }">
            <TaskListStatusTag :status="row.status" class="w-fit" />
        </template>

        <template #column:progress="{ row }">
            <TaskListProgressCell :counts="row.task_status_counts" />
        </template>

        <template #column:updated_at="{ row }">
            <div class="gap-1.5 text-ink-2 flex items-center whitespace-nowrap">
                <UserAvatar
                    v-if="row.updated_by"
                    :initials="row.updated_by.initials"
                    :avatar-url="row.updated_by.avatar_url"
                    size="small"
                />
                <span :title="row.updated_at">{{ formatRelativeTime(row.updated_at) }}</span>
            </div>
        </template>

        <template #empty>
            <slot name="empty">
                <div class="type-meta-3 py-6 text-center">No task lists found.</div>
            </slot>
        </template>
    </EntityTableView>
</template>
