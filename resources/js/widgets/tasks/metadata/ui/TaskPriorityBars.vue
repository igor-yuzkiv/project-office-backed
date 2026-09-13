<script setup lang="ts">
import { computed } from 'vue'
import type { TaskPriorityDto, TaskPriorityName } from '@/entities/task/types/task-priority.types'
import { TaskPriorityMap } from '@/entities/task/config'
import { STATUS_COLORS, useStatusColors } from '@/shared/components/status-pill'

const props = defineProps<{
    priority: TaskPriorityDto | null | undefined
}>()

const FILLED_BARS: Record<TaskPriorityName, number> = { None: 0, Low: 1, Medium: 2, High: 3, Urgent: 3 }
const BAR_HEIGHTS = [5, 8, 12]

const declined = useStatusColors(STATUS_COLORS.declined)

const meta = computed(() => (props.priority ? (TaskPriorityMap[props.priority.name] ?? null) : null))
const filled = computed(() => (meta.value ? FILLED_BARS[meta.value.name] : 0))
const isUrgent = computed(() => meta.value?.name === 'Urgent')
const filledStyle = computed(() => (isUrgent.value ? { backgroundColor: declined.value.fg } : undefined))
</script>

<template>
    <span class="gap-2 inline-flex items-center text-[13px] whitespace-nowrap" title="Priority">
        <span class="h-3 gap-0.5 inline-flex items-end" aria-hidden="true">
            <i
                v-for="(height, index) in BAR_HEIGHTS"
                :key="height"
                class="w-[3px] rounded-[1px]"
                :class="index < filled ? 'bg-ink' : 'bg-line-2'"
                :style="[{ height: `${height}px` }, index < filled ? filledStyle : undefined]"
                :data-filled="index < filled ? 'true' : undefined"
            />
        </span>
        <span :class="{ 'text-ink-2': filled === 0 }">{{ meta?.label ?? 'None' }}</span>
    </span>
</template>
