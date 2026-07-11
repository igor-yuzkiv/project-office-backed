<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import type { PaginationMeta } from '@/shared/types'
import type { ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import { EntityTreeTableView, type EntityTreeNode, type EntityTreeTableColumnDef } from '@/shared/components/table'
import { ProjectDocumentStatusTag } from '@/widgets/project-documents/status-tag'
import { TagList } from '@/widgets/tags/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'

const props = defineProps<{
    treeNodes: EntityTreeNode<ProjectDocumentTreeNodeDto>[]
    paginationMeta?: PaginationMeta
    isPending: boolean
    page: number
    expandedKeys: Record<string, boolean>
    columns?: EntityTreeTableColumnDef[]
    // Selection mode: render the title as a pickable button instead of a link to details.
    selectable?: boolean
    selectedId?: string | null
    disabledId?: string | null
}>()

const emit = defineEmits<{
    (e: 'expand-node', nodeId: string): void
    (e: 'collapse-node', nodeId: string): void
    (e: 'page-change', page: number): void
    (e: 'select-node', node: ProjectDocumentTreeNodeDto): void
}>()

const defaultColumns = computed<EntityTreeTableColumnDef[]>(() => {
    if (props.columns) {
        return props.columns
    }

    return [
        { field: 'title', header: 'Title', expander: true },
        { field: 'key', header: 'Key', style: 'width: 14rem' },
        { field: 'status', header: 'Status', style: 'width: 12rem' },
        { field: 'tags', header: 'Tags' },
        { field: 'updated_by', header: 'Updated By', style: 'width: 12rem' },
        { field: 'updated_at', header: 'Updated At', style: 'width: 10rem' },
    ]
})

function onNodeExpand(node: EntityTreeNode<ProjectDocumentTreeNodeDto>) {
    emit('expand-node', node.key)
}

function onNodeCollapse(node: EntityTreeNode<ProjectDocumentTreeNodeDto>) {
    emit('collapse-node', node.key)
}

function onPageChange(page: number) {
    emit('page-change', page)
}

function onSelectNode(row: ProjectDocumentTreeNodeDto) {
    emit('select-node', row)
}
</script>

<template>
    <EntityTreeTableView
        :nodes="treeNodes"
        :columns="props.columns ?? defaultColumns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        :expanded-keys="expandedKeys"
        @node-expand="onNodeExpand"
        @node-collapse="onNodeCollapse"
        @page-change="onPageChange"
    >
        <template #column:title="{ row }">
            <button
                v-if="selectable"
                type="button"
                class="app-link disabled:text-surface-400 gap-2 flex items-center disabled:cursor-not-allowed"
                :class="{ 'font-semibold text-primary-600 dark:text-primary-400': row.id === selectedId }"
                :disabled="row.id === disabledId"
                @click="onSelectNode(row)"
            >
                <Icon :icon="row.has_children ? 'heroicons:folder' : 'heroicons:document-text'" class="text-lg" />

                <span>{{ row.title }}</span>
                <span v-if="row.has_children" class="text-xs text-surface-400">folder</span>
            </button>

            <RouterLink
                v-else
                :to="{ name: 'project-document-details', params: { id: row.id } }"
                class="app-link gap-2 flex items-center"
            >
                <Icon :icon="row.has_children ? 'heroicons:folder' : 'heroicons:document-text'" class="text-lg" />

                <span>{{ row.title }}</span>
                <span v-if="row.has_children" class="text-xs text-surface-400">folder</span>
            </RouterLink>
        </template>

        <template #column:key="{ row }">
            <CopyToClipboard :text="row.key" class="text-surface-500" />
        </template>

        <template #column:status="{ row }">
            <ProjectDocumentStatusTag :status="row.status" variant="light" />
        </template>

        <template #column:tags="{ row }">
            <TagList :tags="row.tags ?? []" />
        </template>

        <template #column:updated_by="{ row }">
            <div v-if="row.updated_by" class="gap-2 flex items-center">
                <UserAvatar :initials="row.updated_by.initials" :avatar-url="row.updated_by.avatar_url" size="small" />
                <span class="text-surface-700 dark:text-surface-300">{{ row.updated_by.name }}</span>
            </div>
        </template>

        <template #column:updated_at="{ row }">
            <DisplayDate :date="row.updated_at" />
        </template>
    </EntityTreeTableView>
</template>
