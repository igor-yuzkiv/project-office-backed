<script setup lang="ts">
import { computed } from 'vue'
import type { RouteLocationRaw } from 'vue-router'
import type { TaskOverviewDto } from '@/entities/task/types'
import { taskTableColumnDefs } from '@/entities/task/config'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyableKey } from '@/shared/components/display'
import { formatRelativeTime } from '@/shared/utils/relative-time.util'
import { TaskPriorityBars, TaskStatusTag } from '@/widgets/tasks/metadata'
import { TagList } from '@/widgets/tags/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'

const props = defineProps<{
    tasks: TaskOverviewDto[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    /** Defaults to the full column set; drop the ones a context does not need with taskTableColumnsExcluding(). */
    columns?: EntityTableColumnDef[]
    to?: (task: TaskOverviewDto) => RouteLocationRaw
    selectionMode?: 'multiple'
}>()

const selection = defineModel<TaskOverviewDto[]>('selection', { default: () => [] })

defineEmits<{
    (e: 'rowClick', task: TaskOverviewDto): void
    (e: 'pageChange', page: number): void
}>()

const columns = computed<EntityTableColumnDef[]>(() => props.columns ?? taskTableColumnDefs)

function countLabel(total: number | undefined) {
    return total === undefined ? undefined : `${total} ${total === 1 ? 'task' : 'tasks'}`
}
</script>

<template>
    <EntityTableView
        v-model:selection="selection"
        :selection-mode="props.selectionMode"
        :rows="tasks"
        :columns="columns"
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
            <div class="font-medium min-w-0 truncate" :title="row.name">{{ row.name }}</div>
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

        <template #[`column:task_list.name`]="{ row }">
            <RouterLink
                v-if="row.task_list"
                :to="{ name: 'task-list-details', params: { id: row.task_list.id } }"
                class="text-ink-2 hover:text-ink block truncate"
                :title="row.task_list.name"
                @click.stop
            >
                {{ row.task_list.name }}
            </RouterLink>
        </template>

        <template #column:status="{ row }">
            <TaskStatusTag :status="row.status" class="w-fit" />
        </template>

        <template #column:priority="{ row }">
            <TaskPriorityBars :priority="row.priority" />
        </template>

        <template #column:tags="{ row }">
            <TagList v-if="row.tags" :tags="row.tags" inline />
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
                <div class="type-meta-3 py-6 text-center">No tasks found.</div>
            </slot>
        </template>
    </EntityTableView>
</template>
