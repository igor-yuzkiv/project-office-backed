<script setup lang="ts">
import { computed, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useProjectsSearchQuery } from '@/entities/project/queries'
import { useDeleteProjectMutation } from '@/entities/project/mutations'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { PAGE_SIZE } from '@/app/config'
import type { IProject } from '@/entities/project/types'
import { ProjectUpsertDialog } from '@/widgets/projects/upsert-dialog'
import { useProjectUpsertDialog } from '@/widgets/projects/upsert-dialog/composables/use.project-upsert-dialog'
import { FilterSidebar, FiltersButton, createFiltersDefinitionsMap, useFilterSidebar } from '@/shared/filters'
import { SearchInput } from '@/shared/components/input'
import { DisplayDate } from '@/shared/components/display'

const upsertDialog = useProjectUpsertDialog()

const layoutStore = useAppLayoutStore()
layoutStore.setHeaderActions([
    { key: 'new-project', title: 'New Project', is_primary: true, action: () => upsertDialog.open() },
])

const {
    visible: sidebarVisible,
    draftDefMap: sidebarDefMap,
    resolvedFilters: appliedFilters,
    updateFilter,
    apply: applyFilters,
    reset: resetFilters,
} = useFilterSidebar(
    createFiltersDefinitionsMap((map) =>
        map.addField('name', 'text', (d) => d.label('Name')).addField('prefix', 'text', (d) => d.label('Prefix'))
    )
)

const { mutateWithConfirm: deleteProject } = useDeleteProjectMutation()

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)
const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedProject = ref<IProject>()

const activeFiltersCount = computed(() => appliedFilters.value.length)
const searchParams = computed(() => ({
    query: searchQuery.value,
    filters: appliedFilters.value,
    page: page.value,
    per_page: PAGE_SIZE,
}))

const { projects, paginationMeta, isPending } = useProjectsSearchQuery(searchParams)

const rowMenuItems: MenuItem[] = [
    { label: 'Edit', icon: 'pi pi-pencil', command: () => upsertDialog.open(selectedProject.value) },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        command: () =>
            deleteProject(
                selectedProject.value!.id,
                `Are you sure you want to delete "${selectedProject.value!.name}"?`
            ),
    },
]

function onSearchSubmit() {
    searchQuery.value = searchInput.value
    page.value = 1
}

function onApply() {
    applyFilters()
    page.value = 1
}

function openRowMenu(event: MouseEvent, project: IProject) {
    selectedProject.value = project
    rowMenu.value?.toggle(event)
}

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}
</script>

<template>
    <div class="gap-4 p-6 flex flex-col">
        <div class="gap-2 flex items-center">
            <SearchInput v-model="searchInput" placeholder="Search projects..." @submit="onSearchSubmit" />
            <FiltersButton :count="activeFiltersCount" @click="sidebarVisible = true" />
        </div>

        <DataTable :value="projects" :loading="isPending" lazy striped-rows class="w-full" row-hover>
            <Column field="prefix" header="Prefix" style="width: 6rem" />
            <Column field="name" header="Project Name" />
            <Column field="created_at" header="Created" style="width: 12rem">
                <template #body="{ data }">
                    <DisplayDate :date="data.created_at" />
                </template>
            </Column>
            <Column style="width: 3rem">
                <template #body="{ data }">
                    <button
                        class="pi pi-ellipsis-v p-1 text-surface-400 hover:text-surface-700 dark:hover:text-surface-200 cursor-pointer"
                        @click="openRowMenu($event, data)"
                    />
                </template>
            </Column>
        </DataTable>

        <Paginator
            v-if="paginationMeta && paginationMeta.last_page > 1"
            :rows="PAGE_SIZE"
            :total-records="paginationMeta.total"
            :first="(page - 1) * PAGE_SIZE"
            @page="onPageChange"
        />
    </div>

    <Menu ref="rowMenu" :model="rowMenuItems" popup />

    <FilterSidebar
        v-model:visible="sidebarVisible"
        :def-map="sidebarDefMap"
        title="Filters"
        @apply="onApply"
        @reset="resetFilters"
        @change="updateFilter"
    />

    <ProjectUpsertDialog
        v-model:visible="upsertDialog.visible.value"
        v-model:name="upsertDialog.name.value"
        :mode="upsertDialog.mode.value"
        :validation-errors="upsertDialog.validationErrors.value"
        :is-pending="upsertDialog.isPending.value"
        @submit="upsertDialog.submit"
    />
</template>
