<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import { useTasksSearchQuery } from '@/entities/task/queries'
import type { ITask } from '@/entities/task/types'
import { PAGE_SIZE } from '@/app/config'
import { FilterSidebar, FiltersButton, createFiltersDefinitionsMap, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog, type SortFieldDef } from '@/shared/sort'
import { SearchInput } from '@/shared/components/input'
import { CopyToClipboard, DisplayDate } from '@/shared/components/display'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { TaskCreateDialog, useTaskCreateDialog } from '@/widgets/tasks/create-dialog'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'

const router = useRouter()
const layoutStore = useAppLayoutStore()

const taskCreateDialog = useTaskCreateDialog()

const {
    visible: sidebarVisible,
    draftDefMap: sidebarDefMap,
    resolvedFilters: appliedFilters,
    updateFilter,
    apply: applyFilters,
    reset: resetFilters,
} = useFilterSidebar(
    createFiltersDefinitionsMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('status', 'text', (d) => d.label('Status'))
            .addField('priority', 'integer', (d) => d.label('Priority'))
    )
)

const sortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'status', label: 'Status' },
    { field: 'priority', label: 'Priority' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

const sort = useSortDialog(sortFieldDefs, 'updated_at', 'desc')

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

const activeFiltersCount = computed(() => appliedFilters.value.length)
const searchParams = computed(() => ({
    query: searchQuery.value,
    filters: appliedFilters.value,
    page: page.value,
    per_page: PAGE_SIZE,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
    include: ['project' as const],
}))

const { tasks, paginationMeta, isPending } = useTasksSearchQuery(searchParams)

function onRowClick(event: { data: ITask }) {
    router.push({ name: 'task-details', params: { id: event.data.id } })
}

function onSortApply() {
    sort.apply()
    sort.close()
}

function onSearchSubmit() {
    searchQuery.value = searchInput.value
    page.value = 1
}

function onApply() {
    applyFilters()
    page.value = 1
}

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}

watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})

onMounted(() => {
    layoutStore.setHeaderActions([
        { key: 'add-task', title: 'Add Task', action: () => taskCreateDialog.open(), is_primary: true },
        { key: 'add-issue', title: 'Add Issue', action: () => console.log('test - Add Issue') },
    ])
})

onUnmounted(() => {
    layoutStore.clearHeaderActions()
})
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 app-card p-1 flex items-center justify-between">
                <SearchInput v-model="searchInput" placeholder="Search tasks..." @submit="onSearchSubmit" />
                <div class="gap-2 flex items-center">
                    <FiltersButton :count="activeFiltersCount" @click="sidebarVisible = true" />
                    <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
                </div>
            </div>

            <div class="app-card flex h-full w-full flex-col overflow-hidden">
                <DataTable
                    :value="tasks"
                    :loading="isPending"
                    lazy
                    striped-rows
                    class="p-0 w-full cursor-pointer"
                    row-hover
                    scrollable
                    scroll-height="flex"
                    size="small"
                    @row-click="onRowClick"
                    pt:footer:class="p-0 border-none"
                >
                    <Column field="key" header="Key" style="width: 10rem">
                        <template #body="{ data }">
                            <CopyToClipboard :text="data.key" class="text-surface-500" />
                        </template>
                    </Column>
                    <Column field="name" header="Task Name" />
                    <Column field="project.name" header="Project" style="width: 12rem; min-width: 0">
                        <template #body="{ data }">
                            <RouterLink
                                v-if="data.project"
                                :to="{ name: 'project-details', params: { id: data.project_id } }"
                                class="text-primary-500 block truncate hover:underline"
                                :title="`${data.project.prefix} - ${data.project.name}`"
                            >
                                {{ data.project.prefix }} - {{ data.project.name }}
                            </RouterLink>
                        </template>
                    </Column>
                    <Column field="status" header="Status" style="width: 9rem">
                        <template #body="{ data }">
                            <TaskStatusTag :status="data.status" class="w-full" />
                        </template>
                    </Column>
                    <Column field="priority.name" header="Priority" style="width: 7rem">
                        <template #body="{ data }">
                            <TaskPriorityTag :priority="data.priority" class="w-full" />
                        </template>
                    </Column>
                    <Column field="created_at" header="Created" style="width: 12rem">
                        <template #body="{ data }">
                            <DisplayDate :date="data.created_at" />
                        </template>
                    </Column>

                    <template #footer>
                        <Paginator
                            v-if="paginationMeta && paginationMeta.last_page > 1"
                            :rows="PAGE_SIZE"
                            :total-records="paginationMeta.total"
                            :first="(page - 1) * PAGE_SIZE"
                            @page="onPageChange"
                            pt:root:class="p-0"
                        />
                    </template>
                </DataTable>
            </div>
        </div>

        <SortDialog
            :visible="sort.visible.value"
            :fields="sortFieldDefs"
            :sort-by="sort.draftSortBy.value"
            :sort-order="sort.draftSortOrder.value"
            @update:visible="sort.visible.value = $event"
            @update:sort-by="sort.setDraftField"
            @update:sort-order="sort.setDraftOrder"
            @apply="onSortApply"
        />

        <FilterSidebar
            v-model:visible="sidebarVisible"
            :def-map="sidebarDefMap"
            title="Filters"
            @apply="onApply"
            @reset="resetFilters"
            @change="updateFilter"
        />

        <TaskCreateDialog
            :visible="taskCreateDialog.visible.value"
            :form-data="taskCreateDialog.formData.value"
            :validation-errors="taskCreateDialog.validationErrors.value"
            :is-pending="taskCreateDialog.isPending.value"
            @update:visible="taskCreateDialog.visible.value = $event"
            @update:form-data="taskCreateDialog.formData.value = $event"
            @submit="taskCreateDialog.submit()"
        />
    </div>
</template>
