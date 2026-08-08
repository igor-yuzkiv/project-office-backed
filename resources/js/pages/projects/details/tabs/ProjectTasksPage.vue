<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useProjectQuery } from '@/entities/project/queries'
import { useTaskSearch } from '@/entities/task/composables'
import { useDeleteTaskMutation } from '@/entities/task/mutations'
import type { TaskOverviewDto } from '@/entities/task/types'
import { taskSortFieldDefs, taskTableColumnsExcluding } from '@/entities/task/config'
import { FilterSidebar, FilterButton } from '@/shared/filters'
import { SortButton, SortDialog } from '@/shared/sort'
import { TaskViewSelect } from '@/widgets/tasks/view-switcher'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { Icon } from '@iconify/vue'

const route = useRoute()
const router = useRouter()
const projectId = route.params.id as string

const { project } = useProjectQuery(projectId)

const search = useTaskSearch({
    scopeFilter: {
        filter_key: 'lookup',
        field_name: 'project_id',
        value: projectId,
        matchMode: null,
        params: {},
    },
    hiddenFilterFields: ['project_id'],
    include: ['taskList'],
})

const tableColumnsDef = taskTableColumnsExcluding('project', 'updated_by')

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
                    :tasks="search.tasks.value"
                    :is-pending="search.isPending.value"
                    :pagination-meta="search.paginationMeta.value"
                    :page="search.page.value"
                    :to="search.taskDetailsRoute"
                    :columns="tableColumnsDef"
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
