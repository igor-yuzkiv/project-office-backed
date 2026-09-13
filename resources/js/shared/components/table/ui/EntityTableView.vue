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
        /** Adds a checkbox column. A row body click also selects, unless the row navigates. */
        selectionMode?: 'multiple'
        dataKey?: string
        /** Where the `actions` slot column sits. */
        actionsPlacement?: 'start' | 'end'
        /** Foot text such as "7 lists"; without it the foot only appears when there is more than one page. */
        countLabel?: string
    }>(),
    { dataKey: 'id', actionsPlacement: 'start' }
)

const selection = defineModel<T[]>('selection', { default: () => [] })

const emit = defineEmits<{
    (e: 'pageChange', page: number): void
    (e: 'rowClick', row: T): void
}>()

const router = useRouter()

const isClickable = computed(() => props.rowClickable || !!props.to)
const hasPages = computed(() => !!props.paginationMeta && props.paginationMeta.last_page > 1)

function onRowClick(event: { data: T; originalEvent: Event }) {
    if (!isClickable.value) {
        return
    }

    // PrimeVue emits row-click for the checkbox cell too, so ticking a row would otherwise navigate.
    const target = event.originalEvent.target
    if (target instanceof Element && target.closest('[data-p-selection-column="true"]')) {
        return
    }

    if (props.to) {
        const to = props.to(event.data)
        const mouseEvent = event.originalEvent as MouseEvent
        if (mouseEvent.ctrlKey || mouseEvent.metaKey) {
            window.open(router.resolve(to).href, '_blank')
        } else {
            router.push(to)
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
        :value="props.rows"
        :loading="props.isPending"
        :selection-mode="isClickable ? undefined : props.selectionMode"
        :data-key="props.selectionMode ? props.dataKey : undefined"
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
        <Column v-if="props.selectionMode" selection-mode="multiple" header-style="width: 3rem" />

        <Column v-if="$slots.actions && props.actionsPlacement === 'start'" style="width: 3rem">
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

        <Column v-if="$slots.actions && props.actionsPlacement === 'end'" style="width: 3rem">
            <template #body="{ data }">
                <slot name="actions" :row="data as T" />
            </template>
        </Column>

        <template #empty>
            <slot name="empty">
                <div class="py-6 text-sm text-surface-400 text-center">No records found.</div>
            </slot>
        </template>

        <template v-if="props.countLabel || hasPages" #footer>
            <div class="gap-2 px-2.5 py-2 flex items-center justify-between">
                <span class="type-meta-3">{{
                    props.countLabel ?? `Total Records: ${props.paginationMeta?.total}`
                }}</span>
                <Paginator
                    v-if="hasPages"
                    :rows="PAGE_SIZE"
                    :total-records="props.paginationMeta?.total ?? 0"
                    :first="(props.page - 1) * PAGE_SIZE"
                    pt:root:class="p-0"
                    @page="onPageChange"
                />
            </div>
        </template>
    </DataTable>
</template>
