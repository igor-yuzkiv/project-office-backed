<script setup lang="ts">
import TaskInfoPanel from './TaskInfoPanel.vue'
import TaskListPanel from './TaskListPanel.vue'
import TaskListNavigation from './TaskListNavigation.vue'
import type { ITask } from '@/entities/task/types'

defineProps<{ task: ITask }>()
</script>

<template>
    <!--
        One vertical rule separates the column from the tab content; the blocks inside it are
        separated from each other by a divider rather than by card borders, so the sidebar reads
        as one surface instead of a stack of boxes.
    -->
    <aside
        class="gap-4 pl-4 border-surface-200 dark:border-surface-700 flex w-1/4 shrink-0 flex-col overflow-y-auto border-l"
    >
        <TaskInfoPanel :task="task" />

        <template v-if="task.task_list">
            <hr class="border-surface-200 dark:border-surface-700" />

            <TaskListPanel :task-list="task.task_list" :current-task-id="task.id" />

            <TaskListNavigation :task-list-id="task.task_list.id" :current-task-id="task.id" />
        </template>
    </aside>
</template>
