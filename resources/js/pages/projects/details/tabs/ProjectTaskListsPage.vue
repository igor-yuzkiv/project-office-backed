<script setup lang="ts">
import { computed, ref, watch } from 'vue'
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
import {
    createDefaultTaskListFiltersDefMap,
    taskListSortFieldDefs,
    taskListTableColumnsExcluding,
} from '@/entities/task-list/config'
import { FilterSidebar, FilterButton, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog } from '@/shared/sort'
import { usePersistedListState } from '@/shared/composables'
import { TaskListCreateDialog, useTaskListCreateDialog } from '@/widgets/task-list/create-dialog'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'

const router = useRouter()
const projectId = useRouteParams<string>('id')

const { project } = useProjectQuery(projectId)

// The project is the page's scope, so its filter field is not offered.
const filtersDefMap = createDefaultTaskListFiltersDefMap()
delete filtersDefMap.project_id
const filterSidebar = useFilterSidebar(filtersDefMap)
const sort = useSortDialog(taskListSortFieldDefs, 'updated_at', 'desc')

usePersistedListState(
    {
        filters: filterSidebar.filtersSnapshot,
        sortBy: sort.sortBy,
        sortOrder: sort.sortOrder,
    },
    {
        key: 'project-task-lists',
        validate: (data) =>
            taskListSortFieldDefs.some((f) => f.field === data.sortBy) &&
            (data.sortOrder === 'asc' || data.sortOrder === 'desc'),
    }
)

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
        filters: [projectFilter, ...filterSidebar.resolvedFilters.value],
        page: page.value,
        per_page: PAGE_SIZE,
        sort_by: sort.sortBy.value,
        sort_order: sort.sortOrder.value,
        include: ['tags', 'updatedBy'],
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
        label: 'New task',
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
            if (selectedTaskList.value) {
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

function openCreateDialog() {
    if (!project.value) return

    createDialog.open(project.value)
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

function onSortApply() {
    sort.apply()
    sort.close()
}

watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})
</script>

<template>
    <div class="flex flex-1 flex-col">
        <div class="gap-2 mb-3 flex flex-wrap items-center">
            <SearchInput v-model="searchInput" placeholder="Search lists" @submit="onSearchSubmit" />
            <span class="flex-1" />
            <FilterButton v-bind="filterSidebar.buttonProps.value" />
            <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
            <Button
                label="New task list"
                icon="pi pi-plus"
                size="small"
                class="!h-7"
                :disabled="!project"
                @click="openCreateDialog"
            />
        </div>

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
                <IconButton severity="secondary" icon="pepicons-pop:dots-y" @click.stop="openRowMenu($event, row)" />
            </template>
        </TaskListsTableView>

        <SortDialog
            :visible="sort.visible.value"
            :fields="taskListSortFieldDefs"
            :sort-by="sort.draftSortBy.value"
            :sort-order="sort.draftSortOrder.value"
            @update:visible="sort.visible.value = $event"
            @update:sort-by="sort.setDraftField"
            @update:sort-order="sort.setDraftOrder"
            @apply="onSortApply"
        />

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" @apply="page = 1" />

        <Menu ref="rowMenu" :model="rowMenuItems" popup />

        <TaskListCreateDialog
            :visible="createDialog.visible.value"
            :form-data="createDialog.formData.value"
            :validation-errors="createDialog.validationErrors.value"
            :is-pending="createDialog.isPending.value"
            project-locked
            @update:visible="createDialog.visible.value = $event"
            @update:form-data="createDialog.formData.value = $event"
            @submit="createDialog.submit"
        />

        <TaskCreateDialog
            v-model:visible="taskCreateDialog.visible.value"
            v-model:form-data="taskCreateDialog.formData.value"
            :validation-errors="taskCreateDialog.validationErrors.value"
            :is-pending="taskCreateDialog.isPending.value"
            @submit="taskCreateDialog.submit"
        />
    </div>
</template>
