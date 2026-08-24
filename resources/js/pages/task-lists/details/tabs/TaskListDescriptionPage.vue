<script setup lang="ts">
import { useRoute } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { DocumentSheet } from '@/shared/components/document-sheet'

const route = useRoute()
const taskListId = route.params.id as string

const { taskList } = useTaskListQuery(taskListId)
</script>

<template>
    <!-- h-full: the sheet scrolls its own canvas, and without a bounded height it would grow
         instead and hand the scrolling back to the tab host. -->
    <div class="flex h-full flex-col">
        <!-- Blocks stay unpickable: annotating belongs to documents, not to task lists. -->
        <DocumentSheet v-if="taskList?.description" :content="taskList.description" />

        <div v-else class="gap-3 p-10 flex flex-1 flex-col items-center justify-center text-center">
            <Icon icon="heroicons:document" class="text-surface-300 text-4xl" />
            <p class="text-surface-700 dark:text-surface-200 text-sm font-medium">This task list has no description</p>
            <p class="text-surface-500 max-w-sm text-xs">Edit the task list to describe what it covers.</p>
        </div>
    </div>
</template>
