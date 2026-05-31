<script setup lang="ts">
import { ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { useProjectsQuery } from '@/entities/project/queries'
import { useDeleteProjectMutation } from '@/entities/project/mutations'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { PAGE_SIZE } from '@/app/config'
import type { IProject } from '@/entities/project/types'
import { ProjectUpsertDialog } from '@/widgets/projects/upsert-dialog'
import { useProjectUpsertDialog } from '@/widgets/projects/upsert-dialog/composables/use.project-upsert-dialog'

const upsertDialog = useProjectUpsertDialog()

const layoutStore = useAppLayoutStore()
layoutStore.setHeaderActions([
    { key: 'new-project', title: 'New Project', is_primary: true, action: () => upsertDialog.open() },
])

const page = ref(1)
const pagination = ref({ page: page.value, per_page: PAGE_SIZE })

const { projects, paginationMeta, isPending } = useProjectsQuery(pagination)
const { mutateWithConfirm: deleteProject } = useDeleteProjectMutation()

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedProject = ref<IProject>()

const rowMenuItems: MenuItem[] = [
    { label: 'Edit', icon: 'pi pi-pencil', command: () => upsertDialog.open(selectedProject.value) },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        command: () => deleteProject(selectedProject.value!.id, `Are you sure you want to delete "${selectedProject.value!.name}"?`),
    },
]

function openRowMenu(event: MouseEvent, project: IProject) {
    selectedProject.value = project
    rowMenu.value?.toggle(event)
}

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
    pagination.value = { page: page.value, per_page: PAGE_SIZE }
}
</script>

<template>
    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Projects</h1>
            <p class="text-sm text-surface-500">Manage and track all your organisation's projects.</p>
        </div>

        <DataTable
            :value="projects"
            :loading="isPending"
            lazy
            striped-rows
            class="w-full"
            row-hover
        >
            <Column field="prefix" header="Prefix" style="width: 6rem" />
            <Column field="name" header="Project Name" />
            <Column field="created_at" header="Created" style="width: 12rem">
                <template #body="{ data }">
                    {{ new Date(data.created_at).toLocaleDateString() }}
                </template>
            </Column>
            <Column style="width: 3rem">
                <template #body="{ data }">
                    <button
                        class="pi pi-ellipsis-v cursor-pointer p-1 text-surface-400 hover:text-surface-700 dark:hover:text-surface-200"
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
    <ProjectUpsertDialog
        v-model:visible="upsertDialog.visible.value"
        v-model:name="upsertDialog.name.value"
        :mode="upsertDialog.mode.value"
        :validation-errors="upsertDialog.validationErrors.value"
        :is-pending="upsertDialog.isPending.value"
        @submit="upsertDialog.submit"
    />
</template>
