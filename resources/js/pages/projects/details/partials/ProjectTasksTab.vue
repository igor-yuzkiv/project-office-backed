<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import { useTasksSearchQuery } from '@/entities/task/queries'
import type { ITask } from '@/entities/task/types'
import type { FilterPayloadItem } from '@/shared/filters'
import { PAGE_SIZE } from '@/app/config'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'

interface Props {
    projectId: string
}

const props = defineProps<Props>()

const router = useRouter()
const page = ref(1)

const searchParams = computed(() => ({
    filters: [
        {
            filter_key: 'lookup',
            field_name: 'project_id',
            value: props.projectId,
            matchMode: null,
            params: {},
        } satisfies FilterPayloadItem,
    ],
    page: page.value,
    per_page: PAGE_SIZE,
}))

const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams)

function onRowClick(event: { data: ITask }) {
    router.push({ name: 'task-details', params: { id: event.data.id } })
}

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}
</script>

<template>
    <DataTable
        :value="tasks"
        :loading="isPending"
        lazy
        striped-rows
        class="p-0 w-full cursor-pointer"
        row-hover
        size="small"
        @row-click="onRowClick"
        pt:footer:class="p-0 border-none"
    >
        <Column field="key" header="Key" style="width: 10rem">
            <template #body="{ data }">
                <CopyToClipboard :text="data.key" class="text-surface-500" />
            </template>
        </Column>
        <Column field="name" header="Task Name" />
        <Column field="status" header="Status" style="width: 9rem">
            <template #body="{ data }">
                <TaskStatusTag :status="data.status" class="w-full" />
            </template>
        </Column>
        <Column field="priority.name" header="Priority" style="width: 7rem">
            <template #body="{ data }">
                <TaskPriorityTag :priority="data.priority" class="w-full" />
            </template>
        </Column>
        <Column field="created_at" header="Created" style="width: 12rem">
            <template #body="{ data }">
                <DisplayDate :date="data.created_at" />
            </template>
        </Column>

        <template #footer>
            <Paginator
                v-if="paginationMeta && paginationMeta.last_page > 1"
                :rows="PAGE_SIZE"
                :total-records="paginationMeta.total"
                :first="(page - 1) * PAGE_SIZE"
                @page="onPageChange"
                pt:root:class="p-0"
            />
        </template>
    </DataTable>
</template>
