<script setup lang="ts" generic="T extends Record<string, unknown>">
import TreeTable from 'primevue/treetable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import type { TreeNode } from 'primevue/treenode'
import type { PaginationMeta } from '@/shared/types'
import { PAGE_SIZE } from '@/app/config'
import type { EntityTreeNode, EntityTreeTableColumnDef } from '../entity-tree-table.types'

const props = defineProps<{
    nodes: EntityTreeNode<T>[]
    columns: EntityTreeTableColumnDef[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
    expandedKeys: Record<string, boolean>
}>()

const emit = defineEmits<{
    nodeExpand: [node: EntityTreeNode<T>]
    nodeCollapse: [node: EntityTreeNode<T>]
    pageChange: [page: number]
}>()

function onNodeExpand(node: TreeNode) {
    emit('nodeExpand', node as EntityTreeNode<T>)
}

function onNodeCollapse(node: TreeNode) {
    emit('nodeCollapse', node as EntityTreeNode<T>)
}

function onPageChange(event: { page: number }) {
    emit('pageChange', event.page + 1)
}
</script>

<template>
    <TreeTable
        :value="props.nodes"
        :expanded-keys="props.expandedKeys"
        :loading="props.isPending"
        lazy
        class="p-0 w-full"
        scrollable
        scroll-height="flex"
        size="small"
        pt:footer:class="p-0 border-none"
        @node-expand="onNodeExpand"
        @node-collapse="onNodeCollapse"
    >
        <Column
            v-for="col in props.columns"
            :key="col.field"
            :field="col.field"
            :header="col.header"
            :style="col.style"
            :expander="col.expander"
        >
            <template v-if="$slots[`column:${col.field}`]" #body="{ node }">
                <slot
                    :name="`column:${col.field}`"
                    :row="(node as EntityTreeNode<T>).data"
                    :node="node as EntityTreeNode<T>"
                />
            </template>
        </Column>

        <template #empty>
            <slot name="empty">
                <div class="py-6 text-sm text-surface-400 text-center">No records found.</div>
            </slot>
        </template>

        <template #footer>
            <Paginator
                v-if="props.paginationMeta && props.paginationMeta.last_page > 1"
                :rows="PAGE_SIZE"
                :total-records="props.paginationMeta.total"
                :first="(props.page - 1) * PAGE_SIZE"
                pt:root:class="py-0.5 px-2"
                @page="onPageChange"
            >
                <template #start>
                    <span class="text-sm text-surface-500">
                        Page {{ props.page }} of {{ props.paginationMeta?.last_page }}
                    </span>
                </template>
                <template #end>
                    <span class="text-sm text-surface-500"> Total Records: {{ props.paginationMeta?.total }} </span>
                </template>
            </Paginator>
        </template>
    </TreeTable>
</template>
