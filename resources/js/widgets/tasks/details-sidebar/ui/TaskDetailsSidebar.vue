<script setup lang="ts">
import TaskInfoPanel from './TaskInfoPanel.vue'
import TaskListPanel from './TaskListPanel.vue'
import type { ITask } from '@/entities/task/types'

defineProps<{ task: ITask }>()
</script>

<template>
    <aside class="gap-4 flex w-1/4 shrink-0 flex-col overflow-y-auto">
        <TaskListPanel v-if="task.task_list" :task-list="task.task_list" :current-task-id="task.id" />

        <TaskInfoPanel :task="task" />
    </aside>
</template>
