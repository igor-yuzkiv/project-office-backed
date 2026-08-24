<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { RouterLink, useRouter } from 'vue-router'
import type { TaskOverviewDto } from '@/entities/task/types'
import { DataPanel, type DataPanelState } from '@/shared/components/data-panel'
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import { formatRelativeTime } from '../lib'
import PanelViewAllLink from './PanelViewAllLink.vue'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { CopyToClipboard } from '@/shared/components/display'

const props = defineProps<{
    tasks: TaskOverviewDto[]
    isPending: boolean
    isError: boolean
}>()

const emit = defineEmits<{
    (e: 'retry'): void
}>()

const router = useRouter()

const state = computed<DataPanelState>(() => {
    if (props.isPending) return 'pending'
    if (props.isError) return 'error'

    return props.tasks.length === 0 ? 'empty' : 'ready'
})

function taskRoute(task: TaskOverviewDto) {
    return { name: 'task-details', params: { id: task.id } }
}

function projectRoute(project: ProjectOverviewDto) {
    return { name: 'project-details', params: { id: project.id } }
}

function openTask(task: TaskOverviewDto) {
    router.push(taskRoute(task))
}
</script>

<template>
    <DataPanel
        title="Recent Tasks"
        :state="state"
        empty-message="No recent tasks"
        error-message="Could not load recent tasks."
        :skeleton-rows="8"
        @retry="emit('retry')"
    >
        <template #action>
            <PanelViewAllLink label="View all tasks" :to="{ name: 'tasks' }" />
        </template>

        <table class="text-sm w-full table-fixed">
            <thead>
                <tr class="bg-surface-50 dark:bg-surface-800 text-surface-400 text-xs text-left">
                    <th class="px-4 py-2 font-medium w-32">Task ID</th>
                    <th class="px-4 py-2 font-medium w-42">Project</th>
                    <th class="px-4 py-2 font-medium">Title</th>
                    <th class="px-4 py-2 font-medium w-36">Status</th>
                    <th class="px-4 py-2 font-medium w-28">Updated</th>
                    <th class="px-4 py-2 w-10"><span class="sr-only">Open</span></th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="task in tasks"
                    :key="task.id"
                    class="border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/60 cursor-pointer border-t"
                    @click="openTask(task)"
                >
                    <td class="px-4 py-2.5">
                        <CopyToClipboard class="text-surface-400" :text="task.key" />
                    </td>
                    <td class="text-surface-600 dark:text-surface-300 px-4 py-2.5 truncate" :title="task.project?.name">
                        <RouterLink
                            v-if="task.project"
                            :to="projectRoute(task.project)"
                            class="app-link block truncate"
                            @click.stop
                        >
                            {{ task.project?.name ?? '—' }}
                        </RouterLink>
                    </td>
                    <td class="text-surface-800 dark:text-surface-100 px-4 py-2.5 truncate" :title="task.name">
                        <RouterLink :to="taskRoute(task)" class="app-link block truncate" @click.stop>
                            {{ task.name }}
                        </RouterLink>
                    </td>
                    <td class="px-4 py-2.5">
                        <TaskStatusTag :status="task.status" class="w-full" />
                    </td>
                    <td class="text-surface-400 px-4 py-2.5 text-xs whitespace-nowrap">
                        {{ formatRelativeTime(task.updated_at) }}
                    </td>
                    <td class="px-4 py-2.5">
                        <Icon icon="heroicons:chevron-right" class="text-surface-400 size-3.5" aria-hidden="true" />
                    </td>
                </tr>
            </tbody>
        </table>
    </DataPanel>
</template>
