<script setup lang="ts">
import { computed } from 'vue'
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import type { TaskOverviewDto } from '@/entities/task/types'
import { TaskStatusMap } from '@/entities/task/config'
import { isDoneTaskStatus } from '@/entities/task-list/lib'
import { pickStatusColors, STATUS_COLORS_FALLBACK } from '@/shared/components/status-pill'
import { TaskStatusTag } from '@/widgets/tasks/metadata'
import { UserAvatar } from '@/widgets/user/user-avatar'

// The plan is read in the order the tasks arrive — the server sorts them by name, which carries
// the list's own numbering. Managing a step happens on the task's page, so a row only links.
const props = defineProps<{
    tasks: TaskOverviewDto[]
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'add'): void
}>()

const theme = useAppThemeStore()

// The current step is the first one still waiting on someone; declined tasks are out of the plan.
const currentTaskId = computed(
    () => props.tasks.find((task) => task.status !== 'declined' && !isDoneTaskStatus(task.status))?.id
)

function dotColor(task: TaskOverviewDto): string {
    return pickStatusColors(TaskStatusMap[task.status]?.colors ?? STATUS_COLORS_FALLBACK, theme.isDark).fg
}
</script>

<template>
    <div class="-mx-2 flex flex-col">
        <p v-if="isPending" class="type-meta-3 px-2 py-2.5">Loading tasks…</p>
        <p v-else-if="tasks.length === 0" class="type-meta-3 px-2 py-2.5">No tasks in the plan yet.</p>

        <RouterLink
            v-for="(task, index) in tasks"
            :key="task.id"
            :to="{ name: 'task-details', params: { id: task.id } }"
            class="border-line gap-2.5 px-2 py-2 hover:bg-hover rounded-md grid grid-cols-[28px_12px_1fr_auto] items-center border-t"
            :class="{ 'selected-row': task.id === currentTaskId }"
            :aria-current="task.id === currentTaskId ? 'step' : undefined"
        >
            <span class="text-ink-3 text-xs tabular-nums">{{ index + 1 }}</span>
            <span class="size-2 shrink-0 rounded-full" :style="{ backgroundColor: dotColor(task) }" />
            <span class="min-w-0 truncate">
                <span class="text-ink font-medium">{{ task.name }}</span>
                <span class="text-ink-3 ml-2 text-xs">{{ task.key }}</span>
            </span>
            <span class="gap-3 flex shrink-0 items-center">
                <UserAvatar
                    v-if="task.updated_by"
                    :initials="task.updated_by.initials"
                    :avatar-url="task.updated_by.avatar_url"
                    size="xsmall"
                    :title="task.updated_by.name"
                />
                <TaskStatusTag :status="task.status" />
            </span>
        </RouterLink>

        <button
            type="button"
            class="border-line px-2 py-2.5 text-ink-3 hover:text-ink cursor-pointer border-t text-left text-[13px]"
            @click="emit('add')"
        >
            + Add a task to the plan
        </button>
    </div>
</template>
