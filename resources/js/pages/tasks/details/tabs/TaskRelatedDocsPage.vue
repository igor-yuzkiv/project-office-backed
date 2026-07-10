<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouteParams } from '@vueuse/router'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useTaskProjectDocumentsQuery, useTaskQuery } from '@/entities/task/queries'
import { ProjectDocumentationFlatTableView } from '@/widgets/project-documents/views/flat-table'
import { AssociateProjectDocumentsDialog } from '@/widgets/tasks/associate-project-documents-dialog'
import { PAGE_SIZE } from '@/app/config'

const taskId = useRouteParams<string>('id')

const { task } = useTaskQuery(taskId)

const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { projectDocuments, paginationMeta, isPending } = useTaskProjectDocumentsQuery(taskId, pagination)

function onPageChange(newPage: number) {
    page.value = newPage
}

const isAssociateDialogVisible = ref(false)
</script>

<template>
    <div class="p-4">
        <div class="gap-2 pb-2 flex items-center justify-end">
            <Button
                label="Associate Document"
                severity="info"
                size="small"
                text
                @click="isAssociateDialogVisible = true"
            >
                <template #icon>
                    <Icon icon="material-symbols:add" class="text-lg" />
                </template>
            </Button>
        </div>

        <ProjectDocumentationFlatTableView
            :documents="projectDocuments"
            :is-pending="isPending"
            :pagination-meta="paginationMeta"
            :page="page"
            empty-message="No related documents yet."
            @page-change="onPageChange"
        />

        <AssociateProjectDocumentsDialog
            v-if="task"
            v-model:visible="isAssociateDialogVisible"
            :task-id="task.id"
            :project-id="task.project_id"
        />
    </div>
</template>
