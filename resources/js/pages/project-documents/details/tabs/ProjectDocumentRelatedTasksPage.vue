<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouteParams } from '@vueuse/router'
import Button from 'primevue/button'
import { Icon } from '@iconify/vue'
import { useProjectDocumentQuery, useProjectDocumentTasksQuery } from '@/entities/project-document'
import type { TaskOverviewDto } from '@/entities/task/types'
import { TasksTableView } from '@/widgets/tasks/views/table'
import { AssociateTasksDialog } from '@/widgets/project-documents/associate-tasks-dialog'
import { PAGE_SIZE } from '@/app/config'
import { taskTableColumnsExcluding } from '@/entities/task'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))
const { tasks, paginationMeta, isPending } = useProjectDocumentTasksQuery(documentId, pagination)

const tableColumnsDef = taskTableColumnsExcluding('project', 'task_list.name')

function onPageChange(newPage: number) {
    page.value = newPage
}

function taskDetailsRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
}

const isAssociateDialogVisible = ref(false)
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="flex h-full w-full flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-end">
                <Button
                    label="Associate Task"
                    severity="info"
                    text
                    size="small"
                    @click="isAssociateDialogVisible = true"
                >
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
                @page-change="onPageChange"
                :columns="tableColumnsDef"
            />
        </div>

        <AssociateTasksDialog
            v-if="projectDocument"
            v-model:visible="isAssociateDialogVisible"
            :document-id="projectDocument.id"
            :project-id="projectDocument.project_id"
        />
    </div>
</template>
