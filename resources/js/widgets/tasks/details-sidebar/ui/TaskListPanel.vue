<script setup lang="ts">
import { computed } from 'vue'
import Skeleton from 'primevue/skeleton'
import { Icon } from '@iconify/vue'
import { useTaskListTasksQuery } from '@/entities/task-list/queries'
import { isTaskDone } from '@/entities/task/config'
import type { ITaskListOverview } from '@/entities/task-list/types'
import TaskListTaskRow from './TaskListTaskRow.vue'

const props = defineProps<{ taskList: ITaskListOverview; currentTaskId: string }>()

const taskListId = computed(() => props.taskList.id)

const { tasks, paginationMeta, isPending, isError } = useTaskListTasksQuery(taskListId)

/**
 * Counted from the page that was actually loaded. When the list is longer than that, the tally
 * would be a claim about tasks nobody fetched, so it is dropped and the link to the full list
 * carries the rest.
 */
const completedCount = computed(() => tasks.value.filter((task) => isTaskDone(task.status)).length)

const isWholeListLoaded = computed(() => {
    const total = paginationMeta.value?.total
    return total == null || total === tasks.value.length
})
</script>

<template>
    <section class="gap-2 flex flex-col">
        <div class="gap-1 flex flex-col">
            <h2 class="font-semibold text-surface-900 dark:text-surface-0">Task List</h2>

            <RouterLink :to="{ name: 'task-list-details', params: { id: taskList.id } }" class="text-sm app-link">
                {{ taskList.name }}
            </RouterLink>

            <span v-if="isWholeListLoaded && tasks.length" class="text-surface-400 text-sm">
                {{ completedCount }} of {{ tasks.length }} completed
            </span>
        </div>

        <div v-if="isPending" class="gap-2 flex flex-col">
            <Skeleton v-for="n in 4" :key="n" height="1.75rem" />
        </div>

        <p v-else-if="isError" class="text-surface-400 text-sm">Failed to load the tasks of this list.</p>

        <p v-else-if="!tasks.length" class="text-surface-400 text-sm">This list has no tasks yet.</p>

        <div v-else class="max-h-96 -mx-2 flex flex-col overflow-y-auto">
            <TaskListTaskRow
                v-for="task in tasks"
                :key="task.id"
                :task="task"
                :is-current="task.id === currentTaskId"
            />
        </div>

        <RouterLink
            :to="{ name: 'task-list-details', params: { id: taskList.id } }"
            class="gap-1 text-sm app-link flex items-center"
        >
            View full task list
            <Icon icon="heroicons:arrow-top-right-on-square" />
        </RouterLink>
    </section>
</template>
