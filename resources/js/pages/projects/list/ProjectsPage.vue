<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useLocalStorage } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import Skeleton from 'primevue/skeleton'
import { usePinnedProjectsQuery, useProjectsSearchQuery } from '@/entities/project/queries'
import { useDeleteProjectMutation } from '@/entities/project/mutations'
import { useBreadcrumbs } from '@/app/shell'
import { PAGE_SIZE } from '@/app/config'
import type { ProjectOverviewDto, ProjectSearchParams } from '@/entities/project/types'
import { projectStatusOptions } from '@/entities/project/config'
import { ProjectCreateDialog, useProjectCreateDialog } from '@/widgets/projects/create-dialog'
import { ProjectCard } from '@/widgets/projects/project-card'
import { ProjectsTableView } from '@/widgets/projects/views/table'
import { FilterSidebar, FilterButton, createFilterDefMap, useFilterSidebar } from '@/shared/filters'
import { useSortDialog, SortButton, SortDialog, type SortFieldDef } from '@/shared/sort'
import { usePersistedListState } from '@/shared/composables'
import { SearchInput } from '@/shared/components/input'

type ProjectsView = 'cards' | 'list'

const VIEW_OPTIONS: { value: ProjectsView; icon: string; label: string }[] = [
    { value: 'cards', icon: 'tabler:layout-grid', label: 'Cards' },
    { value: 'list', icon: 'tabler:list', label: 'List' },
]

useBreadcrumbs([{ label: 'Projects' }])

const router = useRouter()
const createDialog = useProjectCreateDialog()
const view = useLocalStorage<ProjectsView>('app:projects:view', 'list')

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

usePersistedListState(
    {
        filters: filterSidebar.filtersSnapshot,
        sortBy: sort.sortBy,
        sortOrder: sort.sortOrder,
    },
    {
        validate: (data) =>
            sortFieldDefs.some((f) => f.field === data.sortBy) &&
            (data.sortOrder === 'asc' || data.sortOrder === 'desc'),
    }
)

const { mutateWithConfirm: deleteProject } = useDeleteProjectMutation()

const searchInput = ref('')
const searchQuery = ref('')
const page = ref(1)

const searchParams = computed<ProjectSearchParams>(() => ({
    query: searchQuery.value,
    filters: filterSidebar.resolvedFilters.value,
    page: page.value,
    per_page: PAGE_SIZE,
    sort_by: sort.sortBy.value,
    sort_order: sort.sortOrder.value,
}))

const { projects, paginationMeta, isPending } = useProjectsSearchQuery(searchParams)
const { projects: pinnedProjects } = usePinnedProjectsQuery()

const hasPages = computed(() => !!paginationMeta.value && paginationMeta.value.last_page > 1)

function onEdit(project: ProjectOverviewDto) {
    router.push({ name: 'project-edit', params: { id: project.id } })
}

function onDelete(project: ProjectOverviewDto) {
    deleteProject(project.id, `Are you sure you want to delete "${project.name}"?`)
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
</script>

<template>
    <div class="min-h-0 flex-1 overflow-auto">
        <div class="page-container">
            <div class="gap-2 mb-6 flex flex-wrap items-center">
                <SearchInput v-model="searchInput" placeholder="Search projects" @submit="onSearchSubmit" />
                <span class="flex-1" />
                <FilterButton v-bind="filterSidebar.buttonProps.value" />
                <SortButton :label="`Sort: ${sort.activeSortLabel.value}`" @click="sort.open()" />
                <span class="border-line-2 h-7 rounded-md inline-flex overflow-hidden border" role="group">
                    <button
                        v-for="option in VIEW_OPTIONS"
                        :key="option.value"
                        type="button"
                        class="grid w-[30px] cursor-pointer place-items-center transition-colors"
                        :class="view === option.value ? 'bg-hover text-ink' : 'text-ink-3 hover:text-ink-2'"
                        :aria-label="option.label"
                        :aria-pressed="view === option.value"
                        :title="option.label"
                        @click="view = option.value"
                    >
                        <Icon :icon="option.icon" class="text-[15px]" />
                    </button>
                </span>
                <Button label="New project" icon="pi pi-plus" size="small" class="!h-7" @click="createDialog.open()" />
            </div>

            <section v-if="pinnedProjects.length" class="mb-8">
                <h2 class="type-section mb-2.5">Pinned</h2>
                <div class="gap-3 lg:grid-cols-2 grid grid-cols-1">
                    <ProjectCard
                        v-for="project in pinnedProjects"
                        :key="project.id"
                        :project="project"
                        @edit="onEdit"
                        @delete="onDelete"
                    />
                </div>
            </section>

            <section>
                <h2 class="type-section gap-2 mb-2.5 flex items-baseline">
                    All projects
                    <span v-if="paginationMeta" class="type-meta-3">{{ paginationMeta.total }}</span>
                </h2>

                <ProjectsTableView
                    v-if="view === 'list'"
                    :projects="projects"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    @edit="onEdit"
                    @delete="onDelete"
                    @page-change="onPageChange"
                />

                <template v-else>
                    <div v-if="isPending" class="gap-3 lg:grid-cols-2 grid grid-cols-1">
                        <Skeleton v-for="n in 4" :key="n" height="10rem" />
                    </div>
                    <div v-else-if="!projects.length" class="type-meta-3 py-6 text-center">No projects found.</div>
                    <div v-else class="gap-3 lg:grid-cols-2 grid grid-cols-1">
                        <ProjectCard
                            v-for="project in projects"
                            :key="project.id"
                            :project="project"
                            @edit="onEdit"
                            @delete="onDelete"
                        />
                    </div>
                    <Paginator
                        v-if="hasPages"
                        :rows="PAGE_SIZE"
                        :total-records="paginationMeta?.total ?? 0"
                        :first="(page - 1) * PAGE_SIZE"
                        class="mt-2"
                        @page="onPageChange($event.page + 1)"
                    />
                </template>
            </section>
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

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" @apply="page = 1" />

        <ProjectCreateDialog
            v-model:visible="createDialog.visible.value"
            v-model:form-data="createDialog.formData.value"
            :validation-errors="createDialog.validationErrors.value"
            :is-pending="createDialog.isPending.value"
            @submit="createDialog.submit"
        />
    </div>
</template>
