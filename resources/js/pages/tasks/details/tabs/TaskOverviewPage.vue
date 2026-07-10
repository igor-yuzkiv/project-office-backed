<script setup lang="ts">
import { useRoute } from 'vue-router'
import Panel from 'primevue/panel'
import { useTaskQuery } from '@/entities/task/queries'
import { DisplayFields } from '@/shared/components/display'
import type { DisplayFieldConfig } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { TaskPriorityTag, TaskStatusTag } from '@/widgets/tasks/metadata'
import { TagList } from '@/widgets/tags/metadata'
import { formatDate, formatDateTime } from '@/shared/utils/date.util'
import type { ITask } from '@/entities/task/types'

const route = useRoute()
const taskId = route.params.id as string

const { task } = useTaskQuery(taskId)

const generalFields: DisplayFieldConfig<ITask>[] = [
    { name: 'key', label: 'Key' },
    { name: 'sequence_number', label: 'Sequence Number', value: (t) => t.sequence_number },
    { name: 'status', label: 'Status' },
    { name: 'priority', label: 'Priority' },
    { name: 'project', label: 'Project' },
    { name: 'task_list', label: 'Task List', value: (t) => t.task_list?.name ?? null },
    { name: 'tags', label: 'Tags' },
]

const dateFields: DisplayFieldConfig<ITask>[] = [
    { name: 'start_date', label: 'Start Date', value: (t) => formatDate(t.start_date) },
    { name: 'due_date', label: 'Due Date', value: (t) => formatDate(t.due_date) },
]

const systemFields: DisplayFieldConfig<ITask>[] = [
    { name: 'created_by', label: 'Created By' },
    { name: 'created_at', label: 'Created At', value: (t) => formatDateTime(t.created_at) },
    { name: 'updated_by', label: 'Updated By' },
    { name: 'updated_at', label: 'Updated At', value: (t) => formatDateTime(t.updated_at) },
]
</script>

<template>
    <div v-if="task" class="gap-4 p-2 flex flex-col">
        <Panel header="General" :toggleable="true">
            <DisplayFields :item="task" :fields="generalFields">
                <template #[`field:status:value`]="{ item }">
                    <TaskStatusTag :status="item.status" class="w-fit" show-icon />
                </template>
                <template #[`field:priority:value`]="{ item }">
                    <TaskPriorityTag :priority="item.priority" class="w-fit" />
                </template>
                <template #[`field:project:value`]="{ item }">
                    <RouterLink
                        v-if="item.project"
                        :to="{ name: 'project-details', params: { id: item.project_id } }"
                        class="app-link"
                    >
                        {{ item.project.prefix }} - {{ item.project.name }}
                    </RouterLink>
                </template>
                <template #[`field:tags:value`]="{ item }">
                    <TagList :tags="item.tags ?? []" />
                </template>
            </DisplayFields>
        </Panel>

        <Panel header="Dates" :toggleable="true">
            <DisplayFields :item="task" :fields="dateFields" />
        </Panel>

        <Panel header="System" :toggleable="true">
            <DisplayFields :item="task" :fields="systemFields">
                <template #[`field:created_by:value`]="{ item }">
                    <div v-if="item.created_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="item.created_by.initials"
                            :avatar-url="item.created_by.avatar_url"
                            size="small"
                        />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.created_by.name }}</span>
                    </div>
                </template>
                <template #[`field:updated_by:value`]="{ item }">
                    <div v-if="item.updated_by" class="gap-2 flex items-center">
                        <UserAvatar
                            :initials="item.updated_by.initials"
                            :avatar-url="item.updated_by.avatar_url"
                            size="small"
                        />
                        <span class="text-surface-700 dark:text-surface-300">{{ item.updated_by.name }}</span>
                    </div>
                </template>
            </DisplayFields>
        </Panel>
    </div>
</template>
