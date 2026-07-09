<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { useProjectDocumentQuery } from '@/entities/project-document'
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import { CopyToClipboard } from '@/shared/components/display'

const documentId = useRouteParams<string>('id')

const { projectDocument } = useProjectDocumentQuery(documentId)
</script>

<template>
    <div class="p-4 flex flex-col">
        <div v-if="!projectDocument?.tasks || projectDocument.tasks.length === 0" class="text-surface-400 text-sm">
            No related tasks yet.
        </div>

        <div v-else class="divide-surface-200 dark:divide-surface-700 flex flex-col divide-y">
            <RouterLink
                v-for="task in projectDocument.tasks"
                :key="task.id"
                :to="{ name: 'task-details', params: { id: task.id } }"
                class="gap-3 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 flex items-center"
            >
                <CopyToClipboard :text="task.key" hide-copy-icon class="text-surface-500" />
                <span class="app-link">{{ task.name }}</span>
                <TaskStatusTag :status="task.status" class="ml-auto w-fit" show-icon />
            </RouterLink>
        </div>
    </div>
</template>
