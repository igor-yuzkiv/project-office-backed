<script setup lang="ts">
import type { TaskViewDto } from '@/entities/task-view'

defineProps<{
    modelValue: string
    options: TaskViewDto[]
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', key: string): void
}>()
</script>

<template>
    <nav class="border-line gap-0.5 flex flex-wrap items-end border-b" aria-label="Task views">
        <button
            v-for="view in options"
            :key="view.key"
            type="button"
            class="px-2.5 py-1.5 gap-1.5 hover:text-ink -mb-px inline-flex cursor-pointer items-baseline border-b-2 text-[13.5px] whitespace-nowrap"
            :class="view.key === modelValue ? 'text-ink border-accent font-medium' : 'text-ink-2 border-transparent'"
            :aria-current="view.key === modelValue ? 'true' : undefined"
            @click="emit('update:modelValue', view.key)"
        >
            <span>{{ view.label }}</span>
            <span v-if="view.count !== undefined" class="text-ink-3 text-xs tabular-nums">{{ view.count }}</span>
        </button>
    </nav>
</template>
