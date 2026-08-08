<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { PAGE_SIZE } from '@/app/config'
import { useProjectQuery } from '@/entities/project/queries'
import { useTaskListsSearchQuery } from '@/entities/task-list/queries'
import { useRouteParams } from '@vueuse/router'
import type { FilterPayloadItem } from '@/shared/filters'
import { useDeleteTaskListMutation } from '@/entities/task-list/mutations'
import type { ITaskList, TaskListSearchParams } from '@/entities/task-list/types'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { TaskListsTableView } from '@/widgets/task-list/views/table'
import { taskListTableColumnsExcluding } from '@/entities/task-list/config'
import { TaskListCreateDialog, useTaskListCreateDialog } from '@/widgets/task-list/create-dialog'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { Icon } from '@iconify/vue'

const router = useRouter()
const projectId = useRouteParams<string>('id')

const { project } = useProjectQuery(projectId)

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

// The project is already the page context, so its column carries nothing here.
const tableColumnsDef = taskListTableColumnsExcluding('project')

const searchParams = computed<TaskListSearchParams>(() => {
    const projectFilter: FilterPayloadItem = {
        filter_key: 'text',
        field_name: 'project_id',
        value: projectId.value,
        matchMode: 'equals',
        params: {},
    }
    return {
        query: searchQuery.value,
        filters: [projectFilter],
        page: page.value,
        per_page: PAGE_SIZE,
        include: ['tags'],
    }
})

const { taskLists, paginationMeta, isPending } = useTaskListsSearchQuery(searchParams)

const createDialog = useTaskListCreateDialog()
const { mutateWithConfirm: deleteTaskList } = useDeleteTaskListMutation()
const taskCreateDialog = useTaskCreateDialog()

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedTaskList = ref<ITaskList>()

const rowMenuItems: MenuItem[] = [
    {
        label: 'New Task',
        icon: 'pi pi-plus',
        command: () => {
            if (project.value && selectedTaskList.value) {
                taskCreateDialog.open(project.value, selectedTaskList.value)
            }
        },
    },
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        command: () => {
            if (project.value && selectedTaskList.value) {
                router.push({ name: 'task-list-edit', params: { id: selectedTaskList.value.id } })
            }
        },
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        command: () =>
            deleteTaskList(
                selectedTaskList.value!.id,
                `Are you sure you want to delete "${selectedTaskList.value!.name}"?`
            ),
    },
]

function openRowMenu(event: MouseEvent, taskList: ITaskList) {
    selectedTaskList.value = taskList
    rowMenu.value?.toggle(event)
}

function taskListDetailsRoute(taskList: ITaskList) {
    return { name: 'task-list-details', params: { id: taskList.id } }
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
                <SearchInput v-model="searchInput" placeholder="Search task lists..." @submit="onSearchSubmit" />
                <Button
                    severity="info"
                    text
                    label="New Task List"
                    :disabled="!project"
                    @click="project && createDialog.open(project)"
                >
                    <template #icon>
                        <Icon icon="material-symbols:add" class="text-lg" />
                    </template>
                </Button>
            </div>
            <div class="flex h-full w-full flex-col overflow-hidden">
                <TaskListsTableView
                    :task-lists="taskLists"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    :columns="tableColumnsDef"
                    :to="taskListDetailsRoute"
                    @page-change="onPageChange"
                >
                    <template #actions="{ row }">
                        <IconButton
                            severity="secondary"
                            icon="pepicons-pop:dots-y"
                            @click.stop="openRowMenu($event, row)"
                        />
                    </template>
                </TaskListsTableView>
            </div>
        </div>

        <Menu ref="rowMenu" :model="rowMenuItems" popup />

        <TaskListCreateDialog
            :visible="createDialog.visible.value"
            :form-data="createDialog.formData.value"
            :validation-errors="createDialog.validationErrors.value"
            :is-pending="createDialog.isPending.value"
            project-locked
            @update:visible="createDialog.visible.value = $event"
            @update:form-data="createDialog.formData.value = $event"
            @submit="createDialog.submit()"
        />

        <TaskCreateDialog
            v-model:visible="taskCreateDialog.visible.value"
            v-model:form-data="taskCreateDialog.formData.value"
            :validation-errors="taskCreateDialog.validationErrors.value"
            :is-pending="taskCreateDialog.isPending.value"
            @submit="taskCreateDialog.submit()"
        />
    </div>
</template>
