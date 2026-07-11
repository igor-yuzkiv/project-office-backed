<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useTasksSearchQuery } from '@/entities/task/queries'
import { useDeleteTaskMutation } from '@/entities/task/mutations'
import type { TaskOverviewDto } from '@/entities/task/types'
import { useTaskViewsQuery } from '@/entities/task-view'
import { PAGE_SIZE } from '@/app/config'
import { FilterSidebar, FilterButton, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog } from '@/shared/sort'
import { TaskViewSelect, useTaskViewSwitcher } from '@/widgets/tasks/view-switcher'
import { usePersistedListState } from '@/shared/composables'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { useHeaderActions } from '@/app/shell'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { createDefaultTaskFiltersDefMap, taskSortFieldDefs, taskTableColumnDefs } from '@/entities/task/config'

const router = useRouter()

const taskCreateDialog = useTaskCreateDialog()
const { mutateWithConfirm: deleteTask } = useDeleteTaskMutation()

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedTask = ref<TaskOverviewDto>()

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

function openRowMenu(event: MouseEvent, task: TaskOverviewDto) {
    selectedTask.value = task
    rowMenu.value?.toggle(event)
}

const filterSidebar = useFilterSidebar(createDefaultTaskFiltersDefMap())

const { views: taskViews, isPending: isTaskViewsPending } = useTaskViewsQuery()
const taskViewSwitcher = useTaskViewSwitcher(taskViews)

const sort = useSortDialog(taskSortFieldDefs, 'updated_at', 'desc')

usePersistedListState(
    {
        filters: filterSidebar.filtersSnapshot,
        sortBy: sort.sortBy,
        sortOrder: sort.sortOrder,
    },
    {
        validate: (data) =>
            taskSortFieldDefs.some((f) => f.field === data.sortBy) &&
            (data.sortOrder === 'asc' || data.sortOrder === 'desc'),
    }
)

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

const searchParams = computed(() => ({
    query: searchQuery.value,
    filters: [...taskViewSwitcher.activeViewFilters.value, ...filterSidebar.resolvedFilters.value],
    page: page.value,
    per_page: PAGE_SIZE,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
    include: ['project' as const, 'taskList' as const],
}))

// Gate the search until the task views are settled, otherwise it fires once with no view
// filters and again once the default view (All Open) loads.
const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams, {
    enabled: computed(() => !isTaskViewsPending.value),
})

function taskDetailsRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
}

function onSortApply() {
    sort.apply()
    sort.close()
}

function onSearchSubmit() {
    searchQuery.value = searchInput.value
    page.value = 1
}

function onViewSelect(key: string) {
    taskViewSwitcher.select(key)
    filterSidebar.clear()
    page.value = 1
}

function onPageChange(newPage: number) {
    page.value = newPage
}

watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})

useHeaderActions([
    { key: 'add-task', title: 'Add Task', action: () => taskCreateDialog.open(), is_primary: true },
    { key: 'add-issue', title: 'Add Issue', action: () => console.log('test - Add Issue') },
])
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-between">
                <SearchInput v-model="searchInput" placeholder="Search tasks..." @submit="onSearchSubmit" />
                <div class="gap-2 flex items-center">
                    <TaskViewSelect
                        :model-value="taskViewSwitcher.activeViewKey.value"
                        :options="taskViews"
                        @update:model-value="onViewSelect"
                    />
                    <FilterButton v-bind="filterSidebar.buttonProps.value" />
                    <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
                </div>
            </div>

            <div class="flex h-full w-full flex-col overflow-hidden">
                <TasksTableView
                    :tasks="tasks"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    :to="taskDetailsRoute"
                    :columns="taskTableColumnDefs"
                    @page-change="onPageChange"
                >
                    <template #actions="{ row }">
                        <IconButton
                            severity="secondary"
                            icon="pepicons-pop:dots-y"
                            @click.stop="openRowMenu($event, row)"
                        />
                    </template>
                </TasksTableView>
            </div>
        </div>

        <SortDialog
            :visible="sort.visible.value"
            :fields="taskSortFieldDefs"
            :sort-by="sort.draftSortBy.value"
            :sort-order="sort.draftSortOrder.value"
            @update:visible="sort.visible.value = $event"
            @update:sort-by="sort.setDraftField"
            @update:sort-order="sort.setDraftOrder"
            @apply="onSortApply"
        />

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" @apply="page = 1" />

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
