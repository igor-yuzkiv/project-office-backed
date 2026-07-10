<script setup lang="ts">
import type { RouteLocationRaw } from 'vue-router'
import type { ProjectOverviewDto } from '@/entities/project/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { DisplayDate } from '@/shared/components/display'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { ProjectIcon } from '@/widgets/projects/project-icon'
import { UserAvatar } from '@/widgets/user/user-avatar'

defineProps<{
    projects: ProjectOverviewDto[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    to?: (project: ProjectOverviewDto) => RouteLocationRaw
}>()

defineEmits<{
    rowClick: [project: ProjectOverviewDto]
    pageChange: [page: number]
}>()

const columns: EntityTableColumnDef[] = [
    { field: 'prefix', header: 'Prefix', style: 'width: 6rem' },
    { field: 'status', header: 'Status', style: 'width: 8rem' },
    { field: 'name', header: 'Project Name' },
    { field: 'tags', header: 'Tags' },
    { field: 'updated_by', header: 'Updated By', style: 'width: 12rem' },
    { field: 'created_at', header: 'Created', style: 'width: 12rem' },
]
</script>

<template>
    <EntityTableView
        :rows="projects"
        :columns="columns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        row-clickable
        :to="to"
        @row-click="$emit('rowClick', $event)"
        @page-change="$emit('pageChange', $event)"
    >
        <template #column:prefix="{ row }">
            <ProjectIcon :prefix="row.prefix" :status="row.status" size="medium" />
        </template>
        <template #column:status="{ row }">
            <ProjectStatusTag :status="row.status" />
        </template>

        <template #column:tags="{ row }">
            <TagList v-if="row.tags" :tags="row.tags" inline />
        </template>

        <template #column:updated_by="{ row }">
            <div v-if="row.updated_by" class="gap-2 flex items-center">
                <UserAvatar :initials="row.updated_by.initials" :avatar-url="row.updated_by.avatar_url" size="small" />
                <span class="text-surface-700 dark:text-surface-300">{{ row.updated_by.name }}</span>
            </div>
        </template>

        <template #column:created_at="{ row }">
            <DisplayDate :date="row.created_at" />
        </template>

        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>
    </EntityTableView>
</template>
