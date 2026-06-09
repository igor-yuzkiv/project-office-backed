<script setup lang="ts">
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import type { ITask } from '@/entities/task/types'
import type { PaginationMeta } from '@/shared/types'
import { PAGE_SIZE } from '@/app/config'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'

interface Props {
    tasks: ITask[]
    isPending: boolean
    paginationMeta?: PaginationMeta
    page: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
    rowClick: [task: ITask]
    pageChange: [page: number]
}>()

function onRowClick(event: { data: ITask }) {
    emit('rowClick', event.data)
}

function onPageChange(event: { page: number }) {
    emit('pageChange', event.page + 1)
}
</script>

<template>
    <DataTable
        :value="props.tasks"
        :loading="props.isPending"
        lazy
        striped-rows
        class="p-0 w-full cursor-pointer"
        row-hover
        scrollable
        scroll-height="flex"
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
        <Column field="project.name" header="Project" style="width: 12rem; min-width: 0">
            <template #body="{ data }">
                <RouterLink
                    v-if="data.project"
                    :to="{ name: 'project-details', params: { id: data.project_id } }"
                    class="text-primary-500 block truncate hover:underline"
                    :title="`${data.project.prefix} - ${data.project.name}`"
                >
                    {{ data.project.prefix }} - {{ data.project.name }}
                </RouterLink>
            </template>
        </Column>
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
