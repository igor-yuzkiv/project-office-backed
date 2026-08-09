<script setup lang="ts">
import { useRoute } from 'vue-router'
import Panel from 'primevue/panel'
import { useTaskListQuery } from '@/entities/task-list/queries'
import { DisplayFields } from '@/shared/components/display'
import type { DisplayFieldConfig } from '@/shared/components/display'
import { MarkdownPreview } from '@/shared/components/md-editor'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { TagList } from '@/widgets/tags/metadata'
import { formatDateTime } from '@/shared/utils/date.util'
import type { ITaskList } from '@/entities/task-list/types'

const route = useRoute()
const taskListId = route.params.id as string

const { taskList } = useTaskListQuery(taskListId)

const generalFields: DisplayFieldConfig<ITaskList>[] = [
    { name: 'key', label: 'Key' },
    { name: 'sequence_number', label: 'Sequence Number', value: (l) => l.sequence_number },
    { name: 'status', label: 'Status' },
    { name: 'project', label: 'Project' },
    { name: 'tags', label: 'Tags' },
]

const systemFields: DisplayFieldConfig<ITaskList>[] = [
    { name: 'created_by', label: 'Created By' },
    { name: 'created_at', label: 'Created At', value: (l) => formatDateTime(l.created_at) },
    { name: 'updated_by', label: 'Updated By' },
    { name: 'updated_at', label: 'Updated At', value: (l) => formatDateTime(l.updated_at) },
]
</script>

<template>
    <div v-if="taskList" class="gap-4 p-2 flex flex-col">
        <Panel header="General" :toggleable="true">
            <DisplayFields :item="taskList" :fields="generalFields">
                <template #[`field:status:value`]="{ item }">
                    <TaskListStatusTag :status="item.status" class="w-fit" />
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

        <Panel header="System" :toggleable="true">
            <DisplayFields :item="taskList" :fields="systemFields">
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

        <Panel header="Description" :toggleable="true">
            <MarkdownPreview v-if="taskList.description" :model-value="taskList.description" />
            <p v-else class="text-sm text-surface-400 italic">No description available.</p>
        </Panel>
    </div>
</template>
