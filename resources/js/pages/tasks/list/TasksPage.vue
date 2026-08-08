<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useTaskSearch } from '@/entities/task/composables'
import { useBulkUpdateTaskStatusMutation, useDeleteTaskMutation } from '@/entities/task/mutations'
import type { TaskOverviewDto, TaskStatusValue } from '@/entities/task/types'
import { FilterSidebar, FilterButton } from '@/shared/filters'
import { SortButton, SortDialog } from '@/shared/sort'
import { TaskViewSelect } from '@/widgets/tasks/view-switcher'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { useHeaderActions } from '@/app/shell'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { TaskBulkActionsBar } from '@/widgets/tasks/bulk-actions'
import { useToast } from '@/shared/composables'
import { taskSortFieldDefs, taskTableColumnDefs } from '@/entities/task/config'

const router = useRouter()

const toast = useToast()
const taskCreateDialog = useTaskCreateDialog()
const { mutateWithConfirm: deleteTask } = useDeleteTaskMutation()
const { mutate: bulkUpdateStatus, isPending: isBulkUpdatePending } = useBulkUpdateTaskStatusMutation()

const search = useTaskSearch({ include: ['project', 'taskList'] })

const selectedTasks = ref<TaskOverviewDto[]>([])

// Emptying the selection unmounts the bar, which discards its dialog state with it.
function clearSelection() {
    selectedTasks.value = []
}

function applyBulkStatus(status: TaskStatusValue) {
    bulkUpdateStatus(
        { task_ids: selectedTasks.value.map((task) => task.id), status },
        {
            onSuccess: ({ data }) => {
                toast.success(`Updated ${data.updated_count} task(s).`)
                clearSelection()
            },
            onError: () => toast.error('Failed to update the selected tasks.'),
        }
    )
}

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

// The selection only ever covers the rows currently on screen, so anything that changes them drops it.
watch(search.searchParams, clearSelection)

useHeaderActions([{ key: 'add-task', title: 'New Task', action: () => taskCreateDialog.open(), is_primary: true }])
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
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
                </div>
            </div>

            <TaskBulkActionsBar
                v-if="selectedTasks.length"
                :selected-count="selectedTasks.length"
                :is-pending="isBulkUpdatePending"
                @apply="applyBulkStatus"
                @clear="clearSelection"
            />

            <div class="flex h-full w-full flex-col overflow-hidden">
                <TasksTableView
                    v-model:selection="selectedTasks"
                    selection-mode="multiple"
                    :tasks="search.tasks.value"
                    :is-pending="search.isPending.value"
                    :pagination-meta="search.paginationMeta.value"
                    :page="search.page.value"
                    :to="search.taskDetailsRoute"
                    :columns="taskTableColumnDefs"
                    @page-change="search.goToPage"
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
