<script setup lang="ts" generic="T extends Record<string, unknown>">
import { computed } from 'vue'
import { useRouter, type RouteLocationRaw } from 'vue-router'
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
        /** Resolves a row to its route. When set, clicking a row navigates to it; Ctrl/Cmd-click opens it in a new tab. */
        to?: (row: T) => RouteLocationRaw
        selectionMode?: 'multiple'
        dataKey?: string
        expandable?: boolean
    }>(),
    { dataKey: 'id', expandable: false }
)

const selection = defineModel<T[]>('selection', { default: () => [] })
const expandedRows = defineModel<T[]>('expandedRows', { default: () => [] })

const emit = defineEmits<{
    pageChange: [page: number]
    rowClick: [row: T]
}>()

const router = useRouter()

const isClickable = computed(() => props.rowClickable || !!props.to)

function onRowClick(event: { data: T; originalEvent: Event }) {
    if (!isClickable.value) {
        return
    }

    if (props.to) {
        const target = props.to(event.data)
        const mouseEvent = event.originalEvent as MouseEvent
        if (mouseEvent.ctrlKey || mouseEvent.metaKey) {
            window.open(router.resolve(target).href, '_blank')
        } else {
            router.push(target)
        }
        return
    }

    emit('rowClick', event.data)
}

function onPageChange(event: { page: number }) {
    emit('pageChange', event.page + 1)
}
</script>

<template>
    <DataTable
        v-model:selection="selection"
        v-model:expanded-rows="expandedRows"
        :value="props.rows"
        :loading="props.isPending"
        :selection-mode="props.selectionMode"
        :data-key="props.selectionMode || props.expandable ? props.dataKey : undefined"
        lazy
        striped-rows
        class="p-0 w-full"
        :class="{ 'cursor-pointer': isClickable }"
        scrollable
        scroll-height="flex"
        size="small"
        :row-hover="isClickable"
        pt:footer:class="p-0 border-none"
        @row-click="onRowClick"
    >
        <Column v-if="props.expandable" expander style="width: 3rem" />

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

        <template v-if="props.expandable" #expansion="{ data }">
            <slot name="expansion" :row="data as T" />
        </template>

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
