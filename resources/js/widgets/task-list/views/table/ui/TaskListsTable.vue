<script setup lang="ts">
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import type { ITaskList } from '@/entities/task-list/types'
import type { PaginationMeta } from '@/shared/types'
import { PAGE_SIZE } from '@/app/config'
import { DisplayDate } from '@/shared/components/display'

interface Props {
    taskLists: ITaskList[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
    pageChange: [page: number]
}>()

function onPageChange(event: { page: number }) {
    emit('pageChange', event.page + 1)
}
</script>

<template>
    <DataTable
        :value="props.taskLists"
        :loading="props.isPending"
        lazy
        striped-rows
        class="p-0 w-full"
        scrollable
        scroll-height="flex"
        size="small"
        pt:footer:class="p-0 border-none"
    >
        <Column field="name" header="Name" />
        <Column field="tasks_count" header="Tasks" style="width: 8rem">
            <template #body="{ data }">
                <span :class="(data.tasks_count ?? 0) === 0 ? 'text-surface-400' : ''">
                    {{ data.tasks_count ?? 0 }}
                </span>
            </template>
        </Column>
        <Column field="created_at" header="Created" style="width: 12rem">
            <template #body="{ data }">
                <DisplayDate :date="data.created_at" />
            </template>
        </Column>
        <Column v-if="$slots.actions" style="width: 3rem">
            <template #body="{ data }">
                <slot name="actions" :row="data" />
            </template>
        </Column>

        <template #footer>
            <Paginator
                v-if="props.paginationMeta && props.paginationMeta.last_page > 1"
                :rows="PAGE_SIZE"
                :total-records="props.paginationMeta.total"
                :first="(props.page - 1) * PAGE_SIZE"
                @page="onPageChange"
                pt:root:class="p-0"
            />
        </template>
    </DataTable>
</template>
