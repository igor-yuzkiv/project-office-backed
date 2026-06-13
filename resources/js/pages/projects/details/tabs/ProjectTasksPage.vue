<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { PAGE_SIZE } from '@/app/config'
import { useProjectQuery } from '@/entities/project/queries'
import { useTasksSearchQuery } from '@/entities/task/queries'
import { useDeleteTaskMutation } from '@/entities/task/mutations'
import type { ITask, TaskSearchParams } from '@/entities/task/types'
import type { FilterPayloadItem } from '@/shared/filters'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { Icon } from '@iconify/vue'
import type { EntityTableColumnDef } from '@/shared/components/table'

const route = useRoute()
const router = useRouter()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

const tableColumnsDef: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 10rem' },
    { field: 'name', header: 'Task Name' },
    { field: 'status', header: 'Status', style: 'width: 9rem' },
    { field: 'priority', header: 'Priority', style: 'width: 7rem' },
    { field: 'created_at', header: 'Created', style: 'width: 12rem' },
]

const searchParams = computed<TaskSearchParams>(() => ({
    query: searchQuery.value,
    filters: [
        {
            filter_key: 'lookup',
            field_name: 'project_id',
            value: projectId,
            matchMode: null,
            params: {},
        } satisfies FilterPayloadItem,
    ],
    page: page.value,
    per_page: PAGE_SIZE,
}))

const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams)

const taskCreateDialog = useTaskCreateDialog()
const { mutateWithConfirm: deleteTask } = useDeleteTaskMutation()

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedTask = ref<ITask>()

const rowMenuItems: MenuItem[] = [
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        command: () => router.push({ name: 'task-edit', params: { id: selectedTask.value!.id } }),
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        command: () =>
            deleteTask(selectedTask.value!.id, `Are you sure you want to delete "${selectedTask.value!.name}"?`),
    },
]

function openRowMenu(event: MouseEvent, task: ITask) {
    selectedTask.value = task
    rowMenu.value?.toggle(event)
}

function onRowClick(task: ITask) {
    router.push({ name: 'task-details', params: { id: task.id } })
}

function onSearchSubmit() {
    searchQuery.value = searchInput.value
    page.value = 1
}

function onPageChange(newPage: number) {
    page.value = newPage
}
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-between">
                <SearchInput v-model="searchInput" placeholder="Search tasks..." @submit="onSearchSubmit" />
                <Button
                    severity="info"
                    outlined
                    text
                    label="New Task"
                    :disabled="!project"
                    @click="project && taskCreateDialog.open(project)"
                >
                    <template #icon>
                        <Icon icon="material-symbols:add" class="text-lg" />
                    </template>
                </Button>
            </div>
            <div class="flex h-full w-full flex-col overflow-hidden">
                <TasksTableView
                    :tasks="tasks"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    @row-click="onRowClick"
                    @page-change="onPageChange"
                    :columns="tableColumnsDef"
                >
                    <template #actions="{ row }">
                        <IconButton icon="material-symbols-light:more-vert" @click.stop="openRowMenu($event, row)" />
                    </template>
                </TasksTableView>
            </div>
        </div>

        <Menu ref="rowMenu" :model="rowMenuItems" popup />

        <TaskCreateDialog
            v-model:visible="taskCreateDialog.visible.value"
            v-model:form-data="taskCreateDialog.formData.value"
            :validation-errors="taskCreateDialog.validationErrors.value"
            :is-pending="taskCreateDialog.isPending.value"
            @submit="taskCreateDialog.submit()"
        />
    </div>
</template>
