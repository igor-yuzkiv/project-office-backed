<script setup lang="ts">
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useRouteParams } from '@vueuse/router'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { useTaskSearch } from '@/entities/task/composables'
import { taskSortFieldDefs, taskTableColumnsExcluding } from '@/entities/task/config'
import { FilterSidebar, FilterButton } from '@/shared/filters'
import { SortButton, SortDialog } from '@/shared/sort'
import { TaskViewSelect } from '@/widgets/tasks/view-switcher'
import { SearchInput } from '@/shared/components/input'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { AddTasksToTaskListDialog, useAddTasksToTaskListDialog } from '@/widgets/task-list/add-tasks-dialog'

const taskListId = useRouteParams<string>('id')

const { taskList } = useTaskListQuery(taskListId)

const search = useTaskSearch({
    // A getter, not a value: navigating between two lists reuses this component, and a frozen
    // id would leave the table showing the list the user came from.
    scopeFilter: () => ({
        filter_key: 'lookup',
        field_name: 'task_list_id',
        value: taskListId.value,
        matchMode: null,
        params: {},
    }),
    hiddenFilterFields: ['task_list_id'],
    include: ['taskList'],
    // Keyed by tab rather than by path, otherwise every task list gets its own stored state.
    persistKey: 'task-list-details.tasks',
})

// The page is one task list, so its own column would repeat the header.
const tableColumnsDef = taskTableColumnsExcluding('task_list.name', 'updated_by')

const taskCreateDialog = useTaskCreateDialog()

const addTasksDialog = useAddTasksToTaskListDialog(
    () => taskListId.value,
    () => taskList.value?.project_id
)
</script>

<template>
    <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-1 flex items-center justify-between">
            <SearchInput
                v-model="search.searchInput.value"
                placeholder="Search tasks..."
                @submit="search.submitSearch"
            />
            <div class="gap-2 flex items-center">
                <TaskViewSelect
                    :model-value="search.viewSwitcher.activeViewKey.value"
                    :options="search.taskViews.value"
                    @update:model-value="search.selectView"
                />
                <FilterButton v-bind="search.filterSidebar.buttonProps.value" />
                <SortButton :label="`Sort: ${search.sort.activeSortLabel.value}`" @click="search.sort.open()" />
                <Button severity="info" text label="Add Tasks" :disabled="!taskList" @click="addTasksDialog.open()">
                    <template #icon>
                        <Icon icon="material-symbols:playlist-add" class="text-lg" />
                    </template>
                </Button>
                <Button
                    severity="info"
                    text
                    label="New Task"
                    :disabled="!taskList?.project"
                    @click="taskList?.project && taskCreateDialog.open(taskList.project, taskList)"
                >
                    <template #icon>
                        <Icon icon="material-symbols:add" class="text-lg" />
                    </template>
                </Button>
            </div>
        </div>

        <div class="flex h-full w-full flex-col overflow-hidden">
            <TasksTableView
                :tasks="search.tasks.value"
                :is-pending="search.isPending.value"
                :pagination-meta="search.paginationMeta.value"
                :page="search.page.value"
                :to="search.taskDetailsRoute"
                :columns="tableColumnsDef"
                @page-change="search.goToPage"
            />
        </div>

        <SortDialog
            :visible="search.sort.visible.value"
            :fields="taskSortFieldDefs"
            :sort-by="search.sort.draftSortBy.value"
            :sort-order="search.sort.draftSortOrder.value"
            @update:visible="search.sort.visible.value = $event"
            @update:sort-by="search.sort.setDraftField"
            @update:sort-order="search.sort.setDraftOrder"
            @apply="search.applySort"
        />

        <FilterSidebar v-bind="search.filterSidebar.sidebarProps.value" @apply="search.goToPage(1)" />

        <TaskCreateDialog
            v-model:visible="taskCreateDialog.visible.value"
            v-model:form-data="taskCreateDialog.formData.value"
            :validation-errors="taskCreateDialog.validationErrors.value"
            :is-pending="taskCreateDialog.isPending.value"
            @submit="taskCreateDialog.submit()"
        />

        <AddTasksToTaskListDialog
            v-model:visible="addTasksDialog.visible.value"
            v-model:selected="addTasksDialog.selected.value"
            v-model:search-query="addTasksDialog.searchQuery.value"
            :candidates="addTasksDialog.candidates.value"
            :pagination-meta="addTasksDialog.paginationMeta.value"
            :page="addTasksDialog.page.value"
            :is-pending="addTasksDialog.isPending.value"
            :is-saving="addTasksDialog.isSaving.value"
            :can-save="addTasksDialog.canSave.value"
            @page-change="addTasksDialog.onPageChange"
            @submit="addTasksDialog.submit()"
        />
    </div>
</template>
