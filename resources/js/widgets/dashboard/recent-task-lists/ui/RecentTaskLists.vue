<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { RouterLink, useRouter } from 'vue-router'
import type { DashboardTaskListDto } from '@/entities/dashboard'
import { DataPanel, type DataPanelState } from '@/shared/components/data-panel'
import { TaskListStatusTag } from '@/widgets/task-list/metadata'
import { PanelViewAllLink, formatRelativeTime } from '@/widgets/dashboard/shared'
import { CopyToClipboard } from '@/shared/components/display'
import type { ProjectOverviewDto } from '@/entities/project/types'

const props = defineProps<{
    taskLists: DashboardTaskListDto[]
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

    return props.taskLists.length === 0 ? 'empty' : 'ready'
})

function taskListRoute(taskList: DashboardTaskListDto) {
    return { name: 'task-list-details', params: { id: taskList.id } }
}

function projectRoute(project: ProjectOverviewDto) {
    return { name: 'project-details', params: { id: project.id } }
}

function openTaskList(taskList: DashboardTaskListDto) {
    router.push(taskListRoute(taskList))
}
</script>

<template>
    <DataPanel
        title="Recent Task Lists"
        :state="state"
        empty-message="No recent task lists"
        error-message="Could not load recent task lists."
        :skeleton-rows="4"
        @retry="emit('retry')"
    >
        <template #action>
            <PanelViewAllLink label="View all task lists" :to="{ name: 'task-lists' }" />
        </template>

        <table class="text-sm w-full table-fixed">
            <thead>
                <tr class="bg-surface-50 dark:bg-surface-800 text-surface-400 text-xs text-left">
                    <th class="px-4 py-2 font-medium w-34"></th>
                    <th class="px-4 py-2 font-medium w-40">Project</th>
                    <th class="px-4 py-2 font-medium">Task List</th>
                    <th class="px-4 py-2 font-medium w-36">Status</th>
                    <th class="px-4 py-2 font-medium w-24">Tasks</th>
                    <th class="px-4 py-2 font-medium w-28">Updated</th>
                    <th class="px-4 py-2 w-10"><span class="sr-only">Open</span></th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="taskList in taskLists"
                    :key="taskList.id"
                    class="border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/60 cursor-pointer border-t"
                    @click="openTaskList(taskList)"
                >
                    <td class="px-4 py-2.5">
                        <CopyToClipboard class="text-surface-400" :text="taskList.key" />
                    </td>
                    <td class="text-surface-600 dark:text-surface-300 px-4 py-2.5 tabular-nums">
                        <RouterLink
                            v-if="taskList.project"
                            :to="projectRoute(taskList.project)"
                            class="app-link block truncate"
                            @click.stop
                        >
                            {{ taskList.project?.name ?? '—' }}
                        </RouterLink>
                    </td>
                    <td class="px-4 py-2.5">
                        <RouterLink
                            :to="taskListRoute(taskList)"
                            class="app-link block truncate"
                            :title="taskList.name"
                            @click.stop
                        >
                            {{ taskList.name }}
                        </RouterLink>
                        <span
                            v-if="taskList.description"
                            class="text-surface-400 text-xs block truncate"
                            :title="taskList.description"
                        >
                            {{ taskList.description }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <TaskListStatusTag :status="taskList.status" class="w-full" />
                    </td>
                    <td class="text-surface-600 dark:text-surface-300 px-4 py-2.5 tabular-nums">
                        {{ taskList.tasks_count }}
                    </td>
                    <td class="text-surface-400 px-4 py-2.5 text-xs whitespace-nowrap">
                        {{ formatRelativeTime(taskList.updated_at) }}
                    </td>
                    <td class="px-4 py-2.5">
                        <Icon icon="heroicons:chevron-right" class="text-surface-400 size-3.5" aria-hidden="true" />
                    </td>
                </tr>
            </tbody>
        </table>
    </DataPanel>
</template>
