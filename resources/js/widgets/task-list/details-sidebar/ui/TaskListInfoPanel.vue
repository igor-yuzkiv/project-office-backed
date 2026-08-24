<script setup lang="ts">
import { DisplayField } from '@/shared/components/display'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { TagList } from '@/widgets/tags/metadata'
import type { ITaskList } from '@/entities/task-list/types'

defineProps<{ taskList: ITaskList }>()
</script>

<template>
    <section class="gap-2 flex flex-col">
        <h2 class="font-semibold text-surface-900 dark:text-surface-0">General</h2>

        <!-- DisplayFields is a two-column grid from md up, which is wider than this column ever is. -->
        <DisplayField label="Key" :value="taskList.key" />

        <DisplayField label="Sequence" :value="String(taskList.sequence_number)" />

        <DisplayField label="Status">
            <TaskListStatusTag :status="taskList.status" class="w-fit" />
        </DisplayField>

        <DisplayField v-if="taskList.project" label="Project">
            <RouterLink :to="{ name: 'project-details', params: { id: taskList.project_id } }" class="app-link">
                {{ taskList.project.prefix }} - {{ taskList.project.name }}
            </RouterLink>
        </DisplayField>

        <!-- Dropped entirely when there are none: an empty row here is noise. -->
        <DisplayField v-if="taskList.tags?.length" label="Tags">
            <TagList :tags="taskList.tags" />
        </DisplayField>
    </section>
</template>
