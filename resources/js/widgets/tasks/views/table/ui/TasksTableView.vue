<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import type { TaskOverviewDto } from '@/entities/task/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import { computed } from 'vue'
import { TagList } from '@/widgets/tags/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'

const props = defineProps<{
    tasks: TaskOverviewDto[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    columns?: EntityTableColumnDef[]
    to?: (task: TaskOverviewDto) => RouteLocationRaw
}>()

defineEmits<{
    rowClick: [task: TaskOverviewDto]
    pageChange: [page: number]
}>()

const defaultColumns = computed<EntityTableColumnDef[]>(() => {
    if (props.columns) {
        return props.columns
    }

    return [
        { field: 'key', header: 'Key', style: 'min-width: 10rem' },
        { field: 'status', header: 'Status', style: 'min-width: 9rem' },
        { field: 'name', header: 'Task Name', style: 'min-width: 30rem' },
        { field: 'project', header: 'Project', style: 'min-width: 15rem' },
        { field: 'priority', header: 'Priority', style: 'min-width: 7rem' },
        { field: 'tags', header: 'Tags', style: 'min-width: 12rem' },
        { field: 'updated_by', header: 'Updated By', style: 'min-width: 12rem' },
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
            <TaskStatusTag :status="row.status" class="w-fit" />
        </template>

        <template #column:priority="{ row }">
            <TaskPriorityTag :priority="row.priority" class="w-fit" />
        </template>

        <template #column:start_date="{ row }">
            <DisplayDate :date="row.start_date ?? undefined" />
        </template>

        <template #column:due_date="{ row }">
            <DisplayDate :date="row.due_date ?? undefined" />
        </template>

        <template #column:tags="{ row }">
            <TagList v-if="row.tags" :tags="row.tags" inline />
        </template>

        <template #column:created_at="{ row }">
            <DisplayDate :date="row.created_at" />
        </template>

        <template #column:updated_by="{ row }">
            <div v-if="row.updated_by" class="gap-2 flex items-center">
                <UserAvatar :initials="row.updated_by.initials" :avatar-url="row.updated_by.avatar_url" size="small" />
                <span class="text-surface-700 dark:text-surface-300">{{ row.updated_by.name }}</span>
            </div>
        </template>
    </EntityTableView>
</template>
