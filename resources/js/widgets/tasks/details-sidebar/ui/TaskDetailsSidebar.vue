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

        The column itself does not scroll. Task Info and the navigation keep their height, the
        task list takes whatever is left, and the only scrollbar lives inside that list.
    -->
    <aside
        class="gap-4 pl-4 border-surface-200 dark:border-surface-700 flex w-1/4 shrink-0 flex-col overflow-hidden border-l"
    >
        <TaskInfoPanel :task="task" class="shrink-0" />

        <template v-if="task.task_list">
            <hr class="border-surface-200 dark:border-surface-700 shrink-0" />

            <TaskListPanel :task-list="task.task_list" :current-task-id="task.id" />

            <TaskListNavigation :task-list-id="task.task_list.id" :current-task-id="task.id" class="shrink-0" />
        </template>
    </aside>
</template>
