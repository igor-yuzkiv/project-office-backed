<script setup lang="ts">
import type { IAttachment } from '@/entities/attachment/types'
import type { PaginationMeta } from '@/shared/types'
import { EntityTableView, type EntityTableColumnDef } from '@/shared/components/table'
import { formatFileSize } from '@/shared/utils/file.util'

defineProps<{
    attachments: IAttachment[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
}>()

defineEmits<{
    pageChange: [page: number]
}>()

const columns: EntityTableColumnDef[] = [
    { field: 'original_name', header: 'Name' },
    { field: 'role', header: 'Role' },
    { field: 'extension', header: 'Type' },
    { field: 'size_bytes', header: 'Size' },
]
</script>

<template>
    <EntityTableView
        :rows="attachments"
        :columns="columns"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        @page-change="$emit('pageChange', $event)"
    >
        <template #column:extension="{ row }">
            {{ row.extension?.toLowerCase() ?? '' }}
        </template>

        <template #column:size_bytes="{ row }">
            {{ formatFileSize(row.size_bytes) }}
        </template>

        <template v-if="$slots.actions" #actions="{ row }">
            <slot name="actions" :row="row" />
        </template>
    </EntityTableView>
</template>
