<script setup lang="ts">
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import type { TaskOverviewDto } from '@/entities/task/types'

defineProps<{ task: TaskOverviewDto; isCurrent: boolean }>()
</script>

<template>
    <component
        :is="isCurrent ? 'div' : 'RouterLink'"
        :to="isCurrent ? undefined : { name: 'task-details', params: { id: task.id } }"
        class="gap-2 px-2 py-2 rounded flex items-center"
        :class="
            isCurrent
                ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-500 border-l-2'
                : 'hover:bg-surface-100 dark:hover:bg-surface-800'
        "
        :aria-current="isCurrent ? 'page' : undefined"
    >
        <TaskStatusTag :status="task.status" class="shrink-0" />

        <span class="text-surface-400 text-sm shrink-0">{{ task.key }}</span>

        <span class="text-sm truncate" :class="isCurrent ? 'text-surface-900 dark:text-surface-0' : 'app-link'">
            {{ task.name }}
        </span>
    </component>
</template>
