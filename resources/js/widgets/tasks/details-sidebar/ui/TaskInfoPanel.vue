<script setup lang="ts">
import { DisplayField } from '@/shared/components/display'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import type { ITask } from '@/entities/task/types'

defineProps<{ task: ITask }>()
</script>

<template>
    <section class="gap-2 flex flex-col">
        <h2 class="font-semibold text-surface-900 dark:text-surface-0">Task Info</h2>

        <!-- DisplayFields is a two-column grid from md up, which is wider than this column ever is. -->
        <DisplayField label="Status">
            <TaskStatusTag :status="task.status" class="w-fit" show-icon />
        </DisplayField>

        <DisplayField label="Priority">
            <TaskPriorityTag :priority="task.priority" class="w-fit" />
        </DisplayField>

        <DisplayField label="Sequence" :value="String(task.sequence_number)" />
    </section>
</template>
