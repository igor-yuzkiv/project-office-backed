<script setup lang="ts">
import { computed, ref } from 'vue'
import { PAGE_SIZE } from '@/app/config'
import { useTasksSearchQuery } from '@/entities/task/queries'
import type { TaskSearchParams } from '@/entities/task/types'
import { taskTableColumnsExcluding } from '@/entities/task/config'
import type { FilterPayloadItem } from '@/shared/filters'
import { TasksTableView } from '@/widgets/tasks/views/table'

const props = defineProps<{
    taskListId: string
}>()

const page = ref(1)

const tableColumnsDef = taskTableColumnsExcluding('project')

const searchParams = computed<TaskSearchParams>(() => {
    const taskListFilter: FilterPayloadItem = {
        filter_key: 'lookup',
        field_name: 'task_list_id',
        value: props.taskListId,
        matchMode: null,
        params: {},
    }
    return {
        filters: [taskListFilter],
        page: page.value,
        per_page: PAGE_SIZE,
    }
})

const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams)

function onPageChange(newPage: number) {
    page.value = newPage
}
</script>

<template>
    <TasksTableView
        :tasks="tasks"
        :is-pending="isPending"
        :pagination-meta="paginationMeta"
        :page="page"
        :columns="tableColumnsDef"
        @page-change="onPageChange"
    />
</template>
