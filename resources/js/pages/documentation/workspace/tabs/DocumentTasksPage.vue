<script setup lang="ts">
import { computed, ref } from 'vue'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useProjectDocumentTasksQuery } from '@/entities/project-document'
import { taskTableColumnsExcluding } from '@/entities/task'
import type { TaskOverviewDto } from '@/entities/task/types'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { AssociateTasksDialog } from '@/widgets/project-documents/associate-tasks-dialog'
import { PAGE_SIZE } from '@/app/config'
import type { IProjectDocument } from '@/entities/project-document/types'

const props = defineProps<{
    document: IProjectDocument
}>()

const documentId = computed(() => props.document.id)
const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { tasks, paginationMeta, isPending } = useProjectDocumentTasksQuery(documentId, pagination)

const tableColumnsDef = taskTableColumnsExcluding('project', 'task_list.name')

const isAssociateDialogVisible = ref(false)

function onPageChange(newPage: number) {
    page.value = newPage
}

function taskDetailsRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
}
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="gap-2 p-1 flex items-center justify-end">
            <Button label="Add Tasks" severity="info" text size="small" @click="isAssociateDialogVisible = true">
                <template #icon>
                    <Icon icon="material-symbols:add" class="text-lg" />
                </template>
            </Button>
        </div>

        <TasksTableView
            :tasks="tasks"
            :is-pending="isPending"
            :pagination-meta="paginationMeta"
            :page="page"
            :to="taskDetailsRoute"
            :columns="tableColumnsDef"
            @page-change="onPageChange"
        />

        <AssociateTasksDialog
            v-model:visible="isAssociateDialogVisible"
            :document-id="documentId"
            :project-id="document.project_id"
        />
    </div>
</template>
