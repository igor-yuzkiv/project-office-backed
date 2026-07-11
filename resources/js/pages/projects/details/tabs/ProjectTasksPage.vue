<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { PAGE_SIZE } from '@/app/config'
import { useProjectQuery } from '@/entities/project/queries'
import { useTasksSearchQuery } from '@/entities/task/queries'
import { useDeleteTaskMutation } from '@/entities/task/mutations'
import type { TaskOverviewDto, TaskSearchParams } from '@/entities/task/types'
import { createDefaultTaskFiltersDefMap, taskSortFieldDefs, taskTableColumnsExcluding } from '@/entities/task/config'
import { useTaskViewsQuery } from '@/entities/task-view'
import type { FilterPayloadItem } from '@/shared/filters'
import { FilterSidebar, FilterButton, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog } from '@/shared/sort'
import { TaskViewSelect, useTaskViewSwitcher } from '@/shared/task-views'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { Icon } from '@iconify/vue'

const route = useRoute()
const router = useRouter()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)

// The project scope is mandatory: it is always applied and never cleared by a
// view switch or a manual filter reset.
const projectFilter: FilterPayloadItem = {
    filter_key: 'lookup',
    field_name: 'project_id',
    value: projectId,
    matchMode: null,
    params: {},
}

// The project scope is fixed on this page, so the Project filter field is hidden from the sidebar.
const taskFiltersDefMap = createDefaultTaskFiltersDefMap()
delete taskFiltersDefMap.project_id
const filterSidebar = useFilterSidebar(taskFiltersDefMap)

const { views: taskViews, isPending: isTaskViewsPending } = useTaskViewsQuery()
const taskViewSwitcher = useTaskViewSwitcher(taskViews)

const sort = useSortDialog(taskSortFieldDefs, 'priority', 'desc')

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

const tableColumnsDef = taskTableColumnsExcluding('project', 'updated_by')

const searchParams = computed<TaskSearchParams>(() => ({
    query: searchQuery.value,
    include: ['taskList'],
    filters: [projectFilter, ...taskViewSwitcher.activeViewFilters.value, ...filterSidebar.resolvedFilters.value],
    page: page.value,
    per_page: PAGE_SIZE,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
}))

// Gate the search until the task views are settled, otherwise it fires once with no view
// filters and again once the default view (All Open) loads.
const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams, {
    enabled: computed(() => !isTaskViewsPending.value),
})

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

function taskDetailsRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
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

function onSortApply() {
    sort.apply()
    sort.close()
}

function onPageChange(newPage: number) {
    page.value = newPage
}

watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})
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
                    <Button
                        severity="info"
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
            </div>
            <div class="flex h-full w-full flex-col overflow-hidden">
                <TasksTableView
                    :tasks="tasks"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    :to="taskDetailsRoute"
                    @page-change="onPageChange"
                    :columns="tableColumnsDef"
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
