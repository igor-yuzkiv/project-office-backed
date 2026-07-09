<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouteParams } from '@vueuse/router'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import { useProjectDocumentQuery, useProjectDocumentTasksQuery } from '@/entities/project-document'
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import { CopyToClipboard } from '@/shared/components/display'
import { AssociateTasksDialog } from '@/widgets/project-documents/associate-tasks-dialog'
import { PAGE_SIZE } from '@/app/config'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)

const page = ref(1)
const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))
const { tasks, paginationMeta, isPending } = useProjectDocumentTasksQuery(documentId, pagination)

const showPaginator = computed(() => paginationMeta.value && paginationMeta.value.last_page > 1)

function onPageChange(event: { page: number }) {
    page.value = event.page + 1
}

const isAssociateDialogVisible = ref(false)
</script>

<template>
    <div class="gap-3 p-4 flex flex-col">
        <div class="flex items-center justify-end">
            <Button label="Associate Task" size="small" outlined @click="isAssociateDialogVisible = true" />
        </div>

        <div v-if="isPending" class="text-surface-400 text-sm">Loading related tasks...</div>

        <div v-else-if="tasks.length === 0" class="text-surface-400 text-sm">No related tasks yet.</div>

        <div v-else class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
            <RouterLink
                v-for="task in tasks"
                :key="task.id"
                :to="{ name: 'task-details', params: { id: task.id } }"
                class="gap-3 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 flex items-center"
            >
                <CopyToClipboard :text="task.key" hide-copy-icon class="text-surface-500" />
                <span class="app-link">{{ task.name }}</span>
                <TaskStatusTag :status="task.status" class="ml-auto w-fit" show-icon />
            </RouterLink>
        </div>

        <Paginator
            v-if="showPaginator"
            :rows="PAGE_SIZE"
            :total-records="paginationMeta!.total"
            :first="(page - 1) * PAGE_SIZE"
            @page="onPageChange"
        />

        <AssociateTasksDialog
            v-if="projectDocument"
            v-model:visible="isAssociateDialogVisible"
            :document-id="projectDocument.id"
            :project-id="projectDocument.project_id"
        />
    </div>
</template>
