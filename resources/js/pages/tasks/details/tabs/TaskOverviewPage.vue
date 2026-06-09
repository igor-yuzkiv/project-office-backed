<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useTaskQuery } from '@/entities/task/queries'
import { DisplayField, DisplayDate } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'

const route = useRoute()
const taskId = route.params.id as string

const { task } = useTaskQuery(taskId)
</script>

<template>
    <div v-if="task" class="gap-4 p-4 grid grid-cols-2">
        <DisplayField label="Key" :value="task.key" />
        <DisplayField label="Sequence Number" :value="String(task.sequence_number)" />
        <DisplayField label="Status">
            <TaskStatusTag :status="task.status" class="w-fit" show-icon />
        </DisplayField>
        <DisplayField label="Priority">
            <TaskPriorityTag :priority="task.priority" class="w-fit" />
        </DisplayField>
        <DisplayField
            label="Project"
            :value="task.project ? `${task.project.prefix} - ${task.project.name}` : null"
        />
        <DisplayField label="Task List" :value="task.task_list?.name ?? null" />
        <DisplayField label="Created By">
            <div v-if="task.created_by" class="gap-2 flex items-center">
                <UserAvatar :user="task.created_by" size="small" />
                <span class="text-surface-700">{{ task.created_by.name }}</span>
            </div>
        </DisplayField>
        <DisplayField label="Updated By">
            <div v-if="task.updated_by" class="gap-2 flex items-center">
                <UserAvatar :user="task.updated_by" size="small" />
                <span class="text-surface-700">{{ task.updated_by.name }}</span>
            </div>
        </DisplayField>
        <DisplayDate :date="task.created_at" label="Created" />
        <DisplayDate :date="task.updated_at" label="Updated" />
    </div>
</template>
