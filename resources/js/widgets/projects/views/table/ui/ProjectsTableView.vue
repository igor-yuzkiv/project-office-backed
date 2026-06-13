<script setup lang="ts">
import type { IProject } from '@/entities/project/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { DisplayDate } from '@/shared/components/display'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { ProjectIcon } from '@/widgets/projects/project-icon'

defineProps<{
    projects: IProject[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
}>()

defineEmits<{
    rowClick: [project: IProject]
    pageChange: [page: number]
}>()

const columns: EntityTableColumnDef[] = [
    { field: 'prefix', header: 'Prefix', style: 'width: 6rem' },
    { field: 'status', header: 'Status', style: 'width: 8rem' },
    { field: 'name', header: 'Project Name' },
    { field: 'tags', header: 'Tags' },
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

        <template #column:created_at="{ row }">
            <DisplayDate :date="row.created_at" />
        </template>

        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>
    </EntityTableView>
</template>
