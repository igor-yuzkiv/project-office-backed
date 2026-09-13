<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useTaskListQuery, useTaskListTasksQuery } from '@/entities/task-list/queries'
import { taskListProgress } from '@/entities/task-list/lib'
import { IconButton } from '@/shared/components/button'
import { taskNeighbours } from '../lib/task-neighbours'
import TaskRailList from './TaskRailList.vue'

// The rail belongs to the task's list, so it loads the list and its tasks itself and hands the
// tasks down; the page only says which list and which task is open.
const props = defineProps<{
    taskListId: string
    currentTaskId: string
}>()

const emit = defineEmits<{
    (e: 'hide'): void
}>()

const router = useRouter()

const { taskList } = useTaskListQuery(() => props.taskListId)
const { tasks, isPending, isError } = useTaskListTasksQuery(() => props.taskListId)

const progress = computed(() => taskListProgress(taskList.value?.task_status_counts))
const neighbours = computed(() => taskNeighbours(tasks.value, props.currentTaskId))

function goTo(taskId: string) {
    router.push({ name: 'task-details', params: { id: taskId } })
}
</script>

<template>
    <aside class="border-line text-ink-2 min-h-0 flex flex-col overflow-hidden border-l text-[13px]">
        <div class="gap-2 h-11 pr-3 pl-4 border-line flex shrink-0 items-center border-b">
            <RouterLink
                :to="{ name: 'task-list-details', params: { id: taskListId } }"
                class="text-ink hover:text-accent min-w-0 font-medium truncate"
                :title="taskList ? `Open ${taskList.name}` : undefined"
            >
                {{ taskList?.name ?? 'Task list' }}
            </RouterLink>
            <span v-if="taskList" class="type-meta-3 ml-auto whitespace-nowrap">
                {{ progress.done }} of {{ progress.total }} done
            </span>
            <IconButton
                icon="tabler:layout-sidebar-right-collapse"
                aria-label="Hide list"
                title="Hide list"
                class="shrink-0"
                :class="{ 'ml-auto': !taskList }"
                @click="emit('hide')"
            />
        </div>

        <nav class="p-2 min-h-0 flex-1 overflow-auto" :aria-label="taskList ? `Tasks in ${taskList.name}` : 'Tasks'">
            <p v-if="isPending" class="type-meta-3 px-2 py-1.5">Loading tasks…</p>
            <p v-else-if="isError" class="type-meta-3 px-2 py-1.5">Failed to load the tasks of this list.</p>
            <p v-else-if="tasks.length === 0" class="type-meta-3 px-2 py-1.5">No tasks yet.</p>
            <TaskRailList v-else :tasks="tasks" :current-task-id="currentTaskId" />
        </nav>

        <div class="gap-1 p-2 border-line flex shrink-0 border-t">
            <button
                type="button"
                class="gap-1.5 px-2 py-1.5 hover:bg-hover hover:text-ink disabled:hover:text-ink-2 rounded-md flex flex-1 cursor-pointer items-center disabled:cursor-default disabled:opacity-50 disabled:hover:bg-transparent"
                :disabled="!neighbours.previous"
                :title="neighbours.previous?.name"
                @click="neighbours.previous && goTo(neighbours.previous.id)"
            >
                <Icon icon="heroicons:chevron-left" class="text-[14px]" />
                Previous
            </button>
            <button
                type="button"
                class="gap-1.5 px-2 py-1.5 hover:bg-hover hover:text-ink disabled:hover:text-ink-2 rounded-md flex flex-1 cursor-pointer items-center justify-end disabled:cursor-default disabled:opacity-50 disabled:hover:bg-transparent"
                :disabled="!neighbours.next"
                :title="neighbours.next?.name"
                @click="neighbours.next && goTo(neighbours.next.id)"
            >
                Next
                <Icon icon="heroicons:chevron-right" class="text-[14px]" />
            </button>
        </div>
    </aside>
</template>
