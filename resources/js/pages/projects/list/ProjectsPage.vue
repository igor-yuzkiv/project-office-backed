<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useProjectsSearchQuery } from '@/entities/project/queries'
import { useDeleteProjectMutation } from '@/entities/project/mutations'
import { useHeaderActions } from '@/app/shell'
import { PAGE_SIZE } from '@/app/config'
import type { IProject } from '@/entities/project/types'
import { projectStatusOptions } from '@/entities/project/config'
import { ProjectUpsertDialog } from '@/widgets/projects/upsert-dialog'
import { useProjectUpsertDialog } from '@/widgets/projects/upsert-dialog/composables/use.project-upsert-dialog'
import { ProjectsTableView } from '@/widgets/projects/views/table'
import { FilterSidebar, FilterButton, createFilterDefMap, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog, type SortFieldDef } from '@/shared/sort'
import { SearchInput } from '@/shared/components/input'
import { IconButton } from '@/shared/components/button'

const router = useRouter()
const upsertDialog = useProjectUpsertDialog()

const filterSidebar = useFilterSidebar(
    createFilterDefMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('prefix', 'text', (d) => d.label('Prefix'))
            .addField('status', 'select', (d) =>
                d.label('Status').matchMode('in').setInputProps({
                    options: projectStatusOptions(),
                    optionLabel: 'label',
                    optionValue: 'value',
                    placeholder: 'Select status',
                })
            )
            .addField('tags', 'tags', (d) => d.label('Tags'))
    )
)

const sortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'prefix', label: 'Prefix' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

const sort = useSortDialog(sortFieldDefs, 'updated_at', 'desc')

const { mutateWithConfirm: deleteProject } = useDeleteProjectMutation()

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)
const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedProject = ref<IProject>()

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

const searchParams = computed(() => ({
    query: searchQuery.value,
    filters: filterSidebar.resolvedFilters.value,
    page: page.value,
    per_page: PAGE_SIZE,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
}))

const { projects, paginationMeta, isPending } = useProjectsSearchQuery(searchParams)

function onSortApply() {
    sort.apply()
    sort.close()
}

function onSearchSubmit() {
    searchQuery.value = searchInput.value
    page.value = 1
}

function onRowClick(project: IProject) {
    router.push({ name: 'project-details.tasks', params: { id: project.id } })
}

function openRowMenu(event: MouseEvent, project: IProject) {
    selectedProject.value = project
    rowMenu.value?.toggle(event)
}

function onPageChange(newPage: number) {
    page.value = newPage
}

watch([sort.sortBy, sort.sortOrder], () => {
    page.value = 1
})

useHeaderActions([{ key: 'new-project', title: 'New Project', is_primary: true, action: () => upsertDialog.open() }])
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
            <div class="gap-2 app-card p-1 flex items-center justify-between">
                <SearchInput v-model="searchInput" placeholder="Search projects..." @submit="onSearchSubmit" />
                <div class="gap-2 flex items-center">
                    <FilterButton v-bind="filterSidebar.buttonProps.value" />
                    <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
                </div>
            </div>

            <div class="app-card flex h-full w-full flex-col overflow-hidden">
                <ProjectsTableView
                    :projects="projects"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    @row-click="onRowClick"
                    @page-change="onPageChange"
                >
                    <template #actions="{ row }">
                        <IconButton
                            icon="material-symbols-light:more-vert"
                            @click.stop="openRowMenu($event, row)"
                        />
                    </template>
                </ProjectsTableView>
            </div>
        </div>

        <Menu ref="rowMenu" :model="rowMenuItems" popup />

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

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" @apply="page = 1" />

        <ProjectUpsertDialog
            v-model:visible="upsertDialog.visible.value"
            v-model:form-data="upsertDialog.formData.value"
            :mode="upsertDialog.mode.value"
            :validation-errors="upsertDialog.validationErrors.value"
            :is-pending="upsertDialog.isPending.value"
            @submit="upsertDialog.submit"
        />
    </div>
</template>
