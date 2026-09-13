<script setup lang="ts">
import { useAppThemeStore } from '@/app/stores/use.app-theme-store'
import type { TaskOverviewDto } from '@/entities/task/types'
import { TaskStatusMap } from '@/entities/task/config'
import { pickStatusColors, STATUS_COLORS_FALLBACK } from '@/shared/components/status-pill'

defineProps<{
    tasks: TaskOverviewDto[]
    currentTaskId: string
}>()

const theme = useAppThemeStore()

function dotColor(task: TaskOverviewDto): string {
    return pickStatusColors(TaskStatusMap[task.status]?.colors ?? STATUS_COLORS_FALLBACK, theme.isDark).fg
}
</script>

<template>
    <RouterLink
        v-for="task in tasks"
        :key="task.id"
        :to="{ name: 'task-details', params: { id: task.id } }"
        class="gap-x-2.5 px-2 py-1.5 hover:bg-hover text-ink rounded-md grid grid-cols-[8px_1fr] items-start"
        :class="{ 'selected-row font-medium': task.id === currentTaskId }"
        :aria-current="task.id === currentTaskId ? 'page' : undefined"
    >
        <span class="mt-1.5 size-2 shrink-0 rounded-full" :style="{ backgroundColor: dotColor(task) }" />
        <span class="min-w-0">
            <span class="block leading-[1.35]">{{ task.name }}</span>
            <small
                class="mt-0.5 font-normal block text-[11.5px]"
                :class="task.id === currentTaskId ? 'text-ink-2' : 'text-ink-3'"
            >
                {{ task.key }} · {{ TaskStatusMap[task.status]?.label ?? task.status }}
            </small>
        </span>
    </RouterLink>
</template>
