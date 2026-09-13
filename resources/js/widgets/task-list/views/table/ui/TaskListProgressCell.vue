<script setup lang="ts">
import { computed } from 'vue'
import type { TaskStatusCounts } from '@/entities/task/types'
import { taskListProgress } from '@/entities/task-list/lib'
import { ProgressBar } from '@/shared/components/progress-bar'
import { STATUS_COLORS, useStatusColors } from '@/shared/components/status-pill'

const props = defineProps<{
    counts: TaskStatusCounts | undefined
}>()

const doneColors = useStatusColors(STATUS_COLORS.done)
const inProgressColors = useStatusColors(STATUS_COLORS.progress)

const progress = computed(() => taskListProgress(props.counts))
</script>

<template>
    <ProgressBar
        class="w-[120px]"
        :segments="[
            { value: progress.done, colors: doneColors },
            { value: progress.inProgress, colors: inProgressColors },
        ]"
        :total="progress.total"
    >
        {{ progress.done }} of {{ progress.total }} done
    </ProgressBar>
</template>
