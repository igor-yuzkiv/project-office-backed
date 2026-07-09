<script setup lang="ts">
import type { ProjectDocumentOverviewDto, ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TagList } from '@/widgets/tags/metadata'

withDefaults(
    defineProps<{
        documents: (ProjectDocumentOverviewDto | ProjectDocumentTreeNodeDto)[]
        isPending: boolean
        paginationMeta?: PaginationMeta
        page: number
        emptyMessage?: string
    }>(),
    { emptyMessage: 'No documents found.' }
)

defineEmits<{
    (e: 'pageChange', page: number): void
}>()

const columns: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 10rem' },
    { field: 'title', header: 'Title' },
    { field: 'status', header: 'Status', style: 'width: 10rem' },
    { field: 'tags', header: 'Tags', style: 'width: 14rem' },
    { field: 'updated_at', header: 'Updated At', style: 'width: 12rem' },
]
</script>

<template>
    <EntityTableView
        :rows="documents"
        :columns="columns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        @page-change="$emit('pageChange', $event)"
    >
        <template #column:key="{ row }">
            <CopyToClipboard :text="row.key" hide-copy-icon class="text-surface-500" />
        </template>

        <template #column:title="{ row }">
            <RouterLink :to="{ name: 'project-document-details', params: { id: row.id } }" class="app-link">
                {{ row.title }}
            </RouterLink>
        </template>

        <template #column:status="{ row }">
            <ProjectDocumentStatusTag :status="row.status" class="w-fit" />
        </template>

        <template #column:tags="{ row }">
            <TagList v-if="row.tags" :tags="row.tags" inline />
        </template>

        <template #column:updated_at="{ row }">
            <DisplayDate v-if="row.updated_at" :date="row.updated_at" />
        </template>

        <template #empty>
            <div class="py-6 text-sm text-surface-400 text-center">{{ emptyMessage }}</div>
        </template>
    </EntityTableView>
</template>
