<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { Icon } from '@iconify/vue'
import { useTaskQuery } from '@/entities/task/queries'
import { DocumentSheet } from '@/widgets/project-documents/document-sheet'

const taskId = useRouteParams<string>('id')

const { task } = useTaskQuery(taskId)
</script>

<template>
    <!-- h-full: the sheet scrolls its own canvas, and without a bounded height it would grow
         instead and hand the scrolling back to the tab host. -->
    <div class="flex h-full flex-col">
        <!-- Blocks stay unpickable: annotating belongs to documents, not to tasks. -->
        <DocumentSheet v-if="task?.description" :content="task.description" />

        <div v-else class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
            <Icon icon="heroicons:document" class="text-surface-300 text-4xl" />
            <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This task has no description</p>
            <p class="text-surface-500 max-w-sm text-xs">Edit the task to describe what has to be done.</p>
        </div>
    </div>
</template>
