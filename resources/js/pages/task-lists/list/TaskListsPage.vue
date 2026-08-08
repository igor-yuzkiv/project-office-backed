<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useTaskListsSearchQuery } from '@/entities/task-list/queries'
import { useDeleteTaskListMutation } from '@/entities/task-list/mutations'
import type { ITaskList, TaskListSearchParams } from '@/entities/task-list/types'
import {
    createDefaultTaskListFiltersDefMap,
    taskListSortFieldDefs,
    taskListTableColumnDefs,
} from '@/entities/task-list/config'
import { PAGE_SIZE } from '@/app/config'
import { FilterSidebar, FilterButton, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog } from '@/shared/sort'
import { usePersistedListState } from '@/shared/composables'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { TaskListsTableView } from '@/widgets/task-list/views/table'
import { TaskListCreateDialog, useTaskListCreateDialog } from '@/widgets/task-list/create-dialog'
import { useHeaderActions } from '@/app/shell'

const router = useRouter()

const { mutateWithConfirm: deleteTaskList } = useDeleteTaskListMutation()
const createDialog = useTaskListCreateDialog()

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedTaskList = ref<ITaskList>()

const rowMenuItems: MenuItem[] = [
    {
        label: 'Edit',
        icon: 'pi pi-pencil',
        command: () => router.push({ name: 'task-list-edit', params: { id: selectedTaskList.value!.id } }),
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

const filterSidebar = useFilterSidebar(createDefaultTaskListFiltersDefMap())
const sort = useSortDialog(taskListSortFieldDefs, 'updated_at', 'desc')

usePersistedListState(
    {
        filters: filterSidebar.filtersSnapshot,
        sortBy: sort.sortBy,
        sortOrder: sort.sortOrder,
    },
    {
        validate: (data) =>
            taskListSortFieldDefs.some((f) => f.field === data.sortBy) &&
            (data.sortOrder === 'asc' || data.sortOrder === 'desc'),
    }
)

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

const searchParams = computed<TaskListSearchParams>(() => ({
    query: searchQuery.value,
    filters: filterSidebar.resolvedFilters.value,
    page: page.value,
    per_page: PAGE_SIZE,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
    include: ['project', 'tags'],
}))

const { taskLists, paginationMeta, isPending } = useTaskListsSearchQuery(searchParams)

function taskListDetailsRoute(taskList: ITaskList) {
    return { name: 'task-list-details', params: { id: taskList.id } }
}

function onSortApply() {
    sort.apply()
    sort.close()
}

function onSearchSubmit() {
    searchQuery.value = searchInput.value
    page.value = 1
}

function onPageChange(newPage: number) {
    page.value = newPage
}

watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})

useHeaderActions([
    { key: 'new-task-list', title: 'New Task List', action: () => createDialog.open(), is_primary: true },
])
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-between">
                <SearchInput v-model="searchInput" placeholder="Search task lists..." @submit="onSearchSubmit" />
                <div class="gap-2 flex items-center">
                    <FilterButton v-bind="filterSidebar.buttonProps.value" />
                    <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
                </div>
            </div>

            <div class="flex h-full w-full flex-col overflow-hidden">
                <TaskListsTableView
                    :task-lists="taskLists"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    :columns="taskListTableColumnDefs"
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
            @update:visible="createDialog.visible.value = $event"
            @update:form-data="createDialog.formData.value = $event"
            @submit="createDialog.submit()"
        />
    </div>
</template>
