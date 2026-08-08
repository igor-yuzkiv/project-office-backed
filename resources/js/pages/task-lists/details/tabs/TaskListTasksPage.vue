<script setup lang="ts">
import { useRouteParams } from '@vueuse/router'
import { TaskListTasksExpansion } from '@/widgets/task-list/tasks-table'

const taskListId = useRouteParams<string>('id')
</script>

<template>
    <div class="flex h-full w-full flex-col overflow-hidden">
        <TaskListTasksExpansion :task-list-id="taskListId" />
    </div>
</template>
