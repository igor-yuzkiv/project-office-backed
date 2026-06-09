<script setup lang="ts">
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import type { IAttachment } from '@/entities/attachment/types'
import type { PaginationMeta } from '@/shared/types'
import { PAGE_SIZE } from '@/app/config'
import { formatFileSize } from '@/shared/utils/file.util'

interface Props {
    attachments: IAttachment[]
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
        :value="props.attachments"
        :loading="props.isPending"
        lazy
        striped-rows
        class="p-0 w-full"
        scrollable
        scroll-height="flex"
        size="small"
        pt:footer:class="p-0 border-none"
    >
        <Column field="original_name" header="Name" />
        <Column field="role" header="Role" />
        <Column field="extension" header="Type">
            <template #body="{ data }">
                {{ data.extension?.toLowerCase() ?? '' }}
            </template>
        </Column>
        <Column field="size_bytes" header="Size">
            <template #body="{ data }">
                {{ formatFileSize(data.size_bytes) }}
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
