<script setup lang="ts">
import { computed } from 'vue'
import { format as dateFnsFormat } from 'date-fns'

const props = withDefaults(
    defineProps<{
        date: string | Date
        label?: string
        inline?: boolean
        emptyValue?: string
        format?: string
    }>(),
    { inline: false, emptyValue: 'N/A', format: 'MMM d, yyyy HH:mm' }
)

const displayValue = computed(() => {
    try {
        return dateFnsFormat(new Date(props.date), props.format)
    } catch {
        return String(props.date)
    }
})
</script>

<template>
    <div :class="inline ? 'flex-row items-center gap-2' : 'flex-col gap-1'" class="flex">
        <span v-if="label || $slots.label" class="text-surface-400 shrink-0">
            <slot name="label">{{ label }}</slot>
        </span>
        <slot>
            <span class="text-surface-700">{{ displayValue }}</span>
        </slot>
    </div>
</template>
