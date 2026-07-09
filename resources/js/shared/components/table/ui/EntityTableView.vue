<script setup lang="ts" generic="T extends Record<string, unknown>">
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import type { PaginationMeta } from '@/shared/types'
import { PAGE_SIZE } from '@/app/config'
import type { EntityTableColumnDef } from '../entity-table.types'

const props = withDefaults(
    defineProps<{
        rows: T[]
        columns: EntityTableColumnDef[]
        isPending: boolean
        paginationMeta?: PaginationMeta
        page: number
        rowClickable?: boolean
        selectionMode?: 'multiple'
        dataKey?: string
    }>(),
    { dataKey: 'id' }
)

const selection = defineModel<T[]>('selection', { default: () => [] })

const emit = defineEmits<{
    pageChange: [page: number]
    rowClick: [row: T]
}>()

function onRowClick(event: { data: T }) {
    emit('rowClick', event.data)
}

function onPageChange(event: { page: number }) {
    emit('pageChange', event.page + 1)
}
</script>

<template>
    <DataTable
        v-model:selection="selection"
        :value="props.rows"
        :loading="props.isPending"
        :selection-mode="props.selectionMode"
        :data-key="props.selectionMode ? props.dataKey : undefined"
        lazy
        striped-rows
        class="p-0 w-full"
        :class="{ 'cursor-pointer': props.rowClickable }"
        scrollable
        scroll-height="flex"
        size="small"
        :row-hover="props.rowClickable"
        pt:footer:class="p-0 border-none"
        @row-click="props.rowClickable ? onRowClick($event) : undefined"
    >
        <Column v-if="props.selectionMode === 'multiple'" selection-mode="multiple" header-style="width: 3rem" />

        <Column v-if="$slots.actions" style="width: 3rem">
            <template #body="{ data }">
                <slot name="actions" :row="data as T" />
            </template>
        </Column>

        <Column
            v-for="col in props.columns"
            :key="col.field"
            :field="col.field"
            :header="col.header"
            :style="col.style"
        >
            <template v-if="$slots[`column:${col.field}`]" #body="{ data }">
                <slot :name="`column:${col.field}`" :row="data as T" />
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
    </DataTable>
</template>
