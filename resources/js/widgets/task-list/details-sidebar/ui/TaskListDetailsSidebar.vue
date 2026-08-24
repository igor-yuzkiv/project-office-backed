<script setup lang="ts">
import { Icon } from '@iconify/vue'
import Button from 'primevue/button'
import TaskListInfoPanel from './TaskListInfoPanel.vue'
import TaskListSystemPanel from './TaskListSystemPanel.vue'
import type { ITaskList } from '@/entities/task-list/types'

defineProps<{ taskList: ITaskList }>()

const emit = defineEmits<{
    (e: 'collapse'): void
}>()
</script>

<template>
    <!-- No width, border or surface of its own: the host renders this either as a column beside
         the tabs or as the body of a drawer over them. -->
    <div class="flex h-full flex-col">
        <!-- Same height as the tab strip across the way, so the two rows line up. -->
        <header class="gap-3 px-3 py-1.5 flex items-center justify-between" style="min-height: 2.75rem">
            <h2 class="text-surface-600 dark:text-surface-300 text-xs font-semibold tracking-wide uppercase">
                Task List
            </h2>

            <Button
                severity="secondary"
                text
                rounded
                size="small"
                aria-label="Hide task list details"
                title="Hide task list details"
                @click="emit('collapse')"
            >
                <template #icon>
                    <Icon icon="heroicons:bars-3-bottom-right" class="text-base" />
                </template>
            </Button>
        </header>

        <!-- The blocks are separated by a divider rather than by card borders, so the sidebar
             reads as one surface instead of a stack of boxes. -->
        <div class="gap-4 px-3 pb-3 min-h-0 flex flex-1 flex-col overflow-y-auto">
            <TaskListInfoPanel :task-list="taskList" class="shrink-0" />

            <hr class="border-surface-200 dark:border-surface-700 shrink-0" />

            <TaskListSystemPanel :task-list="taskList" class="shrink-0" />
        </div>
    </div>
</template>
